<?php

namespace spec\Assimtech\Dislog;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Assimtech\Dislog\Model\Factory\FactoryInterface;
use Assimtech\Dislog\Handler\HandlerInterface;
use Psr\Log\LoggerInterface;
use Assimtech\Dislog\Model\ApiCallInterface;
use Exception;

class ApiCallLoggerSpec extends ObjectBehavior
{
    function let(FactoryInterface $factory, HandlerInterface $handler, LoggerInterface $psrLogger)
    {
        $this->beConstructedWith($factory, $handler, $psrLogger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Dislog\ApiCallLogger');
    }

    function it_can_log_request(
        FactoryInterface $factory,
        HandlerInterface $handler,
        ApiCallInterface $apiCall,
        $request,
        $endpoint,
        $method,
        $reference
    ) {
        $factory->create()->willReturn($apiCall);

        $apiCall->setRequest($request)->willReturn($apiCall);
        $apiCall->setEndpoint($endpoint)->willReturn($apiCall);
        $apiCall->setMethod($method)->willReturn($apiCall);
        $apiCall->setReference($reference)->willReturn($apiCall);
        $apiCall->setRequestTime(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->shouldBeCalled();

        $this->logRequest($request, $endpoint, $method, $reference)->shouldReturn($apiCall);
    }

    function it_can_log_response(
        ApiCallInterface $apiCall,
        HandlerInterface $handler,
        $response
    ) {
        $requestTime = 1.2;
        $apiCall->getRequestTime()->willReturn($requestTime);

        $apiCall->setResponse($response)->willReturn($apiCall);
        $apiCall->setDuration(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->shouldBeCalled();

        $this->logResponse($apiCall, $response);
    }

    function it_cant_log_request_no_psr_logger(
        FactoryInterface $factory,
        HandlerInterface $handler,
        ApiCallInterface $apiCall,
        $request,
        $endpoint,
        $method,
        $reference
    ) {
        $this->beConstructedWith($factory, $handler);

        $factory->create()->willReturn($apiCall);

        $apiCall->setRequest($request)->willReturn($apiCall);
        $apiCall->setEndpoint($endpoint)->willReturn($apiCall);
        $apiCall->setMethod($method)->willReturn($apiCall);
        $apiCall->setReference($reference)->willReturn($apiCall);
        $apiCall->setRequestTime(Argument::type('float'))->willReturn($apiCall);

        $exceptionMessage = 'failed';
        $e = new Exception($exceptionMessage);
        $handler->handle($apiCall)->willThrow($e);

        $this->logRequest($request, $endpoint, $method, $reference)->shouldReturn($apiCall);
    }

    function it_cant_log_request_with_psr_logger(
        FactoryInterface $factory,
        HandlerInterface $handler,
        ApiCallInterface $apiCall,
        $request,
        $endpoint,
        $method,
        $reference,
        LoggerInterface $psrLogger
    ) {
        $factory->create()->willReturn($apiCall);

        $apiCall->setRequest($request)->willReturn($apiCall);
        $apiCall->setEndpoint($endpoint)->willReturn($apiCall);
        $apiCall->setMethod($method)->willReturn($apiCall);
        $apiCall->setReference($reference)->willReturn($apiCall);
        $apiCall->setRequestTime(Argument::type('float'))->willReturn($apiCall);

        $exceptionMessage = 'failed';
        $e = new Exception($exceptionMessage);
        $handler->handle($apiCall)->willThrow($e);

        $apiCall->getEndpoint()->willReturn($endpoint);
        $apiCall->getMethod()->willReturn($method);
        $apiCall->getReference()->willReturn($reference);

        $psrLogger->warning($exceptionMessage, array(
            'exception' => $e,
            'endpoint' => $endpoint,
            'method' => $method,
            'reference' => $reference,
        ))->shouldBeCalled();

        $this->logRequest($request, $endpoint, $method, $reference)->shouldReturn($apiCall);
    }

    function it_cant_log_response_no_psr_logger(
        FactoryInterface $factory,
        HandlerInterface $handler,
        ApiCallInterface $apiCall,
        $response
    ) {
        $this->beConstructedWith($factory, $handler);

        $requestTime = 1.2;
        $apiCall->getRequestTime()->willReturn($requestTime);

        $apiCall->setResponse($response)->willReturn($apiCall);
        $apiCall->setDuration(Argument::type('float'))->willReturn($apiCall);

        $exceptionMessage = 'failed';
        $e = new Exception($exceptionMessage);
        $handler->handle($apiCall)->willThrow($e);

        $this->logResponse($apiCall, $response);
    }

    function it_cant_log_response_with_psr_logger(
        HandlerInterface $handler,
        ApiCallInterface $apiCall,
        $response,
        $endpoint,
        $method,
        $reference,
        LoggerInterface $psrLogger
    ) {
        $requestTime = 1.2;
        $apiCall->getRequestTime()->willReturn($requestTime);

        $apiCall->setResponse($response)->willReturn($apiCall);
        $apiCall->setDuration(Argument::type('float'))->willReturn($apiCall);

        $exceptionMessage = 'failed';
        $e = new Exception($exceptionMessage);
        $handler->handle($apiCall)->willThrow($e);

        $apiCall->getEndpoint()->willReturn($endpoint);
        $apiCall->getMethod()->willReturn($method);
        $apiCall->getReference()->willReturn($reference);

        $psrLogger->warning($exceptionMessage, array(
            'exception' => $e,
            'endpoint' => $endpoint,
            'method' => $method,
            'reference' => $reference,
        ))->shouldBeCalled();

        $this->logResponse($apiCall, $response);
    }
}
