<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Factory;

use Assimtech\Dislog\Model;

interface FactoryInterface
{
    public function create(): Model\ApiCallInterface;
}
