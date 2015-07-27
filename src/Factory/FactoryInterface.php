<?php

namespace Assimtech\Dislog\Factory;

use Assimtech\Dislog\Model\ApiCallInterface;

interface FactoryInterface
{
    /**
     * @return ApiCallInterface
     */
    public function create();
}
