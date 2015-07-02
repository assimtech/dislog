<?php

namespace Assimtech\Dislog\Handler;

use Assimtech\Dislog\ApiCallInterface;

interface HandlerInterface
{
    /**
     * @param ApiCallInterface $apiCall
     */
    public function handle(ApiCallInterface $apiCall);
}
