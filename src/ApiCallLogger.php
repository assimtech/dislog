<?php

declare(strict_types=1);

namespace Assimtech\Dislog;

use Symfony\Component\OptionsResolver;

class ApiCallLogger implements ApiCallLoggerInterface
{
    protected Factory\FactoryInterface $apiCallFactory;
    protected Handler\HandlerInterface $handler;

    /**
     * @var array<string,bool|int|null> $options
     */
    protected array $options;
    protected ?\Psr\Log\LoggerInterface $psrLogger;

    public function __construct(
        Factory\FactoryInterface $apiCallFactory,
        Handler\HandlerInterface $handler,
        array $options = [],
        ?\Psr\Log\LoggerInterface $psrLogger = null
    ) {
        $resolver = (new OptionsResolver\OptionsResolver())
            ->setDefaults([
                'suppress_handler_exceptions' => true,
                'endpoint_max_length' => null,
                'method_max_length' => null,
                'reference_max_length' => null,
            ])
            ->setAllowedValues('suppress_handler_exceptions', [
                true,
                false,
            ])
            ->setAllowedTypes('suppress_handler_exceptions', 'bool')
            ->setAllowedTypes('endpoint_max_length', [
                'null',
                'int',
            ])
            ->setAllowedTypes('method_max_length', [
                'null',
                'int',
            ])
            ->setAllowedTypes('reference_max_length', [
                'null',
                'int',
            ])
        ;

        $this->apiCallFactory = $apiCallFactory;
        $this->handler = $handler;
        $this->options = $resolver->resolve($options);
        if (null !== $this->options['endpoint_max_length'] && $this->options['endpoint_max_length'] < 1) {
            throw new \InvalidArgumentException(
                "endpoint_max_length must be greater than 1, {$this->options['endpoint_max_length']} given"
            );
        }
        if (null !== $this->options['method_max_length'] && $this->options['method_max_length'] < 1) {
            throw new \InvalidArgumentException(
                "method_max_length must be greater than 1, {$this->options['method_max_length']} given"
            );
        }
        if (null !== $this->options['reference_max_length'] && $this->options['reference_max_length'] < 1) {
            throw new \InvalidArgumentException(
                "reference_max_length must be greater than 1, {$this->options['reference_max_length']} given"
            );
        }
        $this->psrLogger = $psrLogger;
    }

    /**
     * @param callable[]|callable|null $processors
     */
    protected function processPayload(
        $processors,
        ?string $payload
    ): ?string {
        if ($payload === null) {
            return $payload;
        }

        if (null === $processors) {
            $processors = [];
        }
        if (!\is_array($processors) && !$processors instanceof \Traversable) {
            $processors = [ $processors ];
        }

        foreach ($processors as $processor) {
            $payload = \call_user_func($processor, $payload);
        }

        return $payload;
    }

    /**
     * @param callable[]|callable|null $processors
     */
    public function logRequest(
        ?string $request,
        ?string $endpoint,
        ?string $method,
        ?string $reference = null,
        $processors = null,
        ?float $requestTime = null
    ): Model\ApiCallInterface {
        $processedRequest = $this->processPayload($processors, $request);

        if (null !== $endpoint && null !== $this->options['endpoint_max_length']) {
            $endpoint = \substr($endpoint, 0, $this->options['endpoint_max_length']);
        }
        if (null !== $method && null !== $this->options['method_max_length']) {
            $method = \substr($method, 0, $this->options['method_max_length']);
        }
        if (null !== $reference && null !== $this->options['reference_max_length']) {
            $reference = \substr($reference, 0, $this->options['reference_max_length']);
        }

        $apiCall = $this->apiCallFactory->create();
        $apiCall
            ->setRequest($processedRequest)
            ->setEndpoint($endpoint)
            ->setMethod($method)
            ->setReference($reference)
            ->setRequestTime($requestTime ?? \microtime(true))
        ;

        $this->handleApiCall($apiCall);

        return $apiCall;
    }

    /**
     * @param callable[]|callable|null $processors
     */
    public function logResponse(
        Model\ApiCallInterface $apiCall,
        ?string $response = null,
        $processors = null,
        ?float $responseTime = null
    ): void {
        if (null === $responseTime) {
            $responseTime = \microtime(true);
        }
        $duration = $responseTime - $apiCall->getRequestTime();

        $processedResponse = $this->processPayload($processors, $response);

        $apiCall
            ->setResponse($processedResponse)
            ->setDuration($duration)
        ;

        if (null !== $apiCall->getEndpoint() && null !== $this->options['endpoint_max_length']) {
            $apiCall->setEndpoint(\substr($apiCall->getEndpoint(), 0, $this->options['endpoint_max_length']));
        }
        if (null !== $apiCall->getMethod() && null !== $this->options['method_max_length']) {
            $apiCall->setMethod(\substr($apiCall->getMethod(), 0, $this->options['method_max_length']));
        }
        if (null !== $apiCall->getReference() && null !== $this->options['reference_max_length']) {
            $apiCall->setReference(\substr($apiCall->getReference(), 0, $this->options['reference_max_length']));
        }

        $this->handleApiCall($apiCall);
    }

    public function logPayload(
        Model\ApiCallInterface $apiCall,
        ?string $request,
        /* callable[]|callable|null */ $requestProcessors,
        ?string $response,
        /* callable[]|callable|null */ $responseProcessors
    ): ?string {
        $apiCall
            ->setRequest($this->processPayload($requestProcessors, $request))
            ->setResponse($this->processPayload($responseProcessors, $response))
        ;
        $this->handleApiCall($apiCall);

        return $apiCall->getId();
    }

    protected function handleApiCall(
        Model\ApiCallInterface $apiCall
    ): void {
        try {
            $this->handler->handle($apiCall);
        } catch (\Throwable $throwable) {
            // Log handler failures to a Psr-3 Logger if we have one
            $this->logHandlerThrowable($throwable, $apiCall);

            if (!$this->options['suppress_handler_exceptions']) {
                throw $throwable;
            }
        }
    }

    protected function logHandlerThrowable(
        \Throwable $throwable,
        Model\ApiCallInterface $apiCall
    ): void {
        if ($this->psrLogger === null) {
            return;
        }

        $this->psrLogger->warning($throwable->getMessage(), [
            'exception' => $throwable,
            'api_call' => $apiCall->getId(),
            'endpoint' => $apiCall->getEndpoint(),
            'method' => $apiCall->getMethod(),
            'reference' => $apiCall->getReference(),
            'request' => $apiCall->getRequest(),
            'response' => $apiCall->getResponse(),
        ]);
    }
}
