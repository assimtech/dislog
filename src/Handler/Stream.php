<?php

namespace Assimtech\Dislog\Handler;

use Assimtech\Dislog\Identity\IdentityGeneratorInterface;
use Assimtech\Dislog\Serializer\SerializerInterface;
use Assimtech\Dislog\ApiCallInterface;

class Stream implements HandlerInterface
{
    /**
     * @var resource $stream
     */
    protected $stream;

    /**
     * @var IdentityGeneratorInterface $identityGenerator
     */
    protected $identityGenerator;

    /**
     * @var SerializerInterface $serializer
     */
    protected $serializer;

    /**
     * @param resource|string $stream
     * @param IdentityGeneratorInterface $identityGenerator
     * @param SerializerInterface $serializer
     */
    public function __construct(
        $stream,
        IdentityGeneratorInterface $identityGenerator,
        SerializerInterface $serializer
    ) {
        if (is_resource($stream)) {
            $this->stream = $stream;
        } else {
            $this->stream = fopen($stream, 'a');
        }
        $this->identityGenerator = $identityGenerator;
        $this->serializer = $serializer;
    }

    public function __destruct()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ApiCallInterface $apiCall)
    {
        if ($apiCall->getId() === null) {
            $id = $this->identityGenerator->getIdentity();
            $apiCall->setId($id);
        }

        $serializedApiCall = call_user_func($this->serializer, $apiCall);

        fwrite($this->stream, $serializedApiCall);
    }
}
