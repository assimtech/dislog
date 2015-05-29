<?php

namespace spec\Assimtech\Dislog\Handler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\ObjectManager;
use Assimtech\Dislog\Model\ApiCallInterface;

class DoctrineObjectManagerSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager)
    {
        $this->beConstructedWith($objectManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Assimtech\Dislog\Handler\DoctrineObjectManager');
    }

    function it_can_handle_managed_objects(ObjectManager $objectManager, ApiCallInterface $apiCall)
    {
        $objectManager->contains($apiCall)->willReturn(true);
        $objectManager->flush()->shouldBeCalled();
        $this->handle($apiCall);
    }

    function it_can_handle_new_unmanaged_objects(ObjectManager $objectManager, ApiCallInterface $apiCall)
    {
        $objectManager->contains($apiCall)->willReturn(false);
        $apiCall->getId()->willReturn(null);
        $objectManager->persist($apiCall)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->handle($apiCall);
    }

    function it_can_handle_exiting_unmanaged_objects(ObjectManager $objectManager, ApiCallInterface $apiCall)
    {
        $objectManager->contains($apiCall)->willReturn(false);
        $apiCall->getId()->willReturn(1);
        $objectManager->merge($apiCall)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->handle($apiCall);
    }
}
