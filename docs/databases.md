# Databases

The `Database` client (`OpenErpByJsonRpc\Client\Database`) wraps the Odoo
database-manager endpoints: listing, creating, duplicating and dropping
databases. These are administrative operations protected by the server's
**master password** (the `admin_passwd` value from the Odoo configuration file).

Create it from an `OpenERP` instance:

```php
use OpenErpByJsonRpc\Client\Database;

$database = new Database($odoo);
```

> **Authentication note.** Database management endpoints are not tied to a user
> session — they are guarded by the master password. You still build the
> `Database` client from an `OpenERP` object (for the base URI and transport),
> but listing/creating/dropping does not require a logged-in user.

> **Long-running operations.** Creating, duplicating and dropping a database can
> take a while. The client automatically raises the request timeout for these
> calls, so you don't have to.

## `getList()` — list databases

```php
public function getList(): array
```

Returns the names of the databases available on the server.

```php
$names = $database->getList();
// ['main', 'staging', 'demo']
```

## `create()` — create a database

```php
public function create(
    string $password,
    string $name,
    bool $demo,
    string $language,
    string $adminPassword,
    string $login = 'admin'
): bool
```

Creates a new database and returns `true` on success.

| Parameter | Description |
|-----------|-------------|
| `$password` | The server **master password**. |
| `$name` | Name of the database to create. |
| `$demo` | `true` to load Odoo's demonstration data, `false` for an empty database. |
| `$language` | Default language code, e.g. `'fr_FR'`. |
| `$adminPassword` | Password for the administrator account of the new database. |
| `$login` | Login of the administrator account to create (defaults to `'admin'`). |

```php
$database->create(
    'my-master-password', // master password from the Odoo config file
    'new_database',
    false,                 // no demo data
    'en_US',
    'admin-password',      // password of the new admin user
    'admin'                // admin login
);
```

If the operation fails (for example, a wrong master password), a
`OpenErpByJsonRpc\Exception\JsonException` is thrown with the server's error
message — on Odoo 15+ the manager reports a wrong master password as an
`Access Denied` error page.

## `duplicate()` — copy a database

```php
public function duplicate(string $password, string $sourceName, string $name): bool
```

Creates a copy of an existing database and returns `true` on success.

```php
$database->duplicate(
    'my-master-password',
    'main',          // source database
    'main_copy'      // destination database
);
```

## `drop()` — delete a database

```php
public function drop(string $password, string $name): bool
```

Permanently deletes a database and returns `true` on success.

```php
$database->drop('my-master-password', 'main_copy');
```

> ⚠️ Dropping a database is irreversible. Double-check the name and keep backups.

## Method summary

| Method | Returns | Description |
|--------|---------|-------------|
| `getList()` | `array` (`string[]`) | Names of available databases. |
| `create($password, $name, $demo, $language, $adminPassword, $login = 'admin')` | `bool` | Create a database. |
| `duplicate($password, $sourceName, $name)` | `bool` | Copy a database. |
| `drop($password, $name)` | `bool` | Delete a database. |
