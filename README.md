# dislog

[![Build Status](https://travis-ci.org/assimtech/dislog.svg?branch=master)](https://travis-ci.org/assimtech/dislog)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/assimtech/dislog/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/assimtech/dislog/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/assimtech/dislog/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/assimtech/dislog/?branch=master)

Dislog is an API call logger. API calls differ from normal log events because they compose of a request and a response which generally happen at different times.


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

Processors are passed along with the `logRequest` and / or `logResponse` calls to apply to the appropriate payload.

```php
function getMaskedCard($card)
{
    $firstSix = substr($card, 0, 6);
    $lastFour = substr($card, -4);
    $middle = str_repeat('*', strlen($card) - 10);
    return $firstSix . $middle . $lastFour;
}

$card = '4444333322221111';
$request = json_encode(array(
    'amount' => $amount,
    'card' => $card,
    'expiry' => $expiry,
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
use Assimtech\Dislog\Processor;

$request = json_encode(array(
    'username' => $username,
    'password' => $password,
    'body' => $body,
));

$maskPassword = new Processor\StringReplace($password, '***');
$apiCallLogger->logRequest(
    $request,
    $endpoint,
    $method,
    $reference,
    $maskPassword
);
```
