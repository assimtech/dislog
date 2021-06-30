<?php

declare(strict_types=1);

namespace Assimtech\Dislog;

use Psr\Http;

interface LoggingHttpClientInterface
{
    /**
     * @api
     */
    public function sendRequest(
        Http\Message\RequestInterface $request,
        ?string $appMethod = null,
        ?string $reference = null,
        /* callable[]|callable */ $processors = []
    ): Http\Message\ResponseInterface;
}
