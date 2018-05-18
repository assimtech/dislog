# Dislog

[![Latest Stable Version](https://poser.pugx.org/assimtech/dislog/v/stable)](https://packagist.org/packages/assimtech/dislog)
[![Total Downloads](https://poser.pugx.org/assimtech/dislog/downloads)](https://packagist.org/packages/assimtech/dislog)
[![Latest Unstable Version](https://poser.pugx.org/assimtech/dislog/v/unstable)](https://packagist.org/packages/assimtech/dislog)
[![License](https://poser.pugx.org/assimtech/dislog/license)](https://packagist.org/packages/assimtech/dislog)
[![Build Status](https://travis-ci.org/assimtech/dislog.svg?branch=master)](https://travis-ci.org/assimtech/dislog)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/assimtech/dislog/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/assimtech/dislog/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/assimtech/dislog/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/assimtech/dislog/?branch=master)

Dislog is an API call logger. API calls differ from normal log events because they compose of a request and a response which happen at different times however should be logged together as they are related.


## Framework integration

[Symfony 2 - DislogBundle](https://github.com/assimtech/dislog-bundle)


## Usage

The `ApiCallLogger` may be used to record requests and responses to both client and server side apis. Request and response payloads are both optional. If you are recording an FTP file upload, there may not be a response on successful upload. You would still invoke `logResponse` however to indicate the server accepted the file.

```php
/**
 * @var Assimtech\Dislog\ApiCallLoggerInterface $apiCallLogger
 * @var Assimtech\Dislog\Model\ApiCallInterface $apiCall
 */
$apiCall = $apiCallLogger->logRequest($request, $endpoint, $method, $reference);

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


### DoctrineObjectManager

This handler accepts any `Doctrine\Common\Persistence\ObjectManager`:

* Doctrine\ORM\EntityManager
* Doctrine\ODM\MongoDB\DocumentManager
* Doctrine\ODM\CouchDB\DocumentManager

**Note: You must setup any mapping to an `Assimtech\Dislog\Model\ApiCallInterface` in your object manager**
**WARNING: It is advisable to avoid using your application's default object manager as a `flush()` from dislog may interfere with your application**


```php
$doctrineHandler = new Dislog\Handler\DoctrineObjectManager($om);
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


### Aliasing Processors

Generally processors are passed into the `logRequest` / `logResponse` calls however the `ApiCallLogger` implementation
supports registering processor instances with an alias. This can be useful if you are re-using the same processors for
multiple different api calls (quite common when masking passwords).

```php
$apiCallLogger->setAliasedProcessor('card.number', $maskCard);
$apiCallLogger->setAliasedProcessor('card.cvv', $obfuscateCvv);

$apiCallLogger->logRequest(
    $request,
    $endpoint,
    $method,
    $reference,
    array(
        'card.number',
        'card.cvv',
    )
);
```


### StringReplace

This processor is based on php's `str_replace`. It will replace a known string in a request / response.

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
