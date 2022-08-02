<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Processor;

/**
 * @see http://php.net/str_replace
 */
class StringReplace implements ProcessorInterface
{
    protected string $search;
    protected string $replace;

    public function __construct(
        string $search,
        string $replace
    ) {
        $this->search = $search;
        $this->replace = $replace;
    }

    public function __invoke(
        ?string $payload
    ): ?string {
        if (null === $payload) {
            return null;
        }

        return \str_replace($this->search, $this->replace, $payload);
    }
}
