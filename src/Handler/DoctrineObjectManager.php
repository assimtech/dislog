<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Handler;

use Assimtech\Dislog;

class DoctrineObjectManager implements HandlerInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager|\Doctrine\Persistence\ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager|\Doctrine\Persistence\ObjectManager $objectManager
     */
    public function __construct(
        $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    public function handle(
        Dislog\Model\ApiCallInterface $apiCall
    ): void {
        $this->objectManager->persist($apiCall);
        $this->objectManager->flush();
    }

    public function remove(
        int $maxAge
    ): void {
        throw new \BadMethodCallException(__METHOD__ . ' is not supported by ' . __CLASS__);
    }
}
