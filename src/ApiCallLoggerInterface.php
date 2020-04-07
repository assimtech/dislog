<?php

declare(strict_types=1);

namespace Assimtech\Dislog;

use Assimtech\Dislog\Model\ApiCallInterface;

interface ApiCallLoggerInterface
{
    /**
     * @api
     */
    public function logRequest(
        ?string $request,
        ?string $endpoint,
        ?string $method,
        string $reference = null,
        /* callable[]|callable */ $processors = []
    ): ApiCallInterface;

    /**
     * @api
     */
    public function logResponse(
        ApiCallInterface $apiCall,
        string $response = null,
        /* callable[]|callable */ $processors = []
    ): void;
}
