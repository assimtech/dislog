# dislog

[![Build Status](https://travis-ci.org/assimtech/dislog.svg?branch=master)](https://travis-ci.org/assimtech/dislog)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/assimtech/dislog/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/assimtech/dislog/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/assimtech/dislog/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/assimtech/dislog/?branch=master)

Dislog is an API call logger. API calls differ from normal log events because they compose of a request and a response which generally happen at different times.

## Handlers

# DoctrineObjectManager

This handler accepts any Doctrine Object Manager:

* Doctrine\ORM\EntityManager
* Doctrine\ODM\MongoDB\DocumentManager
* Doctrine\ODM\CouchDB\DocumentManager
