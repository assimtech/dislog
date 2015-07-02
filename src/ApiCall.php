<?php

namespace Assimtech\Dislog;

use DateTime;

class ApiCall implements ApiCallInterface
{
    /**
     * @var integer|string $id
     */
    protected $id;

    /**
     * @var string $endpoint
     */
    protected $endpoint;

    /**
     * @var string $method
     */
    protected $method;

    /**
     * @var string|integer|null $reference
     */
    protected $reference;

    /**
     * @var float $requestTime
     */
    protected $requestTime;

    /**
     * @var \DateTime $requestDateTime
     */
    protected $requestDateTime;

    /**
     * @var float|null $duration
     */
    protected $duration;

    /**
     * @var string $request
     */
    protected $request;

    /**
     * @var string|null $response
     */
    protected $response;

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestTime($requestTime)
    {
        $this->requestTime = $requestTime;
        $this->requestDateTime = DateTime::createFromFormat('U.u', $this->requestTime);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestTime()
    {
        return $this->requestTime;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestDateTime()
    {
        return $this->requestDateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return $this->response;
    }
}
