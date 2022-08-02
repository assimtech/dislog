<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Processor;

/**
 * @see http://php.net/preg_replace
 */
class RegexReplace implements ProcessorInterface
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

        return \preg_replace($this->search, $this->replace, $payload);
    }
}
