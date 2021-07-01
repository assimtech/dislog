# Dislog

[![Latest Stable Version](https://poser.pugx.org/assimtech/dislog/v/stable)](https://packagist.org/packages/assimtech/dislog)
[![Total Downloads](https://poser.pugx.org/assimtech/dislog/downloads)](https://packagist.org/packages/assimtech/dislog)
[![Latest Unstable Version](https://poser.pugx.org/assimtech/dislog/v/unstable)](https://packagist.org/packages/assimtech/dislog)
[![License](https://poser.pugx.org/assimtech/dislog/license)](https://packagist.org/packages/assimtech/dislog)

Dislog is an API call logger. API calls differ from normal log events because they compose of a request and a response which happen at different times however should be logged together as they are related.

## Framework integration

[Symfony - DislogBundle](https://github.com/assimtech/dislog-bundle)

## Usage

### LoggingHttpClientInterface

A PSR-18 compatible `LoggingHttpClient` is provided if recording HTTP requests from a `Psr\Http\Client\ClientInterface`.

Note: if using `Assimtech\Dislog\LoggingHttpClient` you **MUST** install the following dependancies into your project:

- "guzzlehttp/psr7" This is only used to translate Psr\Http\Message\{RequestInterface,ResponseInterface} into strings
- "psr/http-client"
- "psr/http-message"

```php
/**
 * @var Psr\Http\Client\ClientInterface $httpClient
 * @var Assimtech\Dislog\ApiCallLoggerInterface $apiCallLogger
 */
$loggingHttpClient = new Assimtech\Dislog\LoggingHttpClient(
    $httpClient,
    $apiCallLogger
);

/**
 * @var Psr\Http\Message\RequestInterface $request
 * @var Psr\Http\Message\ResponseInterface $response
 * @var ?string $appMethod The method in the application that triggered this API call, setting to null will disable API logging
 * @var ?string $reference The reference for this specific call (e.g. id or key if available), helps with searching API logs
 * @var callable[]|callable|null $requestProcessors Processors to apply to $request, see Processors section below
 * @var callable[]|callable|null $responseProcessors Processors to apply to $response, see Processors section below
 * @var bool $deferredLogging If set to true, API calls will only be logged if LoggingHttpClient::logLastApiCall() is called after the request is sent
 *
 * Deferred Logging is useful if you want to inspect the $response before deciding to log the API call or not:
 */
$response = $loggingHttpClient->sendRequest(
    $request,
    /* ?string */ $appMethod = null,
    /* ?string */ $reference = null,
    /* callable[]|callable|null */ $requestProcessors = null,
    /* callable[]|callable|null */ $responseProcessors = null,
    /* bool */ $deferredLogging = false
);
```

#### Deferred Logging

If you only want to log based on certain responses you can use `$deferredLogging`:

```php
$response = $loggingHttpClient->sendRequest(
    $request,
    $appMethod,
    $reference,
    $requestProcessors,
    $responseProcessors,
    true // $deferredLogging - Logging will not happen unless LoggingHttpClient::logLastApiCall() is called
);
if (200 !== $response->getStatusCode()) {
    $loggingHttpClient->logLastApiCall();
}
```

### ApiCallLogger

The `ApiCallLogger` may be used to record requests and responses to both client and server side apis. Request and response payloads are both optional. If you are recording an FTP file upload, there may not be a response on successful upload. You would still invoke `logResponse` however to indicate the server accepted the file.

```php
/**
 * @var Assimtech\Dislog\ApiCallLoggerInterface $apiCallLogger
 * @var Assimtech\Dislog\Model\ApiCallInterface $apiCall
 */
$apiCall = $apiCallLogger->logRequest(
    /* ?string */ $request,
    /* ?string */ $endpoint,
    /* ?string */ $appMethod,
    /* ?string */ $reference,
    /* callable[]|callable|null */ $processors
);

$response = $api->transmit($request);

$this->apiCallLogger->logResponse($apiCall, $response);
```

Here's an example of dislog in a fake Api:

```php
use Assimtech\Dislog;

class Api
{
    protected $apiLogger;

    public function __construct(Dislog\ApiCallLoggerInterface $apiCallLogger)
    {
        $this->apiCallLogger = $apiCallLogger;
    }

    public function transmit($request)
    {
        return '<some response />';
    }

    public function doSomething()
    {
        $request = '<some request />';
        $endpoint = 'http://my.endpoint';
        $reference = time();

        $apiCall = $this->apiCallLogger->logRequest(
            $request,
            $endpoint,
            __METHOD__,
            $reference
        );

        $response = $this->transmit($request);

        $this->apiCallLogger->logResponse(
            $apiCall,
            $response
        );
    }
}

$stream = fopen('/tmp/my.log', 'a');
$uniqueIdentity = new Dislog\Identity\UniqueIdGenerator();
$stringSerializer = new Dislog\Serializer\StringSerializer();
$streamHandler = new Dislog\Handler\Stream($stream, $uniqueIdentity, $stringSerializer);
$apiCallFactory = new Dislog\Factory\ApiCallFactory();
$apiCallLogger = new Dislog\ApiCallLogger($apiCallFactory, $streamHandler);

$api = new Api($apiCallLogger);
$api->doSomething();
```

Old logs can be cleaned up by calling remove on supporting handlers:

```php
$handler->remove(60 * 60 * 24 * 30); // keep 30 days worth of logs
```

## Handlers

### Stream

This handler accepts a writable stream resource. You must also give it an identity generator and a serializer.

```php
use Assimtech\Dislog;

$stream = fopen('/tmp/my.log', 'a');
$uniqueIdentity = new Dislog\Identity\UniqueIdGenerator();
$stringSerializer = new Dislog\Serializer\StringSerializer();

$streamHandler = new Dislog\Handler\Stream($stream, $uniqueIdentity, $stringSerializer);
```

### DoctrineDocumentManager

This handler accepts a `Doctrine\ODM\MongoDB\DocumentManager`.

**Note: You must setup any mapping to an `Assimtech\Dislog\Model\ApiCallInterface` in your document manager**
**WARNING: It is advisable to avoid using your application's default document manager as a `flush()` from dislog may interfere with your application**

```php
$documentHandler = new Dislog\Handler\DoctrineDocumentManager($documentManager);
```

### DoctrineEntityManager

This handler accepts a `Doctrine\ORM\EntityManagerInterface`.

**Note: You must setup any mapping to an `Assimtech\Dislog\Model\ApiCallInterface` in your entity manager**
**WARNING: It is advisable to avoid using your application's default entity manager as a `flush()` from dislog may interfere with your application**

```php
$entityHandler = new Dislog\Handler\DoctrineEntityManager($entityManager);
```

## Processors

A processor is a callable which is executed on either the request or response payload. They can be used for modifying the request or response before the ApiCall is handled. An example might be to mask credit card numbers or obfuscate a password.

Processors are passed along with the `logRequest` and / or `logResponse` calls to process the appropriate payload.

**Note: Processors are not invoked on a null request / response.**

```php
function getMaskedCard($card)
{
    $firstSix = substr($card, 0, 6);
    $lastFour = substr($card, -4);
    $middle = str_repeat('*', strlen($card) - 10);
    return $firstSix . $middle . $lastFour;
}

$endpoint = 'https://my.endpoint';
$appMethod = 'processPayment';
$reference = time();
$card = '4444333322221111';
$cvv = '123';
$request = json_encode([
    'amount' => 12.95,
    'card' => $card,
    'expiry' => '2021-04',
    'cvv' => $cvv,
]);

$maskCard = function ($request) use ($card) {
    $maskedCard = getMaskedCard($card);
    return str_replace($card, $maskedCard, $request);
};
$obfuscateCvv = function ($request) use ($cvv) {
    return str_replace($cvv, '***', $request);
};
$apiCallLogger->logRequest(
    $request,
    $endpoint,
    $appMethod,
    $reference,
    [
        $maskCard,
        $obfuscateCvv,
    )
);
```

### StringReplace

This processor is based on php's `str_replace`. It will replace a known string in a request / response.

```php
$maskedCard = getMaskedCard($card);
$obfuscatedCvv = '***';
$stringReplace = new Assimtech\Dislog\Processor\StringReplace([
    $card,
    $cvv,
], [
    $maskedCard,
    $obfuscatedCvv,
]);
$apiCallLogger->logRequest(
    $request,
    $endpoint,
    $appMethod,
    $reference,
    $stringReplace
);
```

### RegexReplace

This processor is based on php's `preg_replace`. It will replace a regex in a request / response.

```php
$response = '{social_security_number: "1234567890"}';
$regexReplace = new Assimtech\Dislog\Processor\RegexReplace(
    '/social_security_number: "(\d\d)\d+(\d\d)"/',
    'social_security_number: "$1***$2"'
);
$apiCallLogger->logResponse(
    $apiCall,
    $response,
    $regexReplace
);
```

## Serializers

A Serializer is a callable which converts an `ApiCall` into something a handler can deal with. Not all handers need to be
paired with a Serializer and can deal with a raw `ApiCall` (e.g. `DoctrineObjectManager`).
