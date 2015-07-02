<?php

namespace Assimtech\Dislog;

interface ApiCallLoggerInterface
{
    /**
     * @api
     * @param string|null $request
     * @param string $endpoint
     * @param string $method
     * @param string|null $reference
     * @param callable|callable[] $processors
     * @return ApiCallInterface
     */
    public function logRequest($request, $endpoint, $method, $reference = null, $processors = array());

    /**
     * @api
     * @param ApiCallInterface $apiCall
     * @param string|null $response
     * @param callable|callable[] $processors
     * @return void
     */
    public function logResponse(ApiCallInterface $apiCall, $response = null, $processors = array());
}
