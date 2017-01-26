# Piper

[![Build Status](https://travis-ci.org/SolidWorx/Piper.svg)](https://travis-ci.org/SolidWorx/Piper)

Piper is pipeline process library or PHP. It allows you to pipe several processes to be executed in sequence.
 
 
## Table of Contents
- [Requirements](#requirements)
- [Installation](#installation)
    - [Composer](#composer)
- [Usage](#usage)
    - [Config](#config)
    - [Basic usage](#basec-usage)
    - [Passing information between processes](#passing-information-between-processes)
    - [Start a process with initial data](#start-a-process-with-initial-data)
    - [Rolling back changes](#rolling-back-changes)
    - [Using callables](#using-callables)
- [Testing](#testing)
- [Contributing](#contributing)
- [Licence](#licence)

## Requirements

Piper requires PHP 7.1+

## Installation

### Composer

```bash
$ composer require solidworx/piper:^2.0
```

## Usage

### Basic usage

Imagine you have an E-Commerce site, where users can buy items online.
When a user places an order, there are several things that needs to happen, E.G place the order, send a order confirmation email to the user, send an order notice to the site admin, create a shipping label etc.

All these processes can be piped together to be executed sequentially.

```php
<?php

use SolidWorx\Piper\Piper;
use SolidWorx\Piper\PipeInterface;
use SolidWorx\Piper\Context;

class PlaceOrder implements PipeInterface
{
    public function process(Context $context)
    {
        // Place the order in the database
    }
}

$piper = new Piper();

$piper->pipe(new PlaceOrder())
    ->pipe(new SendEmailConfirmation())
    ->pipe(new SendOrderPlacedEmail())
    ->pipe(new CreateShippingLabel())
    ;

$piper->process();

```

This will start by calling the `process` method on the `PlaceOrder` class, followed by `SendEmailConfirmation` etc.
The `process` method will handle all the logic needed before continuing to the next step.

### Passing information between processes

Sometimes you need to pass information between steps, for example, the first step might be to place the order, then you need the order id in the next step when sending the confirmation id.
 
In this scenarios, you can use the `Context` class to set information. This object is passed between steps where you can add or get information.

```php
<?php

use SolidWorx\Piper\PipeInterface;
use SolidWorx\Piper\Context;

class PlaceOrder implements PipeInterface
{
    public function process(Context $context)
    {
        $orderId = // Place the order in the database and get the id
        
        $context->set('orderId', $orderId);
    }
}

class SendEmailConfirmation implements PipeInterface
{
    public function process(Context $context)
    {
        // Get the order id from the previous step
        $orderId = $context->get('orderId');
    }
}
```

### Start a process with initial data

If you want to start the pipe process with an initial set of data (for example the user id), then you can just pass an instance of the `Context` class.

```php
<?php

use SolidWorx\Piper\Context;
use SolidWorx\Piper\Piper;

$context = new Context(['userId' => 123]);

$piper->process($context);

```

### Rolling back changes

If you want to roll back changes made by a step, then you can implement the `RollbackInterface` in your step.

When ever an error occurs in any step, all the previous steps will be scanned to check for a possible rollback.
If a step implements the `RollbackInterface` interface, then the `rollback` method will be executed, where any changes made in that step can be undone.

```php
<?php

use SolidWorx\Piper\RollbackInterface;
use SolidWorx\Piper\Context;

class PlaceOrder implements RollbackInterface
{
    public function process(Context $context)
    {
    }
    
    public function rollback(Context $context)
    {
        // An error occurred during the checkout process, undo the order here
    }
}

```

### Using callables

Piping steps can also take a any callable. This is useful of the step doesn't have much logic and doesn't warrant a standalone class

```php
<?php

use SolidWorx\Piper\Context;
use SolidWorx\Piper\Piper;

$piper = new Piper();

$piper->pipe(function (Context $context) {
    
});

```

## Testing

To run the unit tests, execute the following command

```bash
$ vendor/bin/phpunit
```

## Contributing

See [CONTRIBUTING](https://github.com/SolidWorx/Piper/blob/master/CONTRIBUTING.md)

## License

Piper is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

Please see the [LICENSE](LICENSE) file for the full license.
