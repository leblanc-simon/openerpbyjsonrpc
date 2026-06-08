# OpenErpByJsonRpc — Documentation

`OpenErpByJsonRpc` is a small PHP library that talks to an
[Odoo](https://www.odoo.com/) / OpenERP server through its JSON-RPC web API.

It gives you a thin, typed, object-oriented layer over the raw JSON-RPC calls so
you can authenticate, read and write records, run searches, manage user sessions
and administer databases without crafting HTTP payloads by hand.

The library transparently supports both the legacy controllers (Odoo &lt; 15) and
the newer controllers introduced in Odoo 15+, so the same code works across a
wide range of server versions.

## Table of contents

1. [Getting started](getting-started.md) — requirements, installation and your
   first connected script.
2. [Connection & authentication](connection-and-authentication.md) — how to
   build the client, log in, and reuse sessions.
3. [Working with records](working-with-records.md) — the `Model` client:
   read, search, create, update and delete records.
4. [Building queries](building-queries.md) — the fluent `Criteria` builder and
   the raw domain syntax.
5. [Sessions](sessions.md) — the `Session` client: session info, languages,
   modules and password changes.
6. [Databases](databases.md) — the `Database` client: list, create, duplicate
   and drop databases.
7. [Session storage](session-storage.md) — persisting sessions with
   `FileStorage`, `NullStorage` or your own backend.
8. [Error handling](error-handling.md) — the exception hierarchy and how to
   react to failures.
9. [API reference](api-reference.md) — a concise, exhaustive list of every
   public class and method.
10. [Development](development.md) — tooling for contributors: coding standards,
    static analysis, refactoring and tests.

> Working with an AI assistant? A condensed, single-file reference optimised for
> large language models is available in [`llms.md`](llms.md).

## At a glance

```php
use OpenErpByJsonRpc\JsonRpc\ZendJsonRpc;
use OpenErpByJsonRpc\JsonRpc\OpenERP;
use OpenErpByJsonRpc\Storage\FileStorage;
use OpenErpByJsonRpc\Client\Model;
use OpenErpByJsonRpc\Criteria;

// 1. Build the transport and the high-level client.
$transport = new ZendJsonRpc('https://odoo.example.com');
$odoo = new OpenERP($transport, new FileStorage([
    'directory' => __DIR__ . '/var/cache',
    'prefix'    => 'odoo_session_',
]));

// 2. Configure the connection and authenticate.
$odoo
    ->setBaseUri('https://odoo.example.com')
    ->setPort(null)
    ->setUsername('admin')
    ->setPassword('admin')
    ->setDatabase('main')
    ->reconnectOrLogin(null);

// 3. Use a client to query the server.
$model = new Model($odoo);

$users = $model->search('res.users', (new Criteria())->equal('share', false), ['id', 'login']);
```

## License

Released under the [MIT](http://opensource.org/licenses/MIT) license.

Author: Simon Leblanc &lt;contact@leblanc-simon.eu&gt;
