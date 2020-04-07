<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Serializer;

use Assimtech\Dislog\Model\ApiCallInterface;

class StringSerializer implements SerializerInterface
{
    protected $eol;

    public function __construct(
        string $eol = "\n"
    ) {
        $this->eol = $eol;
    }

    public function __invoke(
        ApiCallInterface $apiCall
    ): string {
        $data = json_encode([
           'duration' => $apiCall->getDuration(),
           'request' => $apiCall->getRequest(),
           'response' => $apiCall->getResponse(),
        ]);

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
