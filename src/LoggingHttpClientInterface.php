<?php

declare(strict_types=1);

namespace Assimtech\Dislog;

use Psr\Http;

interface LoggingHttpClientInterface
{
    /**
     * @api
     *
     * Get the last ApiCall id, useful when logging errors to allow for easier ApiCall lookup
     */
    public function getLastApiCallId(): ?string;

    /**
     * @api
     *
     * Logs the last ApiCall when self::sendRequest was used with $deferredLogging = true
     *
     * Returns the LastApiCallId (even if self::sendRequest was used with $deferredLogging = false)
     *
     * @return ?string Last ApiCall Id
     */
    public function logLastApiCall(): ?string;

    /**
     * @api
     *
     * @param ?string $appMethod Application method, if null; disable ApiCall logging
     */
    public function sendRequest(
        Http\Message\RequestInterface $request,
        ?string $appMethod = null,
        ?string $reference = null,
        /* callable[]|callable|null */ $requestProcessors = null,
        /* callable[]|callable|null */ $responseProcessors = null,
        bool $deferredLogging = false
    ): Http\Message\ResponseInterface;
}
