<?php

declare(strict_types=1);

namespace spec\Assimtech\Dislog\Model;

use Assimtech\Dislog\Model\ApiCall;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ApiCallSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ApiCall::class);
    }

    function it_has_id()
    {
        $string = __METHOD__;
        $this->setId($string)->shouldReturn($this);
        $this->getId()->shouldReturn($string);
    }

    function it_has_endpoint()
    {
        $string = __METHOD__;
        $this->setEndpoint($string)->shouldReturn($this);
        $this->getEndpoint()->shouldReturn($string);
    }

    function it_has_method()
    {
        $string = __METHOD__;
        $this->setMethod($string)->shouldReturn($this);
        $this->getMethod()->shouldReturn($string);
    }

    function it_has_reference()
    {
        $string = __METHOD__;
        $this->setReference($string)->shouldReturn($this);
        $this->getReference()->shouldReturn($string);
    }

    function it_has_request_time()
    {
        $float = 1.2;
        $this->setRequestTime($float)->shouldReturn($this);
        $this->getRequestTime()->shouldReturn($float);
        $this->getRequestDateTime()->shouldReturnAnInstanceOf(\DateTimeInterface::class);
    }

    function it_has_duration()
    {
        $float = 1.3;
        $this->setDuration($float)->shouldReturn($this);
        $this->getDuration()->shouldReturn($float);
    }

    function it_has_request()
    {
        $string = __METHOD__;
        $this->setRequest($string)->shouldReturn($this);
        $this->getRequest()->shouldReturn($string);
    }

    function it_has_response()
    {
        $string = __METHOD__;
        $this->setResponse($string)->shouldReturn($this);
        $this->getResponse()->shouldReturn($string);
    }
}
