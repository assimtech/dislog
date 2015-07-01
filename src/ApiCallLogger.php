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
     * @param Model\ApiCallInterface $apiCall
     * @return void
     */
    protected function handleApiCall(Model\ApiCallInterface $apiCall)
    {
        try {
            $this->handler->handle($apiCall);
        } catch (Exception $e) {
            // Log handler failures to a Psr-3 Logger if we have one
            if ($this->psrLogger !== null) {
                $this->psrLogger->warning($e->getMessage(), array(
                    'exception' => $e,
                    'endpoint' => $apiCall->getEndpoint(),
                    'method' => $apiCall->getMethod(),
                    'reference' => $apiCall->getReference(),
                    'request' => $apiCall->getRequest(),
                    'response' => $apiCall->getResponse(),
                ));
            }

            if (!$this->options['suppressHandlerExceptions']) {
                throw $e;
            }
        }
    }
}
