<?php

declare(strict_types=1);

namespace spec\Assimtech\Dislog;

use Assimtech\Dislog;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ApiCallLoggerSpec extends ObjectBehavior
{
    function let(
        Dislog\Factory\FactoryInterface $factory,
        Dislog\Handler\HandlerInterface $handler,
        \Psr\Log\LoggerInterface $psrLogger
    ) {
        $options = [];
        $this->beConstructedWith($factory, $handler, $options, $psrLogger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Dislog\ApiCallLogger::class);
    }

    function it_can_log_request(
        Dislog\Factory\FactoryInterface $factory,
        Dislog\Handler\HandlerInterface $handler,
        Dislog\Model\ApiCallInterface $apiCall
    ) {
        $request = 'my request';
        $endpoint = 'my endpoint';
        $appMethod = 'my method';
        $reference = 'my reference';

        $factory->create()->willReturn($apiCall);

        $apiCall->setRequest($request)->willReturn($apiCall);
        $apiCall->setEndpoint($endpoint)->willReturn($apiCall);
        $apiCall->setMethod($appMethod)->willReturn($apiCall);
        $apiCall->setReference($reference)->willReturn($apiCall);
        $apiCall->setRequestTime(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->shouldBeCalled();

        $this->logRequest($request, $endpoint, $appMethod, $reference)->shouldReturn($apiCall);
    }

    function it_can_log_response(
        Dislog\Model\ApiCallInterface $apiCall,
        Dislog\Handler\HandlerInterface $handler
    ) {
        $response = 'my response';

        $requestTime = 1.2;
        $apiCall->getRequestTime()->willReturn($requestTime);

        $apiCall->getEndpoint()->willReturn(null);
        $apiCall->getMethod()->willReturn(null);
        $apiCall->getReference()->willReturn(null);

        $apiCall->setResponse($response)->willReturn($apiCall);
        $apiCall->setDuration(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->shouldBeCalled();

        $this->logResponse($apiCall, $response);
    }

    function it_cant_log_request_without_a_psr_logger(
        Dislog\Factory\FactoryInterface $factory,
        Dislog\Handler\HandlerInterface $handler,
        Dislog\Model\ApiCallInterface $apiCall
    ) {
        $this->beConstructedWith($factory, $handler);

        $request = 'my request';
        $endpoint = 'my endpoint';
        $appMethod = 'my method';
        $reference = 'my reference';

        $factory->create()->willReturn($apiCall);

        $apiCall->setRequest($request)->willReturn($apiCall);
        $apiCall->setEndpoint($endpoint)->willReturn($apiCall);
        $apiCall->setMethod($appMethod)->willReturn($apiCall);
        $apiCall->setReference($reference)->willReturn($apiCall);
        $apiCall->setRequestTime(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->willThrow(new \Exception('failed'));

        $this->logRequest($request, $endpoint, $appMethod, $reference)->shouldReturn($apiCall);
    }

    function it_cant_log_request_with_a_psr_logger(
        Dislog\Factory\FactoryInterface $factory,
        Dislog\Handler\HandlerInterface $handler,
        Dislog\Model\ApiCallInterface $apiCall,
        \Psr\Log\LoggerInterface $psrLogger
    ) {
        $apiCallId = 'my id';
        $request = 'my request';
        $endpoint = 'my endpoint';
        $appMethod = 'my method';
        $reference = 'my reference';

        $factory->create()->willReturn($apiCall);

        $apiCall->setRequest($request)->willReturn($apiCall);
        $apiCall->setEndpoint($endpoint)->willReturn($apiCall);
        $apiCall->setMethod($appMethod)->willReturn($apiCall);
        $apiCall->setReference($reference)->willReturn($apiCall);
        $apiCall->setRequestTime(Argument::type('float'))->willReturn($apiCall);

        $exceptionMessage = 'failed';
        $e = new \Exception($exceptionMessage);
        $handler->handle($apiCall)->willThrow($e);

        $apiCall->getId()->willReturn($apiCallId);
        $apiCall->getEndpoint()->willReturn($endpoint);
        $apiCall->getMethod()->willReturn($appMethod);
        $apiCall->getReference()->willReturn($reference);
        $apiCall->getRequest()->willReturn($request);
        $apiCall->getResponse()->willReturn(null);

        $psrLogger->warning($exceptionMessage, [
            'exception' => $e,
            'api_call' => $apiCallId,
            'endpoint' => $endpoint,
            'method' => $appMethod,
            'reference' => $reference,
            'request' => $request,
            'response' => null,
        ])->shouldBeCalled();

        $this->logRequest($request, $endpoint, $appMethod, $reference)->shouldReturn($apiCall);
    }

    function it_cant_log_response_no_psr_logger(
        Dislog\Factory\FactoryInterface $factory,
        Dislog\Handler\HandlerInterface $handler,
        Dislog\Model\ApiCallInterface $apiCall
    ) {
        $this->beConstructedWith($factory, $handler);

        $response = 'my response';

        $requestTime = 1.2;
        $apiCall->getRequestTime()->willReturn($requestTime);

        $apiCall->getEndpoint()->willReturn(null);
        $apiCall->getMethod()->willReturn(null);
        $apiCall->getReference()->willReturn(null);

        $apiCall->setResponse($response)->willReturn($apiCall);
        $apiCall->setDuration(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->willThrow(new \Exception('failed'));

        $this->logResponse($apiCall, $response);
    }

    function it_cant_log_response_with_psr_logger(
        Dislog\Handler\HandlerInterface $handler,
        Dislog\Model\ApiCallInterface $apiCall,
        \Psr\Log\LoggerInterface $psrLogger
    ) {
        $apiCallId = 'my id';
        $request = 'my request';
        $endpoint = 'my endpoint';
        $appMethod = 'my method';
        $reference = 'my reference';

        $response = 'my response';

        $requestTime = 1.2;
        $apiCall->getRequestTime()->willReturn($requestTime);

        $apiCall->setResponse($response)->willReturn($apiCall);
        $apiCall->setDuration(Argument::type('float'))->willReturn($apiCall);

        $exceptionMessage = 'failed';
        $e = new \Exception($exceptionMessage);
        $handler->handle($apiCall)->willThrow($e);

        $apiCall->getId()->willReturn($apiCallId);
        $apiCall->getEndpoint()->willReturn($endpoint);
        $apiCall->getMethod()->willReturn($appMethod);
        $apiCall->getReference()->willReturn($reference);
        $apiCall->getRequest()->willReturn($request);
        $apiCall->getResponse()->willReturn($response);

        $psrLogger->warning($exceptionMessage, [
            'exception' => $e,
            'api_call' => $apiCallId,
            'endpoint' => $endpoint,
            'method' => $appMethod,
            'reference' => $reference,
            'request' => $request,
            'response' => $response,
        ])->shouldBeCalled();

        $this->logResponse($apiCall, $response);
    }

    function it_cant_log_with_psr_logger_and_rethrow(
        Dislog\Factory\FactoryInterface $factory,
        Dislog\Handler\HandlerInterface $handler,
        Dislog\Model\ApiCallInterface $apiCall,
        \Psr\Log\LoggerInterface $psrLogger
    ) {
        $options = [
            'suppress_handler_exceptions' => false,
        ];

        $this->beConstructedWith($factory, $handler, $options, $psrLogger);

        $apiCallId = 'my id';
        $request = 'my request';
        $endpoint = 'my endpoint';
        $appMethod = 'my method';
        $reference = 'my reference';

        $response = 'my response';

        $requestTime = 1.2;
        $apiCall->getRequestTime()->willReturn($requestTime);

        $apiCall->setResponse($response)->willReturn($apiCall);
        $apiCall->setDuration(Argument::type('float'))->willReturn($apiCall);

        $exceptionMessage = 'failed';
        $e = new \Exception($exceptionMessage);
        $handler->handle($apiCall)->willThrow($e);

        $apiCall->getId()->willReturn($apiCallId);
        $apiCall->getEndpoint()->willReturn($endpoint);
        $apiCall->getMethod()->willReturn($appMethod);
        $apiCall->getReference()->willReturn($reference);
        $apiCall->getRequest()->willReturn($request);
        $apiCall->getResponse()->willReturn($response);

        $psrLogger->warning($exceptionMessage, [
            'exception' => $e,
            'api_call' => $apiCallId,
            'endpoint' => $endpoint,
            'method' => $appMethod,
            'reference' => $reference,
            'request' => $request,
            'response' => $response,
        ])->shouldBeCalled();

        $this
            ->shouldThrow($e)
            ->during('logResponse', [
                $apiCall,
                $response,
            ])
        ;
    }

    function it_can_process_request(
        Dislog\Factory\FactoryInterface $factory,
        Dislog\Handler\HandlerInterface $handler,
        Dislog\Model\ApiCallInterface $apiCall,
        Dislog\Processor\ProcessorInterface $processor
    ) {
        $request = 'my request';
        $endpoint = 'my endpoint';
        $appMethod = 'my method';
        $reference = 'my reference';

        $factory->create()->willReturn($apiCall);

        $processor->__invoke($request)->willReturn($request);

        $apiCall->setRequest($request)->willReturn($apiCall);
        $apiCall->setEndpoint($endpoint)->willReturn($apiCall);
        $apiCall->setMethod($appMethod)->willReturn($apiCall);
        $apiCall->setReference($reference)->willReturn($apiCall);
        $apiCall->setRequestTime(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->shouldBeCalled();

        $this->logRequest($request, $endpoint, $appMethod, $reference, $processor)->shouldReturn($apiCall);
    }

    function it_can_process_response(
        Dislog\Model\ApiCallInterface $apiCall,
        Dislog\Handler\HandlerInterface $handler,
        Dislog\Processor\ProcessorInterface $processor
    ) {
        $response = 'my response';

        $requestTime = 1.2;
        $apiCall->getRequestTime()->willReturn($requestTime);

        $apiCall->getEndpoint()->willReturn(null);
        $apiCall->getMethod()->willReturn(null);
        $apiCall->getReference()->willReturn(null);

        $processor->__invoke($response)->willReturn($response);

        $apiCall->setResponse($response)->willReturn($apiCall);
        $apiCall->setDuration(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->shouldBeCalled();

        $this->logResponse($apiCall, $response, $processor);
    }

    function it_wont_process_null_response(
        Dislog\Model\ApiCallInterface $apiCall,
        Dislog\Handler\HandlerInterface $handler,
        Dislog\Processor\ProcessorInterface $processor
    ) {
        $response = null;

        $requestTime = 1.2;
        $apiCall->getRequestTime()->willReturn($requestTime);

        $apiCall->getEndpoint()->willReturn(null);
        $apiCall->getMethod()->willReturn(null);
        $apiCall->getReference()->willReturn(null);

        $apiCall->setResponse($response)->willReturn($apiCall);
        $apiCall->setDuration(Argument::type('float'))->willReturn($apiCall);

        $handler->handle($apiCall)->shouldBeCalled();

        $processor->__invoke($response)->shouldNotBeCalled();

        $this->logResponse($apiCall, $response, $processor);
    }
}
