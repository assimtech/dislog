<?php

namespace spec\Assimtech\Dislog\Handler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Assimtech\Dislog\Identity\IdentityGeneratorInterface;
use Assimtech\Dislog\Model\ApiCallInterface;
use DateTime;
use RuntimeException;

class StreamSpec extends ObjectBehavior
{
    /**
     * @var resource $stream
     */
    protected $stream;

    function let(IdentityGeneratorInterface $identityGenerator)
    {
        $this->stream = fopen('php://temp', 'r+');
        $this->beConstructedWith($identityGenerator, $this->stream);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Dislog\Handler\Stream');
    }

    function it_can_handle_new_apicall(
        ApiCallInterface $apiCall,
        IdentityGeneratorInterface $identityGenerator,
        DateTime $requestDateTime
    ) {
        $identity = 'my-id';
        $endpoint = 'my-endpoint';
        $requestTime = 987654321.098;
        $request = '<request />';
        $requestDateTimeFormatted = '2004-02-12T15:19:21+00:00';
        $method = 'my-method';
        $reference = 'my-reference';

        $apiCall->getId()->willReturn(null);
        $identityGenerator->getIdentity()->willReturn($identity);
        $apiCall->setId($identity)->shouldBeCalled();

        $apiCall->getEndpoint()->willReturn($endpoint);
        $apiCall->getRequestTime()->willReturn($requestTime);
        $apiCall->getDuration()->willReturn(null);
        $apiCall->getRequest()->willReturn($request);
        $apiCall->getResponse()->willReturn(null);

        $requestDateTime->format('c')->willReturn($requestDateTimeFormatted);
        $apiCall->getRequestDateTime()->willReturn($requestDateTime);
        $apiCall->getMethod()->willReturn($method);
        $apiCall->getReference()->willReturn($reference);

        $this->handle($apiCall);

        $expectedData = '[2004-02-12T15:19:21+00:00]'
            . ' ()'
            . ' my-method'
            . ' | my-reference'
            . ' - {'
            . '"endpoint":"my-endpoint",'
            . '"requestTime":987654321.098,'
            . '"duration":null,'
            . '"request":"<request \/>",'
            . '"response":null'
            . '}' . PHP_EOL;
        $writtenData = stream_get_contents($this->stream, -1, 0);
        if ($writtenData !== $expectedData) {
            throw new RuntimeException(sprintf(
                "Written data did not match expected data:\nWritten: %s\nExpected: %s",
                $writtenData,
                $expectedData
            ));
        }
    }

    function it_throws_on_write_failure(
        ApiCallInterface $apiCall,
        IdentityGeneratorInterface $identityGenerator,
        DateTime $requestDateTime
    ) {
        fclose($this->stream);

        $identity = 'my-id';
        $endpoint = 'my-endpoint';
        $requestTime = 987654321.098;
        $request = '<request />';
        $requestDateTimeFormatted = '2004-02-12T15:19:21+00:00';
        $method = 'my-method';
        $reference = 'my-reference';

        $apiCall->getId()->willReturn(null);
        $identityGenerator->getIdentity()->willReturn($identity);
        $apiCall->setId($identity)->shouldBeCalled();

        $apiCall->getEndpoint()->willReturn($endpoint);
        $apiCall->getRequestTime()->willReturn($requestTime);
        $apiCall->getDuration()->willReturn(null);
        $apiCall->getRequest()->willReturn($request);
        $apiCall->getResponse()->willReturn(null);

        $requestDateTime->format('c')->willReturn($requestDateTimeFormatted);
        $apiCall->getRequestDateTime()->willReturn($requestDateTime);
        $apiCall->getMethod()->willReturn($method);
        $apiCall->getReference()->willReturn($reference);

        $this
            ->shouldThrow(new RuntimeException('Failed to write to stream'))
            ->during('handle', array(
                $apiCall,
            ))
        ;
    }

    function it_can_handle_existing_apicall(
        ApiCallInterface $apiCall,
        DateTime $requestDateTime
    ) {
        $identity = 'my-id';
        $endpoint = 'my-endpoint';
        $requestTime = 987654321.098;
        $duration = 1.23456789;
        $request = '<request />';
        $response = '<response />';
        $requestDateTimeFormatted = '2004-02-12T15:19:21+00:00';
        $method = 'my-method';
        $reference = 'my-reference';

        $apiCall->getId()->willReturn($identity);
        $apiCall->getEndpoint()->willReturn($endpoint);
        $apiCall->getRequestTime()->willReturn($requestTime);
        $apiCall->getDuration()->willReturn($duration);
        $apiCall->getRequest()->willReturn($request);
        $apiCall->getResponse()->willReturn($response);

        $requestDateTime->format('c')->willReturn($requestDateTimeFormatted);
        $apiCall->getRequestDateTime()->willReturn($requestDateTime);
        $apiCall->getMethod()->willReturn($method);
        $apiCall->getReference()->willReturn($reference);

        $this->handle($apiCall);

        $expectedData = '[2004-02-12T15:19:21+00:00]'
            . ' (my-id)'
            . ' my-method'
            . ' | my-reference'
            . ' - {'
            . '"endpoint":"my-endpoint",'
            . '"requestTime":987654321.098,'
            . '"duration":1.23456789,'
            . '"request":"<request \/>",'
            . '"response":"<response \/>"'
            . '}' . PHP_EOL;
        $writtenData = stream_get_contents($this->stream, -1, 0);
        if ($writtenData !== $expectedData) {
            throw new RuntimeException(sprintf(
                "Written data did not match expected data:\nWritten: %s\nExpected: %s",
                $writtenData,
                $expectedData
            ));
        }
    }
}
