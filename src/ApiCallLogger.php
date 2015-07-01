<?php

namespace Assimtech\Dislog;

use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Exception;

class ApiCallLogger implements ApiCallLoggerInterface
{
    /**
     * @var Model\Factory\FactoryInterface $apiCallFactory
     */
    protected $apiCallFactory;

    /**
     * @var Handler\HandlerInterface $handler
     */
    protected $handler;

    /**
     * @var array $options
     */
    protected $options;

    /**
     * @var LoggerInterface|null $psrLogger
     */
    protected $psrLogger;

    /**
     * @param Model\Factory\FactoryInterface $apiCallFactory
     * @param Handler\HandlerInterface $handler
     * @param array $options
     * @param LoggerInterface|null $psrLogger
     */
    public function __construct(
        Model\Factory\FactoryInterface $apiCallFactory,
        Handler\HandlerInterface $handler,
        array $options = array(),
        LoggerInterface $psrLogger = null
    ) {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'suppressHandlerExceptions' => true,
        ));
        $resolver->setAllowedValues('suppressHandlerExceptions', array(
            true,
            false,
        ));

        $this->apiCallFactory = $apiCallFactory;
        $this->handler = $handler;
        $this->options = $resolver->resolve($options);
        $this->psrLogger = $psrLogger;
    }

    /**
     * @param callable[] $processors
     * @param string|null $payload
     * @return string|null
     */
    protected function processPayload($processors, $payload)
    {
        if ($payload === null) {
            return $payload;
        }

        $castedPayload = (string)$payload;

        if (!is_array($processors)) {
            $processors = array($processors);
        }

        foreach ($processors as $processor) {
            $payload = call_user_func($processor, $castedPayload);
        }

        return $payload;
    }

    /**
     * {@inheritdoc}
     */
    public function logRequest($request, $endpoint, $method, $reference = null, $processors = array())
    {
        $processedRequest = $this->processPayload($processors, $request);

        $apiCall = $this->apiCallFactory->create();
        $apiCall
            ->setRequest($processedRequest)
            ->setEndpoint($endpoint)
            ->setMethod($method)
            ->setReference($reference)
            ->setRequestTime(microtime(true))
        ;

        $this->handleApiCall($apiCall);

        return $apiCall;
    }

    /**
     * {@inheritdoc}
     */
    public function logResponse(Model\ApiCallInterface $apiCall, $response = null, $processors = array())
    {
        $duration = microtime(true) - $apiCall->getRequestTime();

        $processedResponse = $this->processPayload($processors, $response);

        $apiCall
            ->setResponse($processedResponse)
            ->setDuration($duration)
        ;

        $this->handleApiCall($apiCall);
    }

    /**
     * @param Model\ApiCallInterface $apiCall
     * @return void
     */
    protected function handleApiCall(Model\ApiCallInterface $apiCall)
    {
        try {
            $this->handler->handle($apiCall);
        } catch (Exception $exception) {
            // Log handler failures to a Psr-3 Logger if we have one
            $this->logHandlerException($exception, $apiCall);

            if (!$this->options['suppressHandlerExceptions']) {
                throw $exception;
            }
        }
    }

    /**
     * @param Exception $exception
     * @param Model\ApiCallInterface $apiCall
     * @return void
     */
    protected function logHandlerException(Exception $exception, Model\ApiCallInterface $apiCall)
    {
        if ($this->psrLogger === null) {
            return;
        }

        $this->psrLogger->warning($exception->getMessage(), array(
            'exception' => $exception,
            'endpoint' => $apiCall->getEndpoint(),
            'method' => $apiCall->getMethod(),
            'reference' => $apiCall->getReference(),
            'request' => $apiCall->getRequest(),
            'response' => $apiCall->getResponse(),
        ));
    }
}
