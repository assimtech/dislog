<?php

declare(strict_types=1);

namespace spec\Assimtech\Dislog;

use Assimtech\Dislog;
use GuzzleHttp\Psr7 as GuzzlePsr7;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http;
use Psr\Log\LoggerInterface;

class LoggingHttpClientSpec extends ObjectBehavior
{
    function let(
        Http\Client\ClientInterface $httpClient,
        Dislog\ApiCallLogger $apiCallLogger
    ) {
        $this->beConstructedWith($httpClient, $apiCallLogger);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Dislog\LoggingHttpClient::class);
    }

    function it_can_send_and_log(
        Http\Client\ClientInterface $httpClient,
        Dislog\ApiCallLogger $apiCallLogger,
        Http\Message\UriInterface $uri,
        Http\Message\RequestInterface $request,
        Http\Message\ResponseInterface $response,
        Dislog\Model\ApiCallInterface $apiCall
    ) {
        $this->beConstructedWith($httpClient, $apiCallLogger);

        $endpoint = '/my-endpoint';
        $appMethod = 'my method';
        $reference = 'my reference';
        $processors = [];

        $uri->__toString()->willReturn($endpoint);
        $uri->getHost()->willReturn('host.test');

        $request->getMethod()->willReturn('GET');
        $request->getRequestTarget()->willReturn($endpoint);
        $request->getProtocolVersion()->willReturn('3.0');
        $request->hasHeader('host')->willReturn(false);
        $request->getUri()->willReturn($uri);
        $request->getHeaders()->willReturn([]);
        $request->getBody()->willReturn('');

        $apiCallLogger->logRequest(
            Argument::type('string'),
            $endpoint,
            $appMethod,
            $reference,
            $processors
        )
            ->shouldBeCalled()
            ->willReturn($apiCall)
        ;

        $apiCallLogger->logResponse(
            $apiCall,
            Argument::type('string')
        )->shouldBeCalled();

        $httpClient->sendRequest($request)->willReturn($response);

        $response->getProtocolVersion()->willReturn('3.0');
        $response->getStatusCode()->willReturn(200);
        $response->getReasonPhrase()->willReturn('OK');
        $response->getHeaders()->willReturn([]);
        $response->getBody()->willReturn('');

        $this->sendRequest($request, $appMethod, $reference, $processors)->shouldReturn($response);
    }

    function it_can_send_without_logging(
        Http\Client\ClientInterface $httpClient,
        Dislog\ApiCallLogger $apiCallLogger,
        Http\Message\RequestInterface $request,
        Http\Message\ResponseInterface $response
    ) {
        $this->beConstructedWith($httpClient, $apiCallLogger);

        $httpClient->sendRequest($request)->willReturn($response);

        $this->sendRequest($request)->shouldReturn($response);
    }
}
