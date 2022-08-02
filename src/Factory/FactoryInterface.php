<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Factory;

use Assimtech\Dislog;

interface FactoryInterface
{
    public function create(): Dislog\Model\ApiCallInterface;
}
