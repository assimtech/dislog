<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Factory;

use Assimtech\Dislog;

class ApiCallFactory implements FactoryInterface
{
    public function create(): Dislog\Model\ApiCallInterface
    {
        return new Dislog\Model\ApiCall();
    }
}
