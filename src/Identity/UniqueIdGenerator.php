<?php

namespace Assimtech\Dislog\Identity;

class UniqueIdGenerator implements IdentityGeneratorInterface
{
    public function getIdentity()
    {
        return uniqid();
    }
}
