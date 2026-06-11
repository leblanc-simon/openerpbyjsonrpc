# OpenErpByJsonRpc — LLM reference

Condensed, single-file reference for AI assistants. It is self-contained:
signatures, return types, error behaviour, and idioms are all here. Human-facing
guides live in the sibling files (`getting-started.md`, `working-with-records.md`,
etc.).

## What this library is

PHP client for Odoo / OpenERP over the JSON-RPC web API. Namespace root:
`OpenErpByJsonRpc\` (PSR-4). Requires PHP `>= 8.5` and `ext-json`. Supports both
Odoo &lt; 15 and Odoo 15+ (it branches internally on the server version).

## Mental model (3 layers)

1. `JsonRpc\ZendJsonRpc` — transport (raw HTTP JSON-RPC). Implements `JsonRpc\JsonRpcInterface`.
2. `Storage\StorageInterface` — session persistence (`FileStorage` or `NullStorage`).
3. `JsonRpc\OpenERP` — high-level client; holds connection config + auth; is passed to feature clients.

Feature clients built on `OpenERP`: `Client\Model`, `Client\Session`,
`Client\Database`. Query helper: `Criteria`.

## Canonical setup

```php
use OpenErpByJsonRpc\JsonRpc\{ZendJsonRpc, OpenERP};
use OpenErpByJsonRpc\Storage\FileStorage;

$odoo = new OpenERP(
    new ZendJsonRpc('https://host'),
    new FileStorage(['directory' => '/existing/writable/dir', 'prefix' => 'odoo_'])
);
$odoo->setBaseUri('https://host')->setPort(null)
     ->setUsername('admin')->setPassword('admin')->setDatabase('main')
     ->reconnectOrLogin(null); // returns bool; does NOT throw on bad credentials
```

## `JsonRpc\OpenERP`

- `__construct(JsonRpcInterface $jsonRpc, StorageInterface $storage)`
- Fluent setters (return `self`): `setBaseUri(string)`, `setPort(?int)`,
  `setUsername(string)`, `setPassword(string)`, `setDatabase(string)`.
- `reconnectOrLogin(?string $sessionId): bool` — `null` ⇒ fresh login; a session
  id ⇒ restore from storage, fall back to login if invalid. Returns success
  (no exception on failed auth).
- `isLogged(): bool`, `getSessionId(): ?string`, `isOdoo15OrMore(): bool`,
  `prepareLongCall(): void`.
- Low-level (return `mixed`): `call(string $path, array $params = [])`,
  `callWithoutCredential(string $path, array $params = [])`,
  `httpCallWithoutCredential(string $path, array $params = [])`,
  `callBase(string $model, string $method, array $args = [], ?array $kwargs = null)`.

## `Client\Model` — `new Model($odoo)`

| Method | Signature | Return | Notes |
|--------|-----------|--------|-------|
| readOne | `readOne(string $model, int\|int[] $id, string[] $fields = [])` | `?array` | `null` if none; throws `NotSingleException` if &gt;1. |
| read | `read(string $model, int\|int[] $ids, string[] $fields = [])` | `array` | list (possibly empty). |
| search | `search(string $model, array\|Criteria $criteria = [], string[] $fields = [], int $offset = 0, ?int $limit = null, string $sort = '')` | `array` | invalid criteria ⇒ `ClientException`. |
| create | `create(string $model, array $datas)` | `int` | new id. |
| write | `write(string $model, int $id, array $datas)` | `bool` | **update** (not `create`). |
| remove | `remove(string $model, int $id)` | `bool` | **delete** (not `create`). |

Common mistake to avoid: updating/deleting via `create()`. Use `write()` /
`remove()`.

```php
$id  = $model->create('res.partner', ['name' => 'Acme', 'is_company' => true]);
$rec = $model->readOne('res.partner', $id, ['name']);
$model->write('res.partner', $id, ['name' => 'Acme Inc']);
$model->remove('res.partner', $id);
```

## `Criteria` — fluent Odoo domain builder

- Factory: `Criteria::create()` or `new Criteria()`.
- Builder methods (each returns `self`, conditions AND-combined in order):
  `equal`, `notEqual`, `lessThan`, `lessEqual`, `greaterThan`, `greaterEqual`,
  `like`, `ilike`, `in`, `notIn` — all `($field, $value)`.
- Generic: `add(string $field, mixed $value, string $compare = Criteria::EQUAL)`.
- `get(): array` returns the raw domain (`[[field, op, value], ...]`).
- Constants: `EQUAL '='`, `NOT_EQUAL '!='`, `LESS_THAN '<'`, `LESS_EQUAL '<='`,
  `GREATER_THAN '>'`, `GREATER_EQUAL '>='`, `LIKE 'like'`, `ILIKE 'ilike'`,
  `IN 'in'`, `NOT_IN 'not in'`.
- Only AND is supported by the builder. For OR / negation, pass a raw domain
  array (polish notation `'|'`, `'!'`) directly to `search()`.

```php
$c = (new Criteria())->equal('is_company', true)->ilike('name', 'acme');
$model->search('res.partner', $c, ['id', 'name'], 0, 20, 'name asc');
```

## `Client\Session` — `new Session($odoo)`

- `getInfos(): array` — incl. `uid` and `session_id`. Not-logged-in ⇒ `['uid' => null]`.
  `session_id` is the value to persist and pass back to `reconnectOrLogin()`.
- `getLangList(): array` — `[[code, label], ...]`.
- `getModules(): array` — module technical names.
- `changePassword(string $old, string $new): array` — returns `['new_password' => $new]`.

## `Client\Database` — `new Database($odoo)` (master-password guarded; auto long-call)

- `getList(): array` — `string[]`.
- `create(string $password, string $name, bool $demo, string $language, string $adminPassword, string $login = 'admin'): bool`.
- `duplicate(string $password, string $sourceName, string $name): bool`.
- `drop(string $password, string $name): bool`.
- `$password` is the server **master password** (`admin_passwd`). Failure ⇒ `JsonException`.

## `Storage`

- Interface: `__construct(array $options = [])`, `read(string $key): mixed`
  (`null` if absent), `write(string $key, mixed $data): self`.
- `FileStorage(['directory' => ..., 'prefix' => ...])`: both options required;
  directory must exist + be readable-writable. Throws
  `Storage\Exception\OptionException` (bad options) / `WriteException` (write fails).
- `NullStorage`: no-op; `read` ⇒ `null`. Use for one-off scripts/tests; cannot
  reconnect by id.

## Exceptions

- Core base: `OpenErpByJsonRpc\Exception` (abstract, extends `\Exception`).
  Children: `ClientException` (client misuse, e.g. bad criteria), `JsonException`
  (request/server failure), `LoginException`, `SessionException`.
- `Exception\NotSingleException` extends `\Exception` directly (NOT the base) —
  thrown by `Model::readOne()` on multiple matches.
- Storage: `Storage\Exception\StorageException` (extends `\Exception`) ⇒
  `OptionException`, `WriteException`.
- `reconnectOrLogin()` returns `false` on auth failure rather than throwing.

## Gotchas

- `write()` updates, `remove()` deletes — never `create()` for those.
- `reconnectOrLogin()` returns `bool`; check it, it won't throw on bad creds.
- `setPort(null)` uses the scheme default port.
- `FileStorage` directory must pre-exist; the library does not create it.
- `readOne()` throws on multiple matches — use `read()` when several rows are
  expected.
- `Criteria` is AND-only; drop to raw domains for OR/negation.
