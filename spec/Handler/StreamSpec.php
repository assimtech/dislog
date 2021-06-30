<?php

declare(strict_types=1);

namespace spec\Assimtech\Dislog\Handler;

use Assimtech\Dislog;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StreamSpec extends ObjectBehavior
{
    function it_is_initializable(
        Dislog\Identity\IdentityGeneratorInterface $identityGenerator,
        Dislog\Serializer\SerializerInterface $serializer
    ) {
        $stream = 'php://temp';
        $this->beConstructedWith($stream, $identityGenerator, $serializer);

        $this->shouldHaveType(Dislog\Handler\Stream::class);
    }

    function it_can_handle_new_apicall(
        Dislog\Identity\IdentityGeneratorInterface $identityGenerator,
        Dislog\Serializer\SerializerInterface $serializer,
        Dislog\Model\ApiCallInterface $apiCall
    ) {
        $stream = fopen('php://temp', 'a+');
        $this->beConstructedWith($stream, $identityGenerator, $serializer);

        $identity = 'my id';
        $serializedApiCall = 'my api call';

        $apiCall->getId()->willReturn(null);
        $identityGenerator->getIdentity()->willReturn($identity);
        $apiCall->setId($identity)->shouldBeCalled();

        $serializer->__invoke($apiCall)->willReturn($serializedApiCall);

        $this->handle($apiCall);

        $writtenData = stream_get_contents($stream, -1, 0);
        if ($writtenData !== $serializedApiCall) {
            throw new \RuntimeException(sprintf(
                "Written data did not match expected data:\nWritten: %s\nExpected: %s",
                $writtenData,
                $serializedApiCall
            ));
        }
    }

    function it_can_handle_existing_apicall(
        Dislog\Identity\IdentityGeneratorInterface $identityGenerator,
        Dislog\Serializer\SerializerInterface $serializer,
        Dislog\Model\ApiCallInterface $apiCall
    ) {
        $stream = fopen('php://temp', 'a+');
        $this->beConstructedWith($stream, $identityGenerator, $serializer);

        $identity = 'my id';
        $serializedApiCall = 'my api call';

        $apiCall->getId()->willReturn($identity);

        $serializer->__invoke($apiCall)->willReturn($serializedApiCall);

        $this->handle($apiCall);

        $writtenData = stream_get_contents($stream, -1, 0);
        if ($writtenData !== $serializedApiCall) {
            throw new \RuntimeException(sprintf(
                "Written data did not match expected data:\nWritten: %s\nExpected: %s",
                $writtenData,
                $serializedApiCall
            ));
        }
    }
}
