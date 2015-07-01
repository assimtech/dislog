<?php

namespace Assimtech\Dislog\Handler;

use Assimtech\Dislog\Identity\IdentityGeneratorInterface;
use Assimtech\Dislog\Serializer\SerializerInterface;
use Assimtech\Dislog\Model\ApiCallInterface;

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
     * @param resource $stream
     * @param IdentityGeneratorInterface $identityGenerator
     * @param SerializerInterface $serializer
     */
    public function __construct(
        $stream,
        IdentityGeneratorInterface $identityGenerator,
        SerializerInterface $serializer
    ) {
        $this->stream = $stream;
        $this->identityGenerator = $identityGenerator;
        $this->serializer = $serializer;
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
