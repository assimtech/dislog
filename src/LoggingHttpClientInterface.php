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
     * Log the last payload even if $omitPayload was used
     */
    public function logLastPayload(): ?string;

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
        bool $omitPayload = false
    ): Http\Message\ResponseInterface;
}
