# Error handling

The library signals failures with typed exceptions. Catching the right type lets
you react precisely; catching a base type lets you handle whole families at once.

## Exception hierarchy

There are two independent families plus one standalone exception.

### Core exceptions

All extend the abstract base `OpenErpByJsonRpc\Exception`, which itself extends
the PHP built-in `\Exception`.

```
\Exception
└── OpenErpByJsonRpc\Exception            (abstract base)
    ├── OpenErpByJsonRpc\Exception\ClientException
    ├── OpenErpByJsonRpc\Exception\JsonException
    ├── OpenErpByJsonRpc\Exception\LoginException
    └── OpenErpByJsonRpc\Exception\SessionException
```

| Exception | Raised when |
|-----------|-------------|
| `ClientException` | A client-side misuse is detected, e.g. passing an invalid criteria to `Model::search()`. |
| `JsonException` | A JSON-RPC / HTTP request fails or the server returns an error (failed model call, failed database operation, login failure, malformed response). |
| `LoginException` | Authentication-specific failure. |
| `SessionException` | A session could not be restored or is invalid (used internally by `reconnectOrLogin()` to trigger the login fallback). |

Because they share a base class, you can catch the whole family at once:

```php
use OpenErpByJsonRpc\Exception as OpenErpException;

try {
    $model->search('res.users', null, ['id']); // invalid criteria
} catch (OpenErpException $e) {
    // catches ClientException, JsonException, LoginException, SessionException
    error_log($e->getMessage());
}
```

### `NotSingleException`

`OpenErpByJsonRpc\Exception\NotSingleException` extends `\Exception` **directly**
(not the shared base). It is thrown by `Model::readOne()` when more than one
record matches the requested id(s):

```php
use OpenErpByJsonRpc\Exception\NotSingleException;

try {
    $model->readOne('res.country', [1, 2], ['id', 'name']);
} catch (NotSingleException $e) {
    // more than one record matched — use read() instead
}
```

### Storage exceptions

Storage backends raise their own family, all extending
`OpenErpByJsonRpc\Storage\Exception\StorageException` (which extends
`\Exception`).

```
\Exception
└── OpenErpByJsonRpc\Storage\Exception\StorageException
    ├── OpenErpByJsonRpc\Storage\Exception\OptionException
    └── OpenErpByJsonRpc\Storage\Exception\WriteException
```

| Exception | Raised when |
|-----------|-------------|
| `OptionException` | Invalid storage options — e.g. `FileStorage` missing `directory`/`prefix`, or a directory that does not exist or is not readable-writable. |
| `WriteException` | The storage backend failed to persist data (e.g. `FileStorage` could not write the file). |

```php
use OpenErpByJsonRpc\Storage\FileStorage;
use OpenErpByJsonRpc\Storage\Exception\OptionException;

try {
    $storage = new FileStorage(['prefix' => 'odoo_']); // missing "directory"
} catch (OptionException $e) {
    // misconfigured storage
}
```

## Recommended patterns

**Granular handling** — react differently to client misuse vs. server/transport
errors:

```php
use OpenErpByJsonRpc\Exception\ClientException;
use OpenErpByJsonRpc\Exception\JsonException;

try {
    $records = $model->search('sale.order', $criteria, ['id', 'name']);
} catch (ClientException $e) {
    // a bug in our own query — fix the criteria
} catch (JsonException $e) {
    // the server or network failed — retry / log / alert
}
```

**Authentication check** — `reconnectOrLogin()` does not throw on a failed
login; it returns `false`. Test the return value:

```php
if (false === $odoo->reconnectOrLogin(null)) {
    throw new \RuntimeException('Odoo authentication failed.');
}
```

## Quick reference

| Throwing area | Exception(s) |
|---------------|--------------|
| `Model::search()` with invalid criteria | `ClientException` |
| `Model::readOne()` with several matches | `NotSingleException` |
| Any failed JSON-RPC call / DB operation | `JsonException` |
| Authentication failure (`reconnectOrLogin`) | returns `false` (no throw) |
| Misconfigured storage | `OptionException` |
| Failed storage write | `WriteException` |
