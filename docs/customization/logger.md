---
layout: doc
title: Log errors
section: customization
since: 0.6.0
---

Log errors can be really useful when your website is online to not show errors to the client or to simply log when you develop a theme or plugin.

Parvula suggest [Seldaek/Monolog](https://github.com/Seldaek/monolog) to log what you want.

# Use monolog

1. Firstly install Monolog `composer require monolog/monolog`
2. If you want to log error and exception, enable the log [`logErrors: true`](https://github.com/BafS/parvula/blob/v0.6.0/data/config/system.yaml#L11) in system.yaml
3. It is recommended to disable the debug because Whoops block the current exception and throw a new exception. [`debug: true`](https://github.com/BafS/parvula/blob/v0.6.0/data/config/system.yaml#L6) in system.yaml

To log what you want, use the service registered as '*logger*' in the router (`$this->logger`).

A basic example could be:

```php
$this->logger->addError('Hello world');
```
