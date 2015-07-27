<?php

namespace Assimtech\Dislog\Serializer;

use Assimtech\Dislog\Model\ApiCallInterface;

class StringSerializer implements SerializerInterface
{
    /**
     * @var string $eol
     */
    protected $eol;

    /**
     * @param string $eol
     */
    public function __construct($eol = "\n")
    {
        $this->eol = $eol;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ApiCallInterface $apiCall)
    {
        $data = json_encode(array(
           'duration' => $apiCall->getDuration(),
           'request' => $apiCall->getRequest(),
           'response' => $apiCall->getResponse(),
        ));

        return sprintf(
            '[%s] (%s) %s (%s) | %s - %s%s',
            $apiCall->getRequestDateTime()->format('c'),
            $apiCall->getId(),
            $apiCall->getMethod(),
            $apiCall->getEndpoint(),
            $apiCall->getReference(),
            $data,
            $this->eol
        );
    }
}
