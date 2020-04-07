<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Handler;

use Assimtech\Dislog\Model\ApiCallInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DoctrineObjectManager implements HandlerInterface
{
    protected $objectManager;

    public function __construct(
        ObjectManager $objectManager
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
