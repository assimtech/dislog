<?php

namespace Assimtech\Dislog\Processor;

/**
 * A processor acts on either a request or response
 */
interface ProcessorInterface
{
    /**
     * @param string $payload either a request or response
     */
    public function __invoke($payload);
}
