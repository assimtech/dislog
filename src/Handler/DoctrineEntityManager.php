<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Handler;

use Assimtech\Dislog\Model\ApiCall;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineEntityManager extends DoctrineObjectManager
{
    private $entityClass;
    private $requestDateField;

    public function __construct(
        EntityManagerInterface $objectManager,
        string $entityClass = ApiCall::class,
        string $requestDateField = 'requestDateTime'
    ) {
        parent::__construct($objectManager);

        $this->entityClass = $entityClass;
        $this->requestDateField = $requestDateField;
    }

    public function remove(
        int $maxAge
    ): void {
        $dql = <<<"DQL"
        DELETE FROM {$this->entityClass} ac
        WHERE ac.{$this->requestDateField} < :upto
        DQL;
        $query = $this->objectManager->createQuery($dql);
        $query->setParameter('upto', new \DateTimeImmutable($maxAge . ' seconds ago'));
        $query->execute();
    }
}
