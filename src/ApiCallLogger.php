<?php

namespace Assimtech\Dislog;

use Assimtech\Dislog\Model\ApiCallInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Traversable;
use Exception;

class ApiCallLogger implements ApiCallLoggerInterface
{
    /**
     * @var Factory\FactoryInterface $apiCallFactory
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
     * @var callable[] $aliasedProcessors
     *      An associative array of processors (alias => callable).
     *      Adding processor aliases allows the called to reference the alias names of the processors to invoke when
     *      calling logRequest / logResponse. This allows setup of processors for re-use and easier referencing.
     */
    protected $aliasedProcessors;

    /**
     * @param Factory\FactoryInterface  $apiCallFactory
     * @param Handler\HandlerInterface  $handler
     * @param array                     $options
     * @param LoggerInterface|null      $psrLogger
     */
    public function __construct(
        Factory\FactoryInterface $apiCallFactory,
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

        $this->aliasedProcessors = array();
    }

    /**
     * @param string    $alias
     * @param callable  $processor
     * @return self
     */
    public function setAliasedProcessor($alias, /* callable */ $processor)
    {
        $this->aliasedProcessors[$alias] = $processor;

        return $this;
    }

    /**
     * @param callable[]    $processors
     * @param string|null   $payload
     * @return string|null
     */
    protected function processPayload($processors, $payload)
    {
        if ($payload === null) {
            return $payload;
        }

        $strPayload = (string)$payload;

        if (!is_array($processors) && !$processors instanceof Traversable) {
            $processors = array($processors);
        }

        foreach ($processors as $processor) {
            if (is_string($processor) && isset($this->aliasedProcessors[$processor])) {
                $processor = $this->aliasedProcessors[$processor];
            }

            $strPayload = call_user_func($processor, $strPayload);
        }

        return $strPayload;
    }

    /**
     * {@inheritdoc}
     *
     * Processors are allowed to be an aliased string if setup previously using setAliasedProcessor
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
     *
     * Processors are allowed to be an aliased string if setup previously using setAliasedProcessor
     */
    public function logResponse(ApiCallInterface $apiCall, $response = null, $processors = array())
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
     * @param ApiCallInterface $apiCall
     * @return void
     */
    protected function handleApiCall(ApiCallInterface $apiCall)
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
     * @param ApiCallInterface $apiCall
     * @return void
     */
    protected function logHandlerException(Exception $exception, ApiCallInterface $apiCall)
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
