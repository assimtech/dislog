<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Identity;

interface IdentityGeneratorInterface
{
    /**
     * @return integer|string
     */
    public function getIdentity();
}
