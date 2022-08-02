<?php

declare(strict_types=1);

namespace Assimtech\Dislog;

interface ApiCallLoggerInterface
{
    /**
     * @api
     *
     * @param ?float $requestTime if null, $requestTime will be determined by ApiCallLogger
     * @param callable[]|callable|null $processors
     */
    public function logRequest(
        ?string $request,
        ?string $endpoint,
        ?string $appMethod,
        ?string $reference = null,
        $processors = null,
        ?float $requestTime = null
    ): Model\ApiCallInterface;

    /**
     * @api
     *
     * @param callable[]|callable|null $processors
     */
    public function logResponse(
        Model\ApiCallInterface $apiCall,
        ?string $response = null,
        $processors = null,
        ?float $responseTime = null
    ): void;

    /**
     * @param callable[]|callable|null $requestProcessors
     * @param callable[]|callable|null $responseProcessors
     */
    public function logPayload(
        Model\ApiCallInterface $apiCall,
        ?string $request,
        $requestProcessors,
        ?string $response,
        $responseProcessors
    ): ?string;
}
