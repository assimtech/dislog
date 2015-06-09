<?php

namespace spec\Assimtech\Dislog\Identity;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UniqueIdGeneratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Dislog\Identity\UniqueIdGenerator');
    }

    function it_can_generate_a_unique_id()
    {
        $this->getIdentity()->shouldBeString();
    }
}
