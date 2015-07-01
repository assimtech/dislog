<?php

namespace Assimtech\Dislog\Processor;

/**
 * @see http://php.net/str_replace
 */
class StringReplace implements ProcessorInterface
{
    /**
     * @var mixed $search
     */
    protected $search;

    /**
     * @var mixed $replace
     */
    protected $replace;

    /**
     * @param mixed $search
     * @param mixed $replace
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
        return str_replace($this->search, $this->replace, $payload);
    }
}
