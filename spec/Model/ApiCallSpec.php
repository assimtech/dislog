<?php

namespace spec\Assimtech\Dislog\Model;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ApiCallSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Dislog\Model\ApiCall');
    }

    function it_has_id($id)
    {
        $this->setId($id)->shouldReturn($this);
        $this->getId()->shouldReturn($id);
    }

    function it_has_endpoint($endpoint)
    {
        $this->setEndpoint($endpoint)->shouldReturn($this);
        $this->getEndpoint()->shouldReturn($endpoint);
    }

    function it_has_method($method)
    {
        $this->setMethod($method)->shouldReturn($this);
        $this->getMethod()->shouldReturn($method);
    }

    function it_has_reference($reference)
    {
        $this->setReference($reference)->shouldReturn($this);
        $this->getReference()->shouldReturn($reference);
    }

    function it_has_request_time()
    {
        $requestTime = 1.2;
        $this->setRequestTime($requestTime)->shouldReturn($this);
        $this->getRequestTime()->shouldReturn($requestTime);
        $this->getRequestDateTime()->shouldReturnAnInstanceOf('DateTime');
    }

    function it_has_duration($duration)
    {
        $this->setDuration($duration)->shouldReturn($this);
        $this->getDuration()->shouldReturn($duration);
    }

    function it_has_request($request)
    {
        $this->setRequest($request)->shouldReturn($this);
        $this->getRequest()->shouldReturn($request);
    }

    function it_has_response($response)
    {
        $this->setResponse($response)->shouldReturn($this);
        $this->getResponse()->shouldReturn($response);
    }
}
