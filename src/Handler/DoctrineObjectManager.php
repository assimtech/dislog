<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Handler;

use Assimtech\Dislog\Model\ApiCallInterface;

class DoctrineObjectManager implements HandlerInterface
{
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
        ApiCallInterface $apiCall
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
