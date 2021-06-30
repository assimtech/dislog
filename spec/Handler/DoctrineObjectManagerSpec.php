<?php

declare(strict_types=1);

namespace spec\Assimtech\Dislog\Handler;

use Assimtech\Dislog;
use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DoctrineObjectManagerSpec extends ObjectBehavior
{
    function let(ObjectManager $objectManager)
    {
        $this->beConstructedWith($objectManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Dislog\Handler\DoctrineObjectManager::class);
    }

    function it_can_handle_objects(ObjectManager $objectManager, Dislog\Model\ApiCallInterface $apiCall)
    {
        $objectManager->persist($apiCall)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->handle($apiCall);
    }
}
