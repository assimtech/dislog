<?php

declare(strict_types=1);

namespace spec\Assimtech\Dislog\Handler;

use Assimtech\Dislog\Handler\DoctrineObjectManager;
use Assimtech\Dislog\Model\ApiCallInterface;
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
        $this->shouldHaveType(DoctrineObjectManager::class);
    }

    function it_can_handle_objects(ObjectManager $objectManager, ApiCallInterface $apiCall)
    {
        $objectManager->persist($apiCall)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();
        $this->handle($apiCall);
    }
}
