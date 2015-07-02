<?php

namespace Assimtech\Dislog\Serializer;

use Assimtech\Dislog\ApiCallInterface;

/**
 * A serializer converts an ApiCallInterface into something a handler can deal with
 */
interface SerializerInterface
{
    /**
     * @param ApiCallInterface $apiCall
     * @return mixed
     */
    public function __invoke(ApiCallInterface $apiCall);
}
