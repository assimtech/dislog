<?php

declare(strict_types=1);

namespace Assimtech\Dislog;

use GuzzleHttp\Psr7 as GuzzlePsr7;
use Psr\Http;

class LoggingHttpClient implements LoggingHttpClientInterface
{
    private Http\Client\ClientInterface $httpClient;
    private ApiCallLoggerInterface $apiCallLogger;

    private ?string $lastApiCallId = null;

    private ?Http\Message\RequestInterface $lastRequest = null;
    private ?Http\Message\ResponseInterface $lastResponse = null;
    private ?string $lastAppMethod = null;
    private ?string $lastReference = null;
    private /* callable[]|callable|null */ $lastRequestProcessors = null;
    private /* callable[]|callable|null */ $lastResponseProcessors = null;
    private ?float $lastRequestTime = null;

    public function __construct(
        Http\Client\ClientInterface $httpClient,
        ApiCallLoggerInterface $apiCallLogger
    ) {
        $this->httpClient = $httpClient;
        $this->apiCallLogger = $apiCallLogger;
    }

    private function setDeferredRequest(
        Http\Message\RequestInterface $request,
        ?string $appMethod = null,
        ?string $reference = null,
        /* callable[]|callable|null */ $requestProcessors = null,
        /* callable[]|callable|null */ $responseProcessors = null
    ): void {
        $this->lastRequest = $request;
        $this->lastAppMethod = $appMethod;
        $this->lastReference = $reference;
        $this->lastRequestProcessors = $requestProcessors;
        $this->lastResponseProcessors = $responseProcessors;
        $this->lastRequestTime = \microtime(true);
    }

    private function setDeferredResponse(
        Http\Message\ResponseInterface $response
    ): void {
        $this->lastResponse = $response;
    }

    private function resetLastApiCallId(): self
    {
        $this->lastApiCallId = null;

        return $this;
    }

    private function resetDeferredApiCall(): self
    {
        $this->lastRequest = null;
        $this->lastResponse = null;
        $this->lastAppMethod = null;
        $this->lastReference = null;
        $this->lastRequestProcessors = null;
        $this->lastResponseProcessors = null;
        $this->lastRequestTime = null;

        return $this;
    }

    private function logRequest(
        Http\Message\RequestInterface $request,
        ?string $appMethod,
        ?string $reference = null,
        /* callable[]|callable|null */ $requestProcessors = null,
        ?float $requestTime = null
    ): Model\ApiCallInterface {
        $loggedApiCall = $this->apiCallLogger->logRequest(
            GuzzlePsr7\Message::toString($request),
            (string) $request->getUri(),
            $appMethod,
            $reference,
            $requestProcessors,
            $requestTime
        );

        $this->lastApiCallId = (string) $loggedApiCall->getId();

        return $loggedApiCall;
    }

    private function logResponse(
        Model\ApiCallInterface $loggedApiCall,
        Http\Message\ResponseInterface $response,
        /* callable[]|callable|null */ $responseProcessors = null
    ): void {
        $this->apiCallLogger->logResponse(
            $loggedApiCall,
            GuzzlePsr7\Message::toString($response),
            $responseProcessors
        );
    }

    /**
     * @api
     */
    public function logLastApiCall(): ?string
    {
        $loggedApiCall = $this->logRequest(
            $this->lastRequest,
            $this->lastAppMethod,
            $this->lastReference,
            $this->lastRequestProcessors,
            $this->lastRequestTime
        );
        $this->logResponse(
            $loggedApiCall,
            $this->lastResponse,
            $this->lastResponseProcessors
        );

        return $this->getLastApiCallId();
    }

    /**
     * @api
     */
    public function getLastApiCallId(): ?string
    {
        return $this->lastApiCallId;
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
        bool $deferredLogging = false
    ): Http\Message\ResponseInterface {
        $this
            ->resetLastApiCallId()
            ->resetDeferredApiCall()
        ;

        $loggedApiCall = null;
        if (null !== $appMethod) {
            if ($deferredLogging) {
                $this->setDeferredRequest(
                    $request,
                    $appMethod,
                    $reference,
                    $requestProcessors,
                    $responseProcessors
                );
            } else {
                $loggedApiCall = $this->logRequest(
                    $request,
                    $appMethod,
                    $reference,
                    $requestProcessors
                );
            }
        }

        $response = $this->httpClient->sendRequest($request);

        if ($deferredLogging) {
            $this->setDeferredResponse($response);
        } elseif (null !== $loggedApiCall) {
            $this->logResponse(
                $loggedApiCall,
                $response,
                $responseProcessors
            );
        }

        return $response;
    }
}
