<?php

namespace Assimtech\Dislog\Factory;

use Assimtech\Dislog\ApiCallInterface;

interface FactoryInterface
{
    /**
     * @return ApiCallInterface
     */
    public function create();
}
