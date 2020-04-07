<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Processor;

/**
 * A processor acts on either a request or response
 */
interface ProcessorInterface
{
    /**
     * @param string $payload either a request or response
     */
    public function __invoke(
        ?string $payload
    ): ?string;
}
