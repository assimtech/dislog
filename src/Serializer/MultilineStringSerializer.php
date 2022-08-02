<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Serializer;

use Assimtech\Dislog;

class MultilineStringSerializer implements SerializerInterface
{
    protected string $eol;

    public function __construct(
        string $eol = "\n"
    ) {
        $this->eol = $eol;
    }

    public function __invoke(
        Dislog\Model\ApiCallInterface $apiCall
    ): string {
        return \implode(' ', [
            "[{$apiCall->getRequestDateTime()->format('c')}]",
            "({$apiCall->getId()})",
            $apiCall->getMethod(),
            "({$apiCall->getEndpoint()})",
            '|',
            "({$apiCall->getReference()})",
            '-',
            "{$apiCall->getDuration()}"
        ]) . $this->eol . $this->eol
            . $apiCall->getRequest() . $this->eol . $this->eol
            . $apiCall->getResponse() . $this->eol
            . '---' . $this->eol . $this->eol
        ;
    }
}
