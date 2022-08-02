<?php

declare(strict_types=1);

namespace Assimtech\Dislog\Model;

interface ApiCallInterface
{
    public function setId(?string $id): self;
    public function getId(): ?string;

    public function setEndpoint(?string $endpoint): self;
    public function getEndpoint(): ?string;

    public function setMethod(?string $method): self;
    public function getMethod(): ?string;

    public function setReference(?string $reference): self;
    public function getReference(): ?string;

    public function setRequestTime(?float $requestTime): self;
    public function getRequestTime(): ?float;
    public function getRequestDateTime(): ?\DateTimeInterface;

    public function setDuration(?float $duration): self;
    public function getDuration(): ?float;

    public function setRequest(?string $request): self;
    public function getRequest(): ?string;

    public function setResponse(?string $response): self;
    public function getResponse(): ?string;
}
