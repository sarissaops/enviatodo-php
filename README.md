# Enviatodo PHP SDK

A clean, framework-agnostic PSR-18 HTTP client wrapper around the [Enviatodo Shipping API (V2)](https://app.enviatodo.com/#Api) — quotes, orders, labels, pickups, addresses, packages, carriers and catalogs for shipping inside Mexico.

[![Latest Version](https://img.shields.io/github/release/sarissaops/enviatodo-php.svg?style=flat-square)](https://github.com/sarissaops/enviatodo-php/releases)
[![License](https://img.shields.io/github/license/sarissaops/enviatodo-php.svg?style=flat-square)](LICENSE)

The SDK is not coupled to any specific HTTP library. Bring your own PSR-18 client (e.g. `symfony/http-client`, `guzzlehttp/guzzle`) and PSR-7/PSR-17 implementation (e.g. `nyholm/psr7`); auto-discovery configures them for you.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Balance](#balance)
- [Postal Codes](#postal-codes)
- [Usage](#usage)
- [Response Handling](#response-handling)
- [Debugging](#debugging)
- [Framework Integration](#framework-integration)
- [Contributing](#contributing)

---

## Requirements

- PHP **8.2** or higher
- A PSR-18 HTTP client (e.g. `symfony/http-client`, `guzzlehttp/guzzle`)
- A PSR-7 / PSR-17 implementation (e.g. `nyholm/psr7`)
- An Enviatodo API token (sandbox tokens work against the QA endpoint below)

---

## Installation

```bash
composer require sarissaops/enviatodo-php symfony/http-client nyholm/psr7
```

---

## Quick Start

```php
require 'vendor/autoload.php';

use SarissaOps\Enviatodo\Enviatodo;

// Sandbox (default endpoint: https://apiqav2.enviatodo.mx/index.php/)
$client = Enviatodo::create('your-sandbox-token');

// Production endpoint: confirm the current base URL in the official
// API docs before pointing production traffic at it.
$client = Enviatodo::create('your-token', 'https://api.enviatodo.mx/index.php/');
```

Every request carries `Authorization: Bearer <token>` plus `x-api-key: enviatodo` and `x-enviatodo-app: custom` automatically. Prefer a custom PSR-18 client or factories? Inject them:

```php
use SarissaOps\Enviatodo\HttpClient\HttpClientConfigurator;

$configurator = (new HttpClientConfigurator())
    ->setToken('your-token')
    ->setEndpoint('https://apiqav2.enviatodo.mx/index.php/')
    ->setHttpClient($psr18Client);

$client = new Enviatodo($configurator);
```

---

## Balance

Check the account balance:

```php
$balance = $client->balance()->show();

echo $balance->getBalance(); // e.g. 758.13 (MXN)
```

---

## Postal Codes

Look up a postal code:

```php
$zip = $client->zipCode()->show('64000');

foreach ($zip->getItems() as $item) {
    echo $item['suburb_name'] . PHP_EOL;
}
```

---

## Usage

One method per API operation across 10 domains — `address()`, `package()`, `parcel()`, `quote()`, `order()`, `guide()`, `pickup()`, `catalog()` (plus `balance()` and `zipCode()` above). Full per-resource recipes live in [doc/examples.md](doc/examples.md); the domain map in [doc/index.md](doc/index.md); envelope and error semantics in [doc/errors.md](doc/errors.md).

```php
// Quote all carriers, then create the order from the returned UUID.
$quote = $client->quote()->all($quotePayload);

foreach ($quote->getRates() as $rate) {
    echo "{$rate->getServiceName()}" . PHP_EOL;
}

$order = $client->order()->create(
    uuid: $quote->getTransactionUuid(),
    providerId: '9',
    serviceId: '11',
    insurance: false,
);

foreach ($order->getGuides() as $guide) {
    echo $guide->getTrackingId() . PHP_EOL;
}
```

---

## Response Handling

All API methods return typed model objects with IDE-friendly getters by default. The Enviatodo envelope (`{success, message, data, error, code}`) is unwrapped for you — and `error: true` raises even on HTTP 200, since this API can fail inside a 200 response:

```php
use SarissaOps\Enviatodo\Exception\EnviatodoException;
use SarissaOps\Enviatodo\Exception\HttpClientException;
use SarissaOps\Enviatodo\Exception\HttpServerException;

try {
    $quote = $client->quote()->all($quote);
} catch (HttpClientException $e) {
    echo $e->getResponseCode();          // HTTP status
    print_r($e->getResponseBody());      // decoded envelope
    $e->getResponse();                   // raw PSR-7 response
} catch (HttpServerException | EnviatodoException $e) {
    echo $e->getMessage();
}

$client->getLastResponse(); // last raw PSR-7 response, for logging
```

### Array responses

Prefer raw arrays? Inject `ArrayHydrator`:

```php
use SarissaOps\Enviatodo\Enviatodo;
use SarissaOps\Enviatodo\HttpClient\HttpClientConfigurator;
use SarissaOps\Enviatodo\Hydrator\ArrayHydrator;

$configurator = (new HttpClientConfigurator())->setToken('your-token');

$client = new Enviatodo($configurator, new ArrayHydrator());

$data = $client->balance()->show();
// $data is now the plain unwrapped `data` payload
```

### Raw PSR-7 response

Need the raw response? Use `NoopHydrator` — **note: no exceptions are thrown on error responses when using this hydrator.**

```php
use SarissaOps\Enviatodo\Hydrator\NoopHydrator;

$client = new Enviatodo($configurator, new NoopHydrator());
$response = $client->balance()->show();
// $response is a Psr\Http\Message\ResponseInterface
echo $response->getStatusCode();
```

---

## Debugging

Point the client at a request bin and dump traffic with the raw hydrator:

```php
use SarissaOps\Enviatodo\HttpClient\HttpClientConfigurator;
use SarissaOps\Enviatodo\Hydrator\NoopHydrator;

$configurator = (new HttpClientConfigurator())
    ->setEndpoint('https://your-bin.example/abc123') // replace with your bin URL
    ->setToken('your-token')
    ->setDebug(true);

$client = new Enviatodo($configurator, new NoopHydrator());
$client->balance()->show();
```

---

## Framework Integration

The package is framework-free by design: it only needs *any* PSR-18 client. It stays portable across Symfony, Laravel, WooCommerce and plain CLI scripts.

| Backend | Install |
|---------|---------|
| Symfony HttpClient | `composer require symfony/http-client` |
| Guzzle | `composer require php-http/guzzle7-adapter guzzlehttp/guzzle` |
| PSR-7/PSR-17 factory | `composer require nyholm/psr7` |

Symfony bundle live in follow-up package [`sarissaops/enviatodo-bundle`](https://github.com/sarissaops/enviatodo-bundle.git).

---

## Contributing

TDD is mandatory: Red → Green → Refactor. Every endpoint ships as `Api\*Api` + `Model/` value object + facade method + `tests/Api` + `tests/Model`.

```bash
git clone git@github.com:sarissaops/enviatodo-php.git
cd enviatodo-php
composer install
composer validate
composer test
vendor/bin/phpunit tests/Api/BalanceApiTest.php
vendor/bin/phpstan analyse --level=max src tests
vendor/bin/php-cs-fixer fix --dry-run --diff
```

Sandbox runs need a token without hardcoding one: copy `.env.example` to `.env` and set `ENVIATODO_TOKEN` (see [doc/testing.md](doc/testing.md)). Integration tests read it from the environment and are skipped without it; mutating tests live behind `--group=mutating` and never run by default.
