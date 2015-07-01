<?php

namespace Assimtech\Dislog\Identity;

class UniqueIdGenerator implements IdentityGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentity()
    {
        return uniqid();
    }
}
