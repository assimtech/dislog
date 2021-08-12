<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Serializer;

use Assimtech\Dislog;

/**
 * A serializer converts an ApiCallInterface into something a handler can deal with
 */
interface SerializerInterface
{
    public function __invoke(
        Dislog\Model\ApiCallInterface $apiCall
    );
}
