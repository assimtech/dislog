<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Factory;

use Assimtech\Dislog\Model;

class ApiCallFactory implements FactoryInterface
{
    public function create(): Model\ApiCallInterface
    {
        return new Model\ApiCall();
    }
}
