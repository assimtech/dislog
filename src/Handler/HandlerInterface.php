<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Handler;

use Assimtech\Dislog;

interface HandlerInterface
{
    /**
     * Record / update an apiCall log
     */
    public function handle(
        Dislog\Model\ApiCallInterface $apiCall
    ): void;

    /**
     * Remove apiCall logs older than $maxAge (seconds)
     */
    public function remove(
        int $maxAge
    ): void;
}
