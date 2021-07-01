<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Model;

class ApiCall implements ApiCallInterface
{
    protected $id = null;
    protected ?string $endpoint = null;
    protected ?string $method = null;
    protected ?string $reference = null;
    protected ?float $requestTime = null;
    protected ?\DateTimeInterface $requestDateTime = null;
    protected ?float $duration = null;
    protected ?string $request = null;
    protected ?string $response = null;

    /**
     * @param integer|string $id
     */
    public function setId(
        $id
    ): ApiCallInterface {
        $this->id = $id;

        return $this;
    }

    /**
     * @return integer|string
     */
    public function getId()
    {
        return $this->id;
    }

    public function setEndpoint(
        ?string $endpoint
    ): ApiCallInterface {
        $this->endpoint = $endpoint;

        return $this;
    }

    public function getEndpoint(): ?string
    {
        return $this->endpoint;
    }

    public function setMethod(
        ?string $method
    ): ApiCallInterface {
        $this->method = $method;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setReference(
        ?string $reference
    ): ApiCallInterface {
        $this->reference = $reference;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setRequestTime(
        ?float $requestTime
    ): ApiCallInterface {
        $this->requestTime = $requestTime;
        if (null === $requestTime) {
            $this->requestDateTime = null;
            return $this;
        }

        $micro = \sprintf('%06d', ($requestTime - \floor($requestTime)) * 1000000);
        $dateTimeStr = \date('Y-m-d H:i:s', (int) \floor($requestTime));
        $dateTimeStr .= '.' . $micro;
        $this->requestDateTime = new \DateTimeImmutable($dateTimeStr);

        return $this;
    }

    public function getRequestTime(): ?float
    {
        return $this->requestTime;
    }

    public function getRequestDateTime(): ?\DateTimeInterface
    {
        return $this->requestDateTime;
    }

    public function setDuration(
        ?float $duration
    ): ApiCallInterface {
        $this->duration = $duration;

        return $this;
    }

    public function getDuration(): ?float
    {
        return $this->duration;
    }

    public function setRequest(
        ?string $request
    ): ApiCallInterface {
        $this->request = $request;

        return $this;
    }

    public function getRequest(): ?string
    {
        return $this->request;
    }

    public function setResponse(
        ?string $response
    ): ApiCallInterface {
        $this->response = $response;

        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }
}
