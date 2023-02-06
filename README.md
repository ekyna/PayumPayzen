PayumPayzen
===========

PayZen Payum Gateway (Systempay, Scellius, CLIC&PAY, OSB, SOGE_COMMERCE)

[![Build](https://github.com/ekyna/PayumPayzen/actions/workflows/build.yml/badge.svg?branch=master)](https://github.com/ekyna/PayumPayzen/actions/workflows/build.yml)

## Installation / Configuration

```bash
composer require ekyna/payum-payzen
```

```php
use Ekyna\Component\Payum\Payzen\Api\Api;
use Ekyna\Component\Payum\Payzen\PayzenGatewayFactory;

$factory = new PayzenGatewayFactory();

$gateway = $factory->create([
    'site_id'     => '132456',
    'certificate' => '132456',
    'ctx_mode'    => Api::MODE_PRODUCTION,
    'hash_mode'   => Api::HASH_MODE_SHA256,
    'directory'   => __DIR__ . '/payzen-cache',
    'endpoint'    => Api::ENDPOINT_SYSTEMPAY, // default value, see `Api::ENDPOINT_*` constants for more
    'endpoint_url'    => // null default value, add custom endpoint not in  `Api::ENDPOINT_*` constants 
]);

// Register your convert payment action
// $gateway->addAction(new \Acme\ConvertPaymentAction());
```
