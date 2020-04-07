<?php

declare(strict_types=1);

namespace spec\Assimtech\Dislog\Identity;

use Assimtech\Dislog\Identity\UniqueIdGenerator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UniqueIdGeneratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UniqueIdGenerator::class);
    }

    function it_can_generate_a_unique_id()
    {
        $this->getIdentity()->shouldBeString();
    }
}
