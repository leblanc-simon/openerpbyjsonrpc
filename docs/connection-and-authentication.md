# Connection & authentication

The `OpenERP` class (`OpenErpByJsonRpc\JsonRpc\OpenERP`) is the heart of the
library. It owns the connection settings, performs authentication, and is passed
to every feature client (`Model`, `Session`, `Database`).

## Building the client

`OpenERP` takes two collaborators in its constructor:

```php
use OpenErpByJsonRpc\JsonRpc\ZendJsonRpc;
use OpenErpByJsonRpc\JsonRpc\OpenERP;
use OpenErpByJsonRpc\Storage\FileStorage;

$transport = new ZendJsonRpc('https://odoo.example.com');
$storage   = new FileStorage([
    'directory' => __DIR__ . '/var/cache',
    'prefix'    => 'odoo_session_',
]);

$odoo = new OpenERP($transport, $storage);
```

- **`$transport`** implements `JsonRpcInterface`. The bundled implementation is
  `ZendJsonRpc`, which performs the JSON-RPC HTTP calls. Its constructor accepts
  an optional base URI (and optional username/password, unused by the
  high-level flow).
- **`$storage`** implements `StorageInterface` and is where the authenticated
  session is persisted. See [Session storage](session-storage.md).

## Configuring the connection

`OpenERP` exposes a set of **fluent setters** (each returns `$this`, so they can
be chained):

```php
$odoo
    ->setBaseUri('https://odoo.example.com') // server URL
    ->setPort(8069)                          // or null for the scheme default
    ->setUsername('admin')                   // Odoo login
    ->setPassword('admin')                   // Odoo password
    ->setDatabase('main');                   // database name
```

| Method | Argument | Purpose |
|--------|----------|---------|
| `setBaseUri(string $baseUri)` | server URL | Base URL used to build every request. Required. |
| `setPort(?int $port)` | port or `null` | Appended to the URI when not null. Use `null` to keep the scheme's default port. |
| `setUsername(string $username)` | login | Used during login. |
| `setPassword(string $password)` | password | Used during login. |
| `setDatabase(string $database)` | db name | The Odoo database to authenticate against. |

> The base URI is typically the same value you passed to `ZendJsonRpc`. Setting
> it on `OpenERP` is what the request builder actually uses.

## Authenticating

Authentication is driven by a single method:

```php
public function reconnectOrLogin(?string $sessionId): bool
```

It returns `true` on success and `false` on failure. Its behaviour depends on
the argument:

### Fresh login — `reconnectOrLogin(null)`

Pass `null` to force a brand-new login using the username, password and database
you configured. On success:

- the session is opened on the server,
- the session identifier and user context are stored in your `StorageInterface`
  (keyed by the session id),
- subsequent calls reuse the session automatically.

```php
$odoo
    ->setUsername('admin')
    ->setPassword('admin')
    ->setDatabase('main')
    ->reconnectOrLogin(null);
```

### Reconnecting — `reconnectOrLogin($sessionId)`

Pass a previously obtained session id to **restore** a session from storage
instead of logging in again. The client reads the stored session, re-applies the
cookie, and validates it against the server. If the stored session is missing or
no longer valid, it transparently falls back to a full login (so you still need
valid credentials configured as a safety net).

```php
// Retrieve the id from a previous run, e.g. from Session::getInfos().
$sessionId = $myPersistedSessionId;

$odoo
    ->setBaseUri('https://odoo.example.com')
    ->setPort(null)
    ->reconnectOrLogin($sessionId);
```

This is the mechanism that lets long-running or repeated processes avoid sending
credentials on every request — combine it with a persistent
[`FileStorage`](session-storage.md).

## Inspecting connection state

```php
$odoo->isLogged();        // bool — is a session currently open?
$odoo->getSessionId();    // ?string — the current session identifier (cookie value)
$odoo->isOdoo15OrMore();  // bool — true if the server is Odoo 15 or newer
```

`getSessionId()` is the value you persist and later hand back to
`reconnectOrLogin()`. You can also obtain a richer set of session details
(including the session id and the user id) through the
[`Session` client](sessions.md).

`isOdoo15OrMore()` is used internally to pick the right controllers; it is
exposed in case you need to branch on the server version yourself.

## Long-running calls

Some operations (notably database management) can take much longer than a normal
request. `OpenERP::prepareLongCall()` raises the transport timeout for the very
next call. The `Database` client calls this for you, so you rarely need it
directly — but it is available if you issue your own long low-level calls.

## Low-level calls (advanced)

For operations not covered by the feature clients you can call the server
directly. These return the raw decoded JSON-RPC result (`mixed`):

```php
// Authenticated JSON-RPC call to /web/<path>.
$odoo->call(string $path, array $params = []);

// JSON-RPC call without an active session (e.g. public endpoints).
$odoo->callWithoutCredential(string $path, array $params = []);

// Plain HTTP POST without credentials (used by the Odoo 15+ database manager).
$odoo->httpCallWithoutCredential(string $path, array $params = []);

// Invoke a model method through /web/dataset/call_kw.
$odoo->callBase(string $model, string $method, array $args = [], ?array $kwargs = null);
```

`callBase()` is the generic entry point the `Model` client is built on. Use it to
reach model methods that have no dedicated helper, for example:

```php
$ids = $odoo->callBase('res.partner', 'search', [[['is_company', '=', true]]]);
```

See [Working with records](working-with-records.md) for the friendlier wrappers.
