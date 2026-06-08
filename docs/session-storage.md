# Session storage

When you authenticate, `OpenERP` persists the session (its identifier, the user
context and the cookie) through a **storage backend**. On a later run you can
hand the stored session id back to
[`reconnectOrLogin()`](connection-and-authentication.md#reconnecting--reconnectorloginsession_id)
and skip a full login.

A storage backend is any class implementing
`OpenErpByJsonRpc\Storage\StorageInterface`. The library ships two
implementations.

## The `StorageInterface` contract

```php
namespace OpenErpByJsonRpc\Storage;

interface StorageInterface
{
    public function __construct(array $options = []);

    public function read(string $key): mixed;

    public function write(string $key, mixed $data): self;
}
```

- `read($key)` returns the previously stored value, or `null` when the key is
  unknown.
- `write($key, $data)` stores a value and returns the storage instance.

## `FileStorage` — persistent, file-based storage

`OpenErpByJsonRpc\Storage\FileStorage` stores each session as a JSON file on
disk. This is the implementation to use when you want sessions to survive across
script runs.

```php
use OpenErpByJsonRpc\Storage\FileStorage;

$storage = new FileStorage([
    'directory' => __DIR__ . '/var/cache', // required
    'prefix'    => 'odoo_session_',        // required
]);
```

### Options

| Option | Required | Description |
|--------|----------|-------------|
| `directory` | yes | Path where session files are written. It **must already exist** and be both readable and writable. |
| `prefix` | yes | Filename prefix for stored sessions, so several apps can share a directory without clashing. |

If `directory` or `prefix` is missing, or if the directory does not exist / is
not readable-writable, the constructor throws a
`OpenErpByJsonRpc\Storage\Exception\OptionException`. A failed write throws
`OpenErpByJsonRpc\Storage\Exception\WriteException`. See
[Error handling](error-handling.md).

### Reusing a session across runs

```php
// First run: log in and remember the session id somewhere durable.
$odoo->setUsername('admin')->setPassword('admin')->setDatabase('main');
$odoo->reconnectOrLogin(null);

$sessionId = (new \OpenErpByJsonRpc\Client\Session($odoo))->getInfos()['session_id'];
// ... persist $sessionId (file, cache, etc.) ...

// Later run: restore the session, no credentials needed (with a FileStorage
// pointed at the same directory/prefix).
$odoo->setBaseUri('https://odoo.example.com')->setPort(null);
$odoo->reconnectOrLogin($sessionId);
```

If the stored session is gone or invalid, `reconnectOrLogin()` falls back to a
full login, so keeping valid credentials configured remains a good safety net.

## `NullStorage` — no persistence

`OpenErpByJsonRpc\Storage\NullStorage` implements the interface but stores
nothing: `read()` always returns `null` and `write()` is a no-op. Use it when you
don't need session reuse — for example one-off scripts or tests.

```php
use OpenErpByJsonRpc\Storage\NullStorage;

$odoo = new OpenERP($transport, new NullStorage());
```

With `NullStorage`, every `reconnectOrLogin(null)` performs a fresh login, and
reconnecting by id is not possible (there is nothing stored to restore).

## Writing your own backend

Implement `StorageInterface` to persist sessions wherever you like (Redis, a
database, APCu, …):

```php
use OpenErpByJsonRpc\Storage\StorageInterface;

final class RedisStorage implements StorageInterface
{
    private \Redis $redis;
    private string $prefix;

    public function __construct(array $options = [])
    {
        $this->redis  = $options['redis'];
        $this->prefix = $options['prefix'] ?? 'odoo_session_';
    }

    public function read(string $key): mixed
    {
        $raw = $this->redis->get($this->prefix . $key);

        return false === $raw ? null : json_decode($raw, true);
    }

    public function write(string $key, mixed $data): self
    {
        $this->redis->set($this->prefix . $key, json_encode($data));

        return $this;
    }
}
```

Then pass an instance to `OpenERP`:

```php
$odoo = new OpenERP($transport, new RedisStorage(['redis' => $redis]));
```
