<?php

namespace Assimtech\Dislog;

interface ApiCallLoggerInterface
{
    /**
     * @api
     * @param string $request
     * @param string $endpoint
     * @param string $method
     * @param string|null $reference
     * @return \Assimtech\Dislog\Model\ApiCallInterface
     */
    public function logRequest($request, $endpoint, $method, $reference = null);

    /**
     * @api
     * @param \Assimtech\Dislog\Model\ApiCallInterface $apiCall
     * @param string|null $response
     * @return void
     */
    public function logResponse(Model\ApiCallInterface $apiCall, $response = null);
}
