<?php

namespace Assimtech\Dislog\Model;

interface ApiCallInterface
{
    /**
     * @param integer|string $id
     * @return self
     */
    public function setId($id);

    /**
     * @return integer|string
     */
    public function getId();

    /**
     * @param string $endpoint
     * @return self
     */
    public function setEndpoint($endpoint);

    /**
     * @return string
     */
    public function getEndpoint();

    /**
     * @param string $method
     * @return self
     */
    public function setMethod($method);

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @param string|integer|null $reference
     * @return self
     */
    public function setReference($reference);

    /**
     * @return string|integer|null
     */
    public function getReference();

    /**
     * @param float $requestTime
     * @return self
     */
    public function setRequestTime($requestTime);

    /**
     * @return float
     */
    public function getRequestTime();

    /**
     * @return \DateTime
     */
    public function getRequestDateTime();

    /**
     * @param float $duration
     * @return self
     */
    public function setDuration($duration);

    /**
     * @return float|null
     */
    public function getDuration();

    /**
     * @param string $request
     * @return self
     */
    public function setRequest($request);

    /**
     * @return string
     */
    public function getRequest();

    /**
     * @param string $response
     * @return self
     */
    public function setResponse($response);

    /**
     * @return string|null
     */
    public function getResponse();
}
