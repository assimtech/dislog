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

    public function __construct(
        Http\Client\ClientInterface $httpClient,
        ApiCallLoggerInterface $apiCallLogger
    ) {
        $this->httpClient = $httpClient;
        $this->apiCallLogger = $apiCallLogger;
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
        /* callable[]|callable */ $requestProcessors = [],
        /* callable[]|callable */ $responseProcessors = []
    ): Http\Message\ResponseInterface {
        $this->lastApiCallId = null;
        $loggedApiCall = null;

        if (null !== $appMethod) {
            $loggedApiCall = $this->apiCallLogger->logRequest(
                    GuzzlePsr7\Message::toString($request),
                    (string) $request->getUri(),
                    $appMethod,
                    $reference,
                    $requestProcessors
                )
            ;
            $this->lastApiCallId = (string) $loggedApiCall->getId();
        }

        $response = $this->httpClient->sendRequest($request);
        if (null !== $loggedApiCall) {
            $this->apiCallLogger->logResponse(
                $loggedApiCall,
                GuzzlePsr7\Message::toString($response),
                $responseProcessors
            );
        }

        return $response;
    }
}
