<?php

declare(strict_types=1);

namespace Assimtech\Dislog;

use Assimtech\Dislog\Model\ApiCallInterface;

interface ApiCallLoggerInterface
{
    /**
     * @api
     *
     * @param ?float $requestTime if null, $requestTime will be determined by ApiCallLogger
     */
    public function logRequest(
        ?string $request,
        ?string $endpoint,
        ?string $appMethod,
        ?string $reference = null,
        /* callable[]|callable|null */ $processors = null,
        ?float $requestTime = null
    ): ApiCallInterface;

    /**
     * @api
     */
    public function logResponse(
        ApiCallInterface $apiCall,
        ?string $response = null,
        /* callable[]|callable|null */ $processors = null
    ): void;
}
