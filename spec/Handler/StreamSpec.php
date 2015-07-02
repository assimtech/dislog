<?php

namespace spec\Assimtech\Dislog\Handler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Assimtech\Dislog\Identity\IdentityGeneratorInterface;
use Assimtech\Dislog\Serializer\SerializerInterface;
use Assimtech\Dislog\ApiCallInterface;
use DateTime;
use RuntimeException;

class StreamSpec extends ObjectBehavior
{
    /**
     * @var resource $stream
     */
    protected $stream;

    function let(IdentityGeneratorInterface $identityGenerator, SerializerInterface $serializer)
    {
        $this->stream = fopen('php://temp', 'a+');
        $this->beConstructedWith($this->stream, $identityGenerator, $serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Dislog\Handler\Stream');
    }

    function it_can_handle_new_apicall(
        ApiCallInterface $apiCall,
        IdentityGeneratorInterface $identityGenerator,
        SerializerInterface $serializer,
        DateTime $requestDateTime
    ) {
        $identity = 'my id';
        $serializedApiCall = 'my api call';

        $apiCall->getId()->willReturn(null);
        $identityGenerator->getIdentity()->willReturn($identity);
        $apiCall->setId($identity)->shouldBeCalled();

        $serializer->__invoke($apiCall)->willReturn($serializedApiCall);

        $this->handle($apiCall);

        $writtenData = stream_get_contents($this->stream, -1, 0);
        if ($writtenData !== $serializedApiCall) {
            throw new RuntimeException(sprintf(
                "Written data did not match expected data:\nWritten: %s\nExpected: %s",
                $writtenData,
                $serializedApiCall
            ));
        }
    }

    function it_can_handle_existing_apicall(
        ApiCallInterface $apiCall,
        SerializerInterface $serializer,
        DateTime $requestDateTime
    ) {
        $identity = 'my id';
        $serializedApiCall = 'my api call';

        $apiCall->getId()->willReturn($identity);

        $serializer->__invoke($apiCall)->willReturn($serializedApiCall);

        $this->handle($apiCall);

        $writtenData = stream_get_contents($this->stream, -1, 0);
        if ($writtenData !== $serializedApiCall) {
            throw new RuntimeException(sprintf(
                "Written data did not match expected data:\nWritten: %s\nExpected: %s",
                $writtenData,
                $serializedApiCall
            ));
        }
    }
}
