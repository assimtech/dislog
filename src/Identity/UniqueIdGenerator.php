<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Identity;

class UniqueIdGenerator implements IdentityGeneratorInterface
{
    public function getIdentity(): string
    {
        return \uniqid();
    }
}
