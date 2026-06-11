# API reference

A concise, exhaustive list of the public classes and methods. For narrative
explanations and examples, follow the links to the topic guides.

All classes live under the `OpenErpByJsonRpc\` namespace (PSR-4).

---

## `JsonRpc\OpenERP`

The high-level client. Owns the connection settings, authentication, and the
low-level calls. See [Connection & authentication](connection-and-authentication.md).

```php
__construct(JsonRpc\JsonRpcInterface $jsonRpc, Storage\StorageInterface $storage)
```

**Connection setters** (fluent, return `self`):

| Method | Signature |
|--------|-----------|
| `setBaseUri` | `setBaseUri(string $baseUri): self` |
| `setPort` | `setPort(?int $port): self` |
| `setUsername` | `setUsername(string $username): self` |
| `setPassword` | `setPassword(string $password): self` |
| `setDatabase` | `setDatabase(string $database): self` |

**Authentication & state:**

| Method | Signature | Notes |
|--------|-----------|-------|
| `reconnectOrLogin` | `reconnectOrLogin(?string $sessionId): bool` | `null` ⇒ fresh login; an id ⇒ restore session, fall back to login. Returns success. |
| `isLogged` | `isLogged(): bool` | Whether a session is open. |
| `getSessionId` | `getSessionId(): ?string` | Current session identifier. |
| `isOdoo15OrMore` | `isOdoo15OrMore(): bool` | Server version branch helper. |
| `prepareLongCall` | `prepareLongCall(): void` | Raises the timeout for the next call. |

**Low-level calls** (return `mixed`):

| Method | Signature |
|--------|-----------|
| `call` | `call(string $path, array $params = [])` |
| `callWithoutCredential` | `callWithoutCredential(string $path, array $params = [])` |
| `httpCallWithoutCredential` | `httpCallWithoutCredential(string $path, array $params = [])` |
| `callBase` | `callBase(string $model, string $method, array $args = [], ?array $kwargs = null)` |

---

## `Client\Model`

CRUD and search over Odoo models. See [Working with records](working-with-records.md).

```php
__construct(JsonRpc\OpenERP $jsonRpc)
```

| Method | Signature | Returns |
|--------|-----------|---------|
| `readOne` | `readOne(string $model, int\|array $id, array $fields = [])` | `?array` — one record or `null`; throws `NotSingleException` if several match. |
| `read` | `read(string $model, int\|array $ids, array $fields = [])` | `array` — list of records. |
| `search` | `search(string $model, array\|Criteria $criteria = [], array $fields = [], int $offset = 0, ?int $limit = null, string $sort = '')` | `array` — list of records. |
| `create` | `create(string $model, array $datas)` | `int` — new record id. |
| `write` | `write(string $model, int $id, array $datas)` | `bool` |
| `remove` | `remove(string $model, int $id)` | `bool` |

---

## `Client\Session`

Session-level endpoints. See [Sessions](sessions.md).

```php
__construct(JsonRpc\OpenERP $jsonRpc)
```

| Method | Signature | Returns |
|--------|-----------|---------|
| `getInfos` | `getInfos(): array` | Session details, incl. `uid` and `session_id`. |
| `getLangList` | `getLangList(): array` | Installed languages as `[code, label]` pairs. |
| `getModules` | `getModules(): array` | Module technical names. |
| `changePassword` | `changePassword(string $oldPassword, string $newPassword): array` | `['new_password' => $newPassword]`. |

---

## `Client\Database`

Database administration (guarded by the master password). See [Databases](databases.md).

```php
__construct(JsonRpc\OpenERP $jsonRpc)
```

| Method | Signature | Returns |
|--------|-----------|---------|
| `getList` | `getList(): array` | `string[]` of database names. |
| `create` | `create(string $password, string $name, bool $demo, string $language, string $adminPassword, string $login = 'admin'): bool` | `true` on success. |
| `duplicate` | `duplicate(string $password, string $sourceName, string $name): bool` | `true` on success. |
| `drop` | `drop(string $password, string $name): bool` | `true` on success. |

---

## `Criteria`

Fluent domain builder. See [Building queries](building-queries.md). Every builder
method returns `self`.

```php
static create(): Criteria
add(string $field, mixed $value, string $compare = self::EQUAL): self
get(): array   // the raw domain array
```

| Builder method | Operator |
|----------------|----------|
| `equal($field, $value)` | `=` |
| `notEqual($field, $value)` | `!=` |
| `lessThan($field, $value)` | `<` |
| `lessEqual($field, $value)` | `<=` |
| `greaterThan($field, $value)` | `>` |
| `greaterEqual($field, $value)` | `>=` |
| `like($field, $value)` | `like` |
| `ilike($field, $value)` | `ilike` |
| `in($field, $value)` | `in` |
| `notIn($field, $value)` | `not in` |

**Constants:** `EQUAL`, `NOT_EQUAL`, `LESS_THAN`, `LESS_EQUAL`, `GREATER_THAN`,
`GREATER_EQUAL`, `LIKE`, `ILIKE`, `IN`, `NOT_IN`.

---

## `Storage\StorageInterface`

Session persistence contract. See [Session storage](session-storage.md).

```php
__construct(array $options = [])
read(string $key): mixed
write(string $key, mixed $data): self
```

### `Storage\FileStorage`

File-based persistence. Options: `directory` (required, must exist and be
readable-writable), `prefix` (required). Throws `OptionException` /
`WriteException`.

### `Storage\NullStorage`

No-op backend: `read()` returns `null`, `write()` does nothing.

---

## `JsonRpc\JsonRpcInterface` / `JsonRpc\ZendJsonRpc`

The transport layer. `ZendJsonRpc` is the bundled implementation.

```php
// ZendJsonRpc (via the abstract AJsonRpc base)
__construct(?string $uri = null, ?string $username = null, ?string $password = null)

setUri(string $uri): self
setUsername(string $username): self
setPassword(string $password): self
setTimeout(int $timeout = 10): self
getSessionId(): ?string
getCookie(): ?array
setCookie(?string $name, ?string $value = null, $expire = null, ?string $path = null, ?string $domain = null, bool $secure = false, bool $httponly = true, ?int $maxAge = null, ?int $version = null): self
call(string $url, string $method, array $params = [], ?string $sessionId = null, bool $longCall = false): mixed
callHttp(string $url, string $method, array $params = [], ?string $sessionId = null, bool $longCall = false): mixed
```

Most applications never touch the transport directly — `OpenERP` drives it.

---

## Exceptions

See [Error handling](error-handling.md).

| Class | Extends |
|-------|---------|
| `Exception` (abstract) | `\Exception` |
| `Exception\ClientException` | `Exception` |
| `Exception\JsonException` | `Exception` |
| `Exception\LoginException` | `Exception` |
| `Exception\SessionException` | `Exception` |
| `Exception\NotSingleException` | `\Exception` |
| `Storage\Exception\StorageException` | `\Exception` |
| `Storage\Exception\OptionException` | `Storage\Exception\StorageException` |
| `Storage\Exception\WriteException` | `Storage\Exception\StorageException` |
