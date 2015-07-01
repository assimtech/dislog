<?php

namespace Assimtech\Dislog\Handler;

use Assimtech\Dislog\Identity\IdentityGeneratorInterface;
use Assimtech\Dislog\Model\ApiCallInterface;
use RuntimeException;

class Stream implements HandlerInterface
{
    /**
     * @var IdentityGeneratorInterface $identityGenerator
     */
    protected $identityGenerator;

    /**
     * @var resource $stream
     */
    protected $stream;

    /**
     * @var string $eol
     */
    protected $eol;

    /**
     * @param IdentityGeneratorInterface $identityGenerator
     * @param resource $stream
     */
    public function __construct(IdentityGeneratorInterface $identityGenerator, $stream, $eol = "\n")
    {
        $this->identityGenerator = $identityGenerator;
        $this->stream = $stream;
        $this->eol = $eol;
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
        $writeResult = @fwrite($this->stream, $serializedApiCall);
        if ($writeResult === false) {
            throw new RuntimeException('Failed to write to stream');
        }
    }

    /**
     * @param ApiCallInterface $apiCall
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
            '[%s] (%s) %s | %s - %s%s',
            $apiCall->getRequestDateTime()->format('c'),
            $apiCall->getId(),
            $apiCall->getMethod(),
            $apiCall->getReference(),
            $payload,
            $this->eol
        );
    }
}
