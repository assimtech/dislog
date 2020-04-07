<?php

declare(strict_types=1);

namespace spec\Assimtech\Dislog\Processor;

use Assimtech\Dislog\Processor\StringReplace;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StringReplaceSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('search for', 'replace with');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(StringReplace::class);
    }

    function it_can_replace()
    {
        $payload = 'I want to search for somthing';
        $this->__invoke($payload)->shouldReturn('I want to replace with somthing');
    }
}
