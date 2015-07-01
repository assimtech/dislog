<?php

namespace Assimtech\Dislog\Identity;

interface IdentityGeneratorInterface
{
    /**
     * @return integer|string
     */
    public function getIdentity();
}
