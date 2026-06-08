# Working with records

The `Model` client (`OpenErpByJsonRpc\Client\Model`) is the workhorse of the
library. It wraps Odoo's ORM methods so you can read, search, create, update and
delete records on any model (`res.partner`, `res.users`, `sale.order`, …).

Create it from a connected `OpenERP` instance:

```php
use OpenErpByJsonRpc\Client\Model;

$model = new Model($odoo); // $odoo is an authenticated OpenERP object
```

All methods may throw an `OpenErpByJsonRpc\Exception\JsonException` when the
server rejects the request. See [Error handling](error-handling.md).

## Reading records

### `readOne()` — fetch a single record

```php
public function readOne(string $model, int|array $id, array $fields = []): ?array
```

Returns the matching record as an associative array, or `null` when nothing
matches. If more than one record matches, a
`OpenErpByJsonRpc\Exception\NotSingleException` is thrown.

```php
$user = $model->readOne('res.users', 2, ['id', 'login', 'name']);
// ['id' => 2, 'login' => 'admin', 'name' => 'Mitchell Admin']

$missing = $model->readOne('res.users', 999999, ['id']);
// null
```

### `read()` — fetch one or several records

```php
public function read(string $model, int|array $ids, array $fields = []): array
```

Always returns a list (possibly empty) of records. Accepts a single id or an
array of ids.

```php
$users = $model->read('res.users', [2, 3], ['id', 'login']);
// [ ['id' => 2, 'login' => 'admin'], ['id' => 3, 'login' => 'demo'] ]

$none = $model->read('res.users', 0, ['id']);
// []
```

> **Choosing the field list.** The `$fields` argument is the list of column names
> to return. Passing an empty array lets the server decide which fields to send
> back; passing an explicit list keeps payloads small and predictable.

## Searching records

### `search()` — query with a domain

```php
public function search(
    string $model,
    array|Criteria $criteria = [],
    array $fields = [],
    int $offset = 0,
    ?int $limit = null,
    string $sort = ''
): array
```

Returns a list of records matching the criteria. The criteria can be either a
raw Odoo domain array or a [`Criteria`](building-queries.md) object.

| Parameter  | Description |
|------------|-------------|
| `$model`   | The model name, e.g. `'res.partner'`. |
| `$criteria`| A raw domain `array`, or a `Criteria` instance. |
| `$fields`  | Field names to return. |
| `$offset`  | Number of leading records to skip (for pagination). |
| `$limit`   | Maximum number of records to return (`null` = no limit). |
| `$sort`    | Sort clause, e.g. `'name asc'` or `'create_date desc'`. |

```php
use OpenErpByJsonRpc\Criteria;

// Raw domain array.
$admins = $model->search(
    'res.users',
    [['login', '=', 'admin']],
    ['id', 'login']
);

// Criteria object (recommended for anything beyond a single condition).
$companies = $model->search(
    'res.partner',
    (new Criteria())->equal('is_company', true),
    ['id', 'name'],
    0,            // offset
    20,           // limit
    'name asc'    // sort
);
```

Passing an invalid criteria (for example `null`) raises a
`OpenErpByJsonRpc\Exception\ClientException`.

See [Building queries](building-queries.md) for the full `Criteria` API and the
raw domain syntax.

## Creating records

### `create()` — insert a record

```php
public function create(string $model, array $datas): int
```

Returns the id of the newly created record.

```php
$id = $model->create('res.partner', [
    'name'     => 'Jean Dupont',
    'function' => 'CTO',
    'phone'    => '+33 1 02 03 04 05',
    'email'    => 'jean.dupont@example.com',
]);
// e.g. 42
```

## Updating records

### `write()` — update a record

```php
public function write(string $model, int $id, array $datas): bool
```

Updates the given record and returns `true` on success.

```php
$model->write('res.partner', $id, [
    'function' => 'CEO',
    'phone'    => '+33 1 99 99 99 99',
]);
// true
```

> **Note:** Updating uses `write()`, not `create()`. (Some older README snippets
> incorrectly reused `create()` for updates and deletes — always use `write()`
> to update and `remove()` to delete.)

## Deleting records

### `remove()` — delete a record

```php
public function remove(string $model, int $id): bool
```

Deletes the record and returns `true` on success.

```php
$model->remove('res.partner', $id);
// true
```

## A full CRUD round-trip

```php
use OpenErpByJsonRpc\Client\Model;

$model = new Model($odoo);

// Create
$id = $model->create('res.partner', ['name' => 'Acme Corp', 'is_company' => true]);

// Read
$partner = $model->readOne('res.partner', $id, ['name', 'is_company']);

// Update
$model->write('res.partner', $id, ['name' => 'Acme Corporation']);

// Search
$companies = $model->search('res.partner', (new \OpenErpByJsonRpc\Criteria())->equal('is_company', true), ['id', 'name']);

// Delete
$model->remove('res.partner', $id);
```

## Method summary

| Method | Returns | Description |
|--------|---------|-------------|
| `readOne($model, $id, $fields = [])` | `?array` | One record or `null`; throws `NotSingleException` if several match. |
| `read($model, $ids, $fields = [])` | `array` | A list of records (possibly empty). |
| `search($model, $criteria = [], $fields = [], $offset = 0, $limit = null, $sort = '')` | `array` | A list of matching records. |
| `create($model, $datas)` | `int` | The new record id. |
| `write($model, $id, $datas)` | `bool` | `true` on success. |
| `remove($model, $id)` | `bool` | `true` on success. |
