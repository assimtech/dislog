<?php

namespace Assimtech\Dislog\Model\Factory;

use Assimtech\Dislog\Model\ApiCallInterface;

interface FactoryInterface
{
    /**
     * @return ApiCallInterface
     */
    public function create();
}
