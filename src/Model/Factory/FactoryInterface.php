<?php

namespace Assimtech\Dislog\Model\Factory;

interface FactoryInterface
{
    /**
     * @return \Assimtech\Dislog\Model\ApiCallInterface
     */
    public function create();
}
