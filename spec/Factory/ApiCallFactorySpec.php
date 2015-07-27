<?php

namespace spec\Assimtech\Dislog\Factory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ApiCallFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Dislog\Factory\ApiCallFactory');
    }

    function it_can_create_an_api_call_interface()
    {
        $this->create()->shouldBeAnInstanceOf('Assimtech\Dislog\Model\ApiCallInterface');
    }

    function it_can_create_an_api_call()
    {
        $this->create()->shouldReturnAnInstanceOf('Assimtech\Dislog\Model\ApiCall');
    }
}
