<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Handler;

use Assimtech\Dislog\Model\ApiCall;
use Doctrine\ODM\MongoDB\DocumentManager;

class DoctrineDocumentManager extends DoctrineObjectManager
{
    private $documentClass;
    private $requestDateField;

    public function __construct(
        DocumentManager $objectManager,
        string $documentClass = ApiCall::class,
        string $requestDateField = 'request_datetime'
    ) {
        parent::__construct($objectManager);

        $this->documentClass = $documentClass;
        $this->requestDateField = $requestDateField;
    }

    public function remove(
        int $maxAge
    ): void {
        $keys = [
            $this->requestDateField => 1,
        ];
        $options = [
            'expireAfterSeconds' => $maxAge,
        ];
        /**
         * @var \MongoDB\Collection $collection
         */
        $collection = $this->objectManager->getDocumentCollection($this->documentClass);
        try {
            $collection->createIndex($keys, $options);
        } catch (\MongoDB\Driver\Exception\CommandException $e) {
            if ($e->getCode() !== 85) {
                throw $e;
            }
            // Try to re-create
            $collection->dropIndex("{$this->requestDateField}_1");
            $collection->createIndex($keys, $options);
        }
    }
}
