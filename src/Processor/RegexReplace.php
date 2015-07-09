<?php

namespace Assimtech\Dislog\Processor;

/**
 * @see http://php.net/preg_replace
 */
class RegexReplace implements ProcessorInterface
{
    /**
     * @var string $search
     */
    protected $search;

    /**
     * @var string $replace
     */
    protected $replace;

    /**
     * @param string $search
     * @param string $replace
     */
    public function __construct($search, $replace)
    {
        $this->search = $search;
        $this->replace = $replace;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke($payload)
    {
        return preg_replace($this->search, $this->replace, $payload);
    }
}
