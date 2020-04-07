<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Serializer;

use Assimtech\Dislog\Model\ApiCallInterface;

/**
 * A serializer converts an ApiCallInterface into something a handler can deal with
 */
interface SerializerInterface
{
    public function __invoke(
        ApiCallInterface $apiCall
    );
}
