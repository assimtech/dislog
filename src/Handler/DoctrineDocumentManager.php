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
        $collection = $this->objectManager->getDocumentCollection($this->documentClass);
        if ($collection->isFieldIndexed($this->requestDateField)) {
            $existingIndexs = $collection->getIndexInfo();
            foreach ($existingIndexs as $existingIndex) {
                if ($keys !== $existingIndex['key']) {
                    continue;
                }
                if (isset($existingIndex['expireAfterSeconds']) && $maxAge === $existingIndex['expireAfterSeconds']) {
                    // Index exists and is correct
                    return;
                }
                // Index exists but is incorrect, re-create it
                $collection->deleteIndex($keys);
                break;
            }
        }
        $collection->ensureIndex($keys, $options);
    }
}
