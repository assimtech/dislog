<?php

namespace spec\Assimtech\Dislog;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Assimtech\Dislog\Model\Factory\FactoryInterface;
use Assimtech\Dislog\Handler\HandlerInterface;
use Psr\Log\LoggerInterface;
use Assimtech\Dislog\Model\ApiCallInterface;
use Assimtech\Dislog\Processor\ProcessorInterface;
use Exception;

class ApiCallLoggerSpec extends ObjectBehavior
{
    function let(FactoryInterface $factory, HandlerInterface $handler, LoggerInterface $psrLogger)
    {
        $options = array();
        $this->beConstructedWith($factory, $handler, $options, $psrLogger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Dislog\ApiCallLogger');
    }

    function it_can_log_request(
        FactoryInterface $factory,
        HandlerInterface $handler,
        ApiCallInterface $apiCall
    ) {
        $request = 'my request';
        $endpoint = 'my endpoint';
        $method = 'my method';
        $reference = 'my reference';

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
        HandlerInterface $handler
    ) {
        $response = 'my response';

        $requestTime = 1.2;
        $apiCall->getRequestTime()->willReturn($requestTime);

        $apiCall->setResponse($response)->willReturn($apiCall);
        $apiCall->setDuration(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->shouldBeCalled();

        $this->logResponse($apiCall, $response);
    }

    function it_cant_log_request_without_a_psr_logger(
        FactoryInterface $factory,
        HandlerInterface $handler,
        ApiCallInterface $apiCall
    ) {
        $this->beConstructedWith($factory, $handler);

        $request = 'my request';
        $endpoint = 'my endpoint';
        $method = 'my method';
        $reference = 'my reference';

        $factory->create()->willReturn($apiCall);

        $apiCall->setRequest($request)->willReturn($apiCall);
        $apiCall->setEndpoint($endpoint)->willReturn($apiCall);
        $apiCall->setMethod($method)->willReturn($apiCall);
        $apiCall->setReference($reference)->willReturn($apiCall);
        $apiCall->setRequestTime(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->willThrow(new Exception('failed'));

        $this->logRequest($request, $endpoint, $method, $reference)->shouldReturn($apiCall);
    }

    function it_cant_log_request_with_a_psr_logger(
        FactoryInterface $factory,
        HandlerInterface $handler,
        ApiCallInterface $apiCall,
        LoggerInterface $psrLogger
    ) {
        $request = 'my request';
        $endpoint = 'my endpoint';
        $method = 'my method';
        $reference = 'my reference';

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
        $apiCall->getRequest()->willReturn($request);
        $apiCall->getResponse()->willReturn(null);

        $psrLogger->warning($exceptionMessage, array(
            'exception' => $e,
            'endpoint' => $endpoint,
            'method' => $method,
            'reference' => $reference,
            'request' => $request,
            'response' => null,
        ))->shouldBeCalled();

        $this->logRequest($request, $endpoint, $method, $reference)->shouldReturn($apiCall);
    }

    function it_cant_log_response_no_psr_logger(
        FactoryInterface $factory,
        HandlerInterface $handler,
        ApiCallInterface $apiCall
    ) {
        $this->beConstructedWith($factory, $handler);

        $response = 'my response';

        $requestTime = 1.2;
        $apiCall->getRequestTime()->willReturn($requestTime);

        $apiCall->setResponse($response)->willReturn($apiCall);
        $apiCall->setDuration(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->willThrow(new Exception('failed'));

        $this->logResponse($apiCall, $response);
    }

    function it_cant_log_response_with_psr_logger(
        HandlerInterface $handler,
        ApiCallInterface $apiCall,
        LoggerInterface $psrLogger
    ) {
        $request = 'my request';
        $endpoint = 'my endpoint';
        $method = 'my method';
        $reference = 'my reference';

        $response = 'my response';

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
        $apiCall->getRequest()->willReturn($request);
        $apiCall->getResponse()->willReturn($response);

        $psrLogger->warning($exceptionMessage, array(
            'exception' => $e,
            'endpoint' => $endpoint,
            'method' => $method,
            'reference' => $reference,
            'request' => $request,
            'response' => $response,
        ))->shouldBeCalled();

        $this->logResponse($apiCall, $response);
    }

    function it_cant_log_with_psr_logger_and_rethrow(
        FactoryInterface $factory,
        HandlerInterface $handler,
        ApiCallInterface $apiCall,
        LoggerInterface $psrLogger
    ) {
        $options = array(
            'suppressHandlerExceptions' => false,
        );

        $this->beConstructedWith($factory, $handler, $options, $psrLogger);

        $request = 'my request';
        $endpoint = 'my endpoint';
        $method = 'my method';
        $reference = 'my reference';

        $response = 'my response';

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
        $apiCall->getRequest()->willReturn($request);
        $apiCall->getResponse()->willReturn($response);

        $psrLogger->warning($exceptionMessage, array(
            'exception' => $e,
            'endpoint' => $endpoint,
            'method' => $method,
            'reference' => $reference,
            'request' => $request,
            'response' => $response,
        ))->shouldBeCalled();

        $this
            ->shouldThrow($e)
            ->during('logResponse', array(
                $apiCall,
                $response
            ))
        ;
    }

    function it_can_process_request(
        FactoryInterface $factory,
        HandlerInterface $handler,
        ApiCallInterface $apiCall,
        ProcessorInterface $processor
    ) {
        $request = 'my request';
        $endpoint = 'my endpoint';
        $method = 'my method';
        $reference = 'my reference';

        $factory->create()->willReturn($apiCall);

        $processor->__invoke($request)->willReturn($request);

        $apiCall->setRequest($request)->willReturn($apiCall);
        $apiCall->setEndpoint($endpoint)->willReturn($apiCall);
        $apiCall->setMethod($method)->willReturn($apiCall);
        $apiCall->setReference($reference)->willReturn($apiCall);
        $apiCall->setRequestTime(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->shouldBeCalled();

        $this->logRequest($request, $endpoint, $method, $reference, $processor)->shouldReturn($apiCall);
    }

    function it_can_process_response(
        ApiCallInterface $apiCall,
        HandlerInterface $handler,
        ProcessorInterface $processor
    ) {
        $response = 'my response';

        $requestTime = 1.2;
        $apiCall->getRequestTime()->willReturn($requestTime);

        $processor->__invoke($response)->willReturn($response);

        $apiCall->setResponse($response)->willReturn($apiCall);
        $apiCall->setDuration(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->shouldBeCalled();

        $this->logResponse($apiCall, $response, $processor);
    }

    function it_wont_process_null_response(
        ApiCallInterface $apiCall,
        HandlerInterface $handler,
        ProcessorInterface $processor
    ) {
        $response = null;

        $requestTime = 1.2;
        $apiCall->getRequestTime()->willReturn($requestTime);

        $apiCall->setResponse($response)->willReturn($apiCall);
        $apiCall->setDuration(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->shouldBeCalled();

        $processor->__invoke($response)->shouldNotBeCalled();

        $this->logResponse($apiCall, $response, $processor);
    }
}
