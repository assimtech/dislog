<?php

declare(strict_types=1);

namespace spec\Assimtech\Dislog\Serializer;

use Assimtech\Dislog\Model\ApiCallInterface;
use Assimtech\Dislog\Serializer\StringSerializer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StringSerializerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(StringSerializer::class);
    }

    function it_can_serialize(ApiCallInterface $apiCall, \DateTime $requestDateTime)
    {
        $duration = 1.23;
        $request = '<request />';
        $response = '<response />';
        $dateTimeFormatted = '1970-01-01T00:00:01+00:00';
        $identity = 'my id';
        $method = 'My::method()';
        $endpoint = 'https://my.endpoint';
        $reference = 'my ref';

        $apiCall->getDuration()->willReturn($duration);
        $apiCall->getRequest()->willReturn($request);
        $apiCall->getResponse()->willReturn($response);

        $requestDateTime->format('c')->willReturn($dateTimeFormatted);
        $apiCall->getRequestDateTime()->willReturn($requestDateTime);

        $apiCall->getId()->willReturn($identity);
        $apiCall->getMethod()->willReturn($method);
        $apiCall->getEndpoint()->willReturn($endpoint);
        $apiCall->getReference()->willReturn($reference);

        $data = json_encode([
           'duration' => $duration,
           'request' => $request,
           'response' => $response,
        ]);
        $expectedString = sprintf(
            '[%s] (%s) %s (%s) | %s - %s%s',
            $dateTimeFormatted,
            $identity,
            $method,
            $endpoint,
            $reference,
            $data,
            "\n"
        );

        $this->__invoke($apiCall)->shouldReturn($expectedString);
    }
}
