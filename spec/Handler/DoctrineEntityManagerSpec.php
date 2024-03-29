<?php

declare(strict_types=1);

namespace spec\Assimtech\Dislog\Handler;

use Assimtech\Dislog;
use Doctrine\ORM;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DoctrineEntityManagerSpec extends ObjectBehavior
{
    function let(ORM\EntityManagerInterface $entityManager)
    {
        $this->beConstructedWith($entityManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Dislog\Handler\DoctrineEntityManager::class);
    }

    function it_can_remove_objects(
        ORM\EntityManagerInterface $entityManager,
        ORM\AbstractQuery $query
    ) {
        $entityClass = Dislog\Model\ApiCall::class;
        $expectedDql = <<<"DQL"
        DELETE FROM {$entityClass} ac
        WHERE ac.requestDateTime < :upto
        DQL;
        $entityManager->createQuery($expectedDql)->willReturn($query);
        $query->setParameter('upto', Argument::type(\DateTimeInterface::class))->shouldBeCalled();
        $query->execute()->shouldBeCalled();
        $this->remove(__LINE__);
    }
}
