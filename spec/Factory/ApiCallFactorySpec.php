<?php

declare(strict_types=1);

namespace spec\Assimtech\Dislog\Factory;

use Assimtech\Dislog;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ApiCallFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Dislog\Factory\ApiCallFactory::class);
    }

    function it_can_create_an_api_call_interface()
    {
        $this->create()->shouldBeAnInstanceOf(Dislog\Model\ApiCallInterface::class);
    }

    function it_can_create_an_api_call()
    {
        $this->create()->shouldReturnAnInstanceOf(Dislog\Model\ApiCall::class);
    }
}
