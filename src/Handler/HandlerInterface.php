<?php

namespace Assimtech\Dislog\Handler;

use Assimtech\Dislog\Model\ApiCallInterface;

interface HandlerInterface
{
    /**
     * @param \Assimtech\Dislog\Model\ApiCallInterface $apiCall
     */
    public function handle(ApiCallInterface $apiCall);
}
