# dislog

[![Build Status](https://travis-ci.org/assimtech/dislog.svg?branch=master)](https://travis-ci.org/assimtech/dislog)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/assimtech/dislog/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/assimtech/dislog/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/assimtech/dislog/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/assimtech/dislog/?branch=master)

Dislog is an API call logger. API calls differ from normal log events because they compose of a request and a response which generally happen at different times.


## Usage

The `ApiCallLogger` may be used to record requests and responses to both client and server site apis. Request and
response payloads are both optional. If you are recording an FTP file upload, there may not be a response on successful upload. You would sill invoke `logResponse` however to indicate the server accepted the file.

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

        $apiCall = $this->apiCallLogger->logRequest($request, $endpoint, __METHOD__, $reference);

        $response = $this->transmit($request);

        $this->apiCallLogger->logResponse($apiCall, $response);
    }
}

$stream = fopen('/tmp/my.log', 'a');
$identityGenerator = new Dislog\Identity\UniqueIdGenerator();
$stringSerializer = new Dislog\Serializer\StringSerializer();
$streamHandler = new Dislog\Handler\Stream($stream, $identityGenerator, $stringSerializer);
$apiCallFactory = new Dislog\Model\Factory\ApiCallFactory();
$apiCallLogger = new Dislog\ApiCallLogger($apiCallFactory, $streamHandler);

$api = new Api($apiCallLogger);
$api->doSomething();
```


## Handlers

### Stream

This handler accepts a writable stream resource


### DoctrineObjectManager

This handler accepts any Doctrine Object Manager:

* Doctrine\ORM\EntityManager
* Doctrine\ODM\MongoDB\DocumentManager
* Doctrine\ODM\CouchDB\DocumentManager


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
$method = 'processPayment';
$reference = time();
$card = '4444333322221111';
$cvv = '123';
$request = json_encode(array(
    'amount' => 12.95,
    'card' => $card,
    'expiry' => '2021-04',
    'cvv' => $cvv,
));

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
    $method,
    $reference,
    array(
        $maskCard,
        $obfuscateCvv,
    )
);
```

### StringReplace

This processor is based on php's `str_replace`. It will replace a known string in the payload.

```php
$maskedCard = getMaskedCard($card);
$obfuscatedCvv = '***';
$stringReplace = new Assimtech\Dislog\Processor\StringReplace(array(
    $card,
    $cvv,
), array(
    $maskedCard,
    $obfuscatedCvv,
));
$apiCallLogger->logRequest(
    $request,
    $endpoint,
    $method,
    $reference,
    $stringReplace
);
```


## Serializers

A Serializer is a callable which converts an `ApiCall` into something a handler can deal with. Not all handers need to be
paired with a Serializer and can deal with a raw `ApiCall` (e.g. `DoctrineObjectManager`).
