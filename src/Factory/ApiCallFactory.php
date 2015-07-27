<?php

namespace Assimtech\Dislog\Factory;

use Assimtech\Dislog\Model\ApiCall;

class ApiCallFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return new ApiCall();
    }
}
