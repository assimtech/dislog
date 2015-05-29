<?php

namespace Assimtech\Dislog;

use Psr\Log\LoggerInterface;
use Exception;

class ApiCallLogger implements ApiCallLoggerInterface
{
    /**
     * @var \Assimtech\Dislog\Model\Factory\FactoryInterface $apiCallFactory
     */
    protected $apiCallFactory;

    /**
     * @var \Assimtech\Dislog\Handler\HandlerInterface $handler
     */
    protected $handler;

    /**
     * @var \Psr\Log\LoggerInterface|null $psrLogger
     */
    protected $psrLogger;

    /**
     * @param \Assimtech\Dislog\Model\Factory\FactoryInterface $apiCallFactory
     * @param \Assimtech\Dislog\Handler\HandlerInterface $handler
     * @param \Psr\Log\LoggerInterface|null $psrLogger
     */
    public function __construct(
        Model\Factory\FactoryInterface $apiCallFactory,
        Handler\HandlerInterface $handler,
        LoggerInterface $psrLogger = null
    ) {
        $this->apiCallFactory = $apiCallFactory;
        $this->handler = $handler;
        $this->psrLogger = $psrLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function logRequest($request, $endpoint, $method, $reference = null)
    {
        $apiCall = $this->apiCallFactory->create();
        $apiCall
            ->setRequest($request)
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
    public function logResponse(Model\ApiCallInterface $apiCall, $response = null)
    {
        $duration = microtime(true) - $apiCall->getRequestTime();
        $apiCall
            ->setResponse($response)
            ->setDuration($duration)
        ;

        $this->handleApiCall($apiCall);
    }

    /**
     * @param \Assimtech\Dislog\Model\ApiCallInterface $apiCall
     * @return void
     */
    protected function handleApiCall(Model\ApiCallInterface $apiCall)
    {
        try {
            $this->handler->handle($apiCall);
        } catch (Exception $e) {
            if ($this->psrLogger === null) {
                return;
            }

            $this->psrLogger->warning($e->getMessage(), array(
                'exception' => $e,
                'endpoint' => $apiCall->getEndpoint(),
                'method' => $apiCall->getMethod(),
                'reference' => $apiCall->getReference(),
            ));
        }
    }
}
