<?php

namespace Assimtech\Dislog\Handler;

use Assimtech\Dislog\Identity\IdentityGeneratorInterface;
use Assimtech\Dislog\Model\ApiCallInterface;

class Stream implements HandlerInterface
{
    /**
     * @var \Assimtech\Dislog\Identity\IdentityGeneratorInterface $identityGenerator
     */
    protected $identityGenerator;

    /**
     * @var resource $stream
     */
    protected $stream;

    /**
     * @param \Assimtech\Dislog\Identity\IdentityGeneratorInterface $identityGenerator
     * @param resource $stream
     */
    public function __construct(IdentityGeneratorInterface $identityGenerator, $stream)
    {
        $this->identityGenerator = $identityGenerator;
        $this->stream = $stream;
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

        $serializedApiCall = $this->serializeApiCall($apiCall);
        fwrite($this->stream, $serializedApiCall . PHP_EOL);
    }

    /**
     * @param \Assimtech\Dislog\Model\ApiCallInterface $apiCall
     * @return string
     */
    protected function serializeApiCall(ApiCallInterface $apiCall)
    {
        $payload = json_encode(array(
           'endpoint' => $apiCall->getEndpoint(),
           'requestTime' => $apiCall->getRequestTime(),
           'duration' => $apiCall->getDuration(),
           'request' => $apiCall->getRequest(),
           'response' => $apiCall->getResponse(),
        ));
        return sprintf(
            '[%s] (%s) %s | %s - %s',
            $apiCall->getRequestDateTime()->format('c'),
            $apiCall->getId(),
            $apiCall->getMethod(),
            $apiCall->getReference(),
            $payload
        );
    }
}
