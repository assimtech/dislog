<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Handler;

use Assimtech\Dislog\Identity\IdentityGeneratorInterface;
use Assimtech\Dislog\Model\ApiCallInterface;
use Assimtech\Dislog\Serializer\SerializerInterface;

class Stream implements HandlerInterface
{
    protected $stream;
    protected $identityGenerator;
    protected $serializer;

    public function __construct(
        /** resource|string */ $stream,
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

    public function handle(
        ApiCallInterface $apiCall
    ): void {
        if (null === $apiCall->getId()) {
            $id = $this->identityGenerator->getIdentity();
            $apiCall->setId($id);
        }

        $serializedApiCall = call_user_func($this->serializer, $apiCall);

        fwrite($this->stream, $serializedApiCall);
    }

    public function remove(
        int $maxAge
    ): void {
        throw new \BadMethodCallException(__METHOD__ . ' is not supported by ' . __CLASS__);
    }
}
