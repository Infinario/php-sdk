# Infinario PHP SDK

The `\Infinario\Infinario` class provides access to the Infinario PHP tracking API.

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
$infinario->track('purchase', array('product' => 'bottle', 'amount' => 5));
```

## Updating customer properties

You can also update information that is stored with a customer.

```php
$infinario->update(array('first_name' => 'John', 'last_name' => 'Smith'));
```