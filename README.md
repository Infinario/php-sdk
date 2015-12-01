# Infinario PHP SDK

![](https://travis-ci.org/Infinario/php-sdk.svg)

The `\Infinario\Infinario` class provides access to the Infinario PHP tracking API.
The SDK requires PHP >= 5.3.2 and php5-curl.

## Installation

Install the latest version with [Composer](https://getcomposer.org/):

```bash
composer require infinario/infinario
```


## Getting started

In order to track events, instantiate the class at least with your project token
(can be found in Project Management in your Infinario account), for example:

```php
use Infinario\Infinario;

$infinario = new Infinario('12345678-90ab-cdef-1234-567890abcdef');                       // PRODUCTION ENVIRONMENT
// $infinario = new Infinario('12345678-90ab-cdef-1234-567890abcdef', ['debug' => true]); // DEVELOPMENT ENVIRONMENT
```

You can also provide a [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
compliant logger interface:

```php
$infinario = new Infinario('12345678-90ab-cdef-1234-567890abcdef', ['logger' => $logger]);
```


Timeout for sending data is set to 1000ms you can overwrite it by following option:

```php
$infinario = new Infinario('12345678-90ab-cdef-1234-567890abcdef', ['timeout' => 500]);
```


## Identifying the customer

When tracking events, you have to specify which customer generated
them. This can be either done right when calling the client's
constructor.

```php
use Infinario\Infinario;

$infinario = new Infinario('12345678-90ab-cdef-1234-567890abcdef', ['customer' => 'john123']);
```

or by calling `identify`.

```php
$infinario->identify('john123');
```

## Tracking events

To track events for the currently selected customer, simply
call the `track` method.

```php
$infinario->track('purchase');
```

You can also specify an array of event properties to store
with the event.

```php
$infinario->track('purchase', ['product' => 'bottle', 'amount' => 5]);
```

## Updating customer properties

You can also update information that is stored with a customer.

```php
$infinario->update(['first_name' => 'John', 'last_name' => 'Smith']);
```
