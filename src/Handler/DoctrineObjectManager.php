<?php

namespace Assimtech\Dislog\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Assimtech\Dislog\ApiCallInterface;

class DoctrineObjectManager implements HandlerInterface
{
    /**
     * @var ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ApiCallInterface $apiCall)
    {
        if (!$this->objectManager->contains($apiCall)) {
            if ($apiCall->getId() === null) {
                $this->objectManager->persist($apiCall);
            } else {
                $this->objectManager->merge($apiCall);
            }
        }

        $this->objectManager->flush();
    }
}
