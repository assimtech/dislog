<?php

declare(strict_types=1);

namespace Assimtech\Dislog;

use GuzzleHttp\Psr7 as GuzzlePsr7;
use Psr\Http;

class LoggingHttpClient implements LoggingHttpClientInterface
{
    private Http\Client\ClientInterface $httpClient;
    private ApiCallLoggerInterface $apiCallLogger;

    private bool $lastPayloadCached = false;
    private ?Model\ApiCallInterface $lastApiCall = null;

    /**
     * @var callable[]|callable|null $lastRequestProcessors
     */
    private $lastRequestProcessors = null;
    private ?string $lastRequest = null;

    /**
     * @var callable[]|callable|null $lastResponseProcessors
     */
    private $lastResponseProcessors = null;
    private ?string $lastResponse = null;

    public function __construct(
        Http\Client\ClientInterface $httpClient,
        ApiCallLoggerInterface $apiCallLogger
    ) {
        $this->httpClient = $httpClient;
        $this->apiCallLogger = $apiCallLogger;
    }

    private function resetLast(): self
    {
        $this->lastPayloadCached = false;
        $this->lastApiCall = null;
        $this->lastRequestProcessors = null;
        $this->lastRequest = null;
        $this->lastResponseProcessors = null;
        $this->lastResponse = null;

        return $this;
    }

    /**
     * @api
     */
    public function getLastApiCallId(): ?string
    {
        return $this->lastApiCall
            ? $this->lastApiCall->getId()
            : null
        ;
    }

    /**
     * @api
     */
    public function logLastPayload(): ?string
    {
        if (!$this->lastPayloadCached) {
            return $this->getLastApiCallId();
        }

        return $this->apiCallLogger->logPayload(
            $this->lastApiCall,
            $this->lastRequest,
            $this->lastRequestProcessors,
            $this->lastResponse,
            $this->lastResponseProcessors
        );
    }

    /**
     * @api
     */
    public function sendRequest(
        Http\Message\RequestInterface $request,
        ?string $appMethod = null,
        ?string $reference = null,
        /* callable[]|callable|null */ $requestProcessors = null,
        /* callable[]|callable|null */ $responseProcessors = null,
        bool $omitPayload = false
    ): Http\Message\ResponseInterface {
        $this->resetLast();

        if (null !== $appMethod) {
            $this->lastApiCall = $this->apiCallLogger->logRequest(
                $omitPayload ? null : GuzzlePsr7\Message::toString($request),
                (string) $request->getUri(),
                $appMethod,
                $reference,
                $requestProcessors
            );
        }

        $response = $this->httpClient->sendRequest($request);

        if (null !== $appMethod) {
            if ($omitPayload) {
                $this->lastPayloadCached = true;
                $this->lastRequestProcessors = $requestProcessors;
                $this->lastRequest = GuzzlePsr7\Message::toString($request);
                $this->lastResponseProcessors = $responseProcessors;
                $this->lastResponse = GuzzlePsr7\Message::toString($response);
            }

            $this->apiCallLogger->logResponse(
                $this->lastApiCall,
                $omitPayload ? null : GuzzlePsr7\Message::toString($response),
                $responseProcessors
            );
        }

        return $response;
    }
}
