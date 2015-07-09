<?php

namespace spec\Assimtech\Dislog\Processor;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RegexReplaceSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('/(this)/', '$1 and that');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Dislog\Processor\RegexReplace');
    }

    function it_can_replace()
    {
        $payload = 'I this to be replaced';
        $this->__invoke($payload)->shouldReturn('I this and that to be replaced');
    }
}
