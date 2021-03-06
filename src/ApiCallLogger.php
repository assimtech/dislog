<?php

declare(strict_types=1);

namespace Assimtech\Dislog;

use Assimtech\Dislog\Model\ApiCallInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApiCallLogger implements ApiCallLoggerInterface
{
    protected $apiCallFactory;
    protected $handler;
    protected $options;
    protected $psrLogger;

    public function __construct(
        Factory\FactoryInterface $apiCallFactory,
        Handler\HandlerInterface $handler,
        array $options = [],
        ?LoggerInterface $psrLogger = null
    ) {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'suppress_handler_exceptions' => true,
        ]);
        $resolver->setAllowedValues('suppress_handler_exceptions', [
            true,
            false,
        ]);

        $this->apiCallFactory = $apiCallFactory;
        $this->handler = $handler;
        $this->options = $resolver->resolve($options);
        $this->psrLogger = $psrLogger;
    }

    protected function processPayload(
        /* callable[]|callable|null */ $processors,
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

    public function logRequest(
        ?string $request,
        ?string $endpoint,
        ?string $appMethod,
        ?string $reference = null,
        /* callable[]|callable|null */ $processors = null,
        ?float $requestTime = null
    ): ApiCallInterface {
        $processedRequest = $this->processPayload($processors, $request);

        $apiCall = $this->apiCallFactory->create();
        $apiCall
            ->setRequest($processedRequest)
            ->setEndpoint($endpoint)
            ->setMethod($appMethod)
            ->setReference($reference)
            ->setRequestTime($requestTime ?? \microtime(true))
        ;

        $this->handleApiCall($apiCall);

        return $apiCall;
    }

    public function logResponse(
        ApiCallInterface $apiCall,
        ?string $response = null,
        /* callable[]|callable|null */ $processors = null
    ): void {
        $duration = \microtime(true) - $apiCall->getRequestTime();

        $processedResponse = $this->processPayload($processors, $response);

        $apiCall
            ->setResponse($processedResponse)
            ->setDuration($duration)
        ;

        $this->handleApiCall($apiCall);
    }

    protected function handleApiCall(
        ApiCallInterface $apiCall
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
        ApiCallInterface $apiCall
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
