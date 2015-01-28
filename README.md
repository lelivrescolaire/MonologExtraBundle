[![LeLivreScolaire](http://h2010.associationhec.com/images/news/logo-officiel-jpeg.jpg)](http://www.lelivrescolaire.fr)

# *Monolog Extra Bundle* [![Build Status](https://secure.travis-ci.org/lelivrescolaire/MonologExtraBundle.png?branch=master)](http://travis-ci.org/lelivrescolaire/MonologExtraBundle) [![Coverage Status](https://coveralls.io/repos/lelivrescolaire/MonologExtraBundle/badge.png?branch=master)](https://coveralls.io/r/lelivrescolaire/MonologExtraBundle?branch=master)

Extend your Monolog stack with some missing tools.

## Installation

```shell
$ composer require "lelivrescolaire/monolog-extra-bundle:dev-master"
```

AppKernel:

```php
public function registerBundles()
{
    $bundles = array(
        new LLS\Bundle\MonologExtraBundle\LLSMonologExtraBundle(),
    );
}
```

## Configuration reference

```yml
lls_monolog_extra:
    handlers:           # Create custom handlers
        sqs_handler:
            type: sqs       # Handler using Queue from SQSBundle
            queue: myQueue  # Queue identifier
            level: INFO     # Log level (int or label)
            bubble: true    # Whether or not execute next handlers

monolog:
    handlers:
        sqs:
            type:     service
            id:       lls_monolog_extra.handlers.sqs_handler  # Auto generated service
            priority: 0
```

Read more documentation [here](./Resources/doc/index.md)

## Contribution

Feel free to send us [Pull Requests](https://github.com/lelivrescolaire/MonologExtraBundle/compare) and [Issues](https://github.com/lelivrescolaire/MonologExtraBundle/issues/new) with your fixs and features.

## Run test

### Unit tests

```shell
$ ./bin/atoum
```

### Coding standards

```shell
$ ./bin/coke
```
