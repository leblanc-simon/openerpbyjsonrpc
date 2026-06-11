# Getting started

This page walks you through the requirements, installation, and a complete
working example.

## Requirements

| Requirement | Version / note |
|-------------|----------------|
| PHP         | `>= 8.5` (the library uses `declare(strict_types=1)` and modern type hints) |
| Extensions  | `ext-json` |
| Odoo / OpenERP | Any version reachable through the JSON-RPC web controllers. Both Odoo &lt; 15 and Odoo 15+ are supported. |

The library depends on a fork of `laminas/laminas-json-server` (pinned to a
PHP-8.5 compatible branch), which is the engine that performs the actual
JSON-RPC HTTP calls.

## Installation

Install with [Composer](https://getcomposer.org/):

```bash
composer require leblanc-simon/openerpbyjsonrpc
```

Because the library relies on a fork of `laminas/laminas-json-server`, you must
declare the corresponding VCS repository in your project's `composer.json`
**before** running the command above:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/leblanc-simon/laminas-json-server"
        }
    ]
}
```

The package follows [PSR-4](https://www.php-fig.org/psr/psr-4/) autoloading under
the `OpenErpByJsonRpc\` namespace, so once installed you only need Composer's
autoloader:

```php
require __DIR__ . '/vendor/autoload.php';
```

## The three building blocks

Every script follows the same pattern, built from three layers:

1. **A transport** (`ZendJsonRpc`) — performs the raw JSON-RPC HTTP calls.
2. **A session store** (`FileStorage`, `NullStorage`, or your own) — keeps the
   authenticated session so you can reconnect without sending credentials every
   time.
3. **The high-level client** (`OpenERP`) — ties the transport and the store
   together, handles authentication, and exposes the calls used by the
   feature-specific clients (`Model`, `Session`, `Database`).

You then instantiate one or more **feature clients** on top of the `OpenERP`
object depending on what you want to do.

## A complete example

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use OpenErpByJsonRpc\JsonRpc\ZendJsonRpc;
use OpenErpByJsonRpc\JsonRpc\OpenERP;
use OpenErpByJsonRpc\Storage\FileStorage;
use OpenErpByJsonRpc\Client\Model;
use OpenErpByJsonRpc\Criteria;
use OpenErpByJsonRpc\Exception\JsonException;

// --- 1. Transport + session store -----------------------------------------
$transport = new ZendJsonRpc('https://odoo.example.com');

$storage = new FileStorage([
    'directory' => __DIR__ . '/var/cache', // must already exist and be writable
    'prefix'    => 'odoo_session_',
]);

// --- 2. High-level client + authentication ---------------------------------
$odoo = new OpenERP($transport, $storage);
$odoo
    ->setBaseUri('https://odoo.example.com')
    ->setPort(null)            // null = default port for the scheme
    ->setUsername('admin')
    ->setPassword('admin')
    ->setDatabase('main');

if (false === $odoo->reconnectOrLogin(null)) {
    throw new \RuntimeException('Unable to authenticate against Odoo.');
}

// --- 3. Do something useful ------------------------------------------------
$model = new Model($odoo);

try {
    $partners = $model->search(
        'res.partner',
        (new Criteria())->equal('is_company', true),
        ['id', 'name', 'email'],
        0,    // offset
        10    // limit
    );

    foreach ($partners as $partner) {
        printf("#%d — %s\n", $partner['id'], $partner['name']);
    }
} catch (JsonException $e) {
    fwrite(STDERR, 'Odoo request failed: ' . $e->getMessage() . PHP_EOL);
}
```

## Where to go next

- To understand authentication and session reuse in depth, read
  [Connection & authentication](connection-and-authentication.md).
- To learn the full CRUD surface, read [Working with records](working-with-records.md).
- To build expressive searches, read [Building queries](building-queries.md).
