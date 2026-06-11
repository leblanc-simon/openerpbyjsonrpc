OpenErpByJsonRpc
================

Communicate with Odoo via JSON-RPC

Installation
------------

```bash
composer require leblanc-simon/openerpbyjsonrpc
```

The library depends on a fork of `laminas/laminas-json-server`. Declare the
matching VCS repository in your project's `composer.json` before installing:

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

Requirements: PHP `>= 8.5` and the `ext-json` extension.

Usage
-----

```php
use OpenErpByJsonRpc\JsonRpc\ZendJsonRpc;
use OpenErpByJsonRpc\JsonRpc\OpenERP;
use OpenErpByJsonRpc\Storage\FileStorage;
use OpenErpByJsonRpc\Client\Session;
use OpenErpByJsonRpc\Client\Database;
use OpenErpByJsonRpc\Client\Model;
use OpenErpByJsonRpc\Criteria;

$jsonrpc = new ZendJsonRpc('http://odoo.com');
$odoo = new OpenERP($jsonrpc, new FileStorage([
    'directory' => 'path/to/cache',
    'prefix' => 'session_',
]));

$odoo
    ->setBaseUri('http://odoo.com')
    ->setPort(null)
    ->setUsername('admin')
    ->setPassword('admin')
    ->setDatabase('main')
    ->reconnectOrLogin(null)
;

// If you use FileStorage, you can reconnect without username and password
// $sessionId can be retrieve via Session::getInfos()
$odoo->reconnectOrLogin($sessionId);


///////////////////////////////
// Model methods
//
$model = new Model($odoo);

// Read a single record
$model->readOne('res.users', 1, ['id', 'login']);

// Search records
// * use array for criteria
$model->search('res.users', [['login', '=', 'admin']], ['id', 'login']);

// * use Criteria class : search login = admin AND name = admin
$criteria = new Criteria();
$criteria
    ->equal('login', 'admin')
    ->equal('name', 'admin')
;
$model->search('res.users', $criteria, ['id', 'login']);

// Create record
$id = $model->create('res.partner', [
    'name' => 'Jean Dupont',
    'function' => 'DRH',
    'phone' => '+330120304050',
]);

// Update record
$model->write('res.partner', $id, [
    'name' => 'Jean Dupond',
]);

// Delete record
$model->remove('res.partner', $id);


///////////////////////////////
// Session methods
//
$session = new Session($odoo);

// Get the session informations
$session->getInfos();

// Get the availables languages
$session->getLangList();

// Get the availables modules
$session->getModules();

// Change your current password
$session->changePassword('admin', 'new pass');


///////////////////////////////
// Database methods
//
$database = new Database($odoo);

// Get the list of available database
$database->getList();

// Create a new database
$database->create(
    'master password', // your master password in your odoo config file
    'database_name',
    false, // true if you want add demo data
    'fr_FR',
    'admin' // the admin password for the created database
);

// Duplicate a database
$database->duplicate(
    'master password', // your master password in your odoo config file
    'database_to_duplicate',
    'new_database'
);

// Drop a database
$database->drop(
    'master password', // your master password in your odoo config file
    'database_name'
);

```

Documentation
-------------

Full documentation is available in the [`docs/`](docs/README.md) directory:

- [Getting started](docs/getting-started.md)
- [Connection & authentication](docs/connection-and-authentication.md)
- [Working with records](docs/working-with-records.md)
- [Building queries](docs/building-queries.md)
- [Sessions](docs/sessions.md)
- [Databases](docs/databases.md)
- [Session storage](docs/session-storage.md)
- [Error handling](docs/error-handling.md)
- [API reference](docs/api-reference.md)
- [Development](docs/development.md)

A condensed reference for AI assistants is available in [`docs/llms.md`](docs/llms.md).

Development
-----------

Quality tooling for contributors:

- **Coding style** — [PHP CS Fixer](https://cs.symfony.com/) with the `@Symfony`
  rule set: `composer cs-fix` (PHP CS Fixer is installed under
  `tools/php-cs-fixer/`).
- **Static analysis** — [PHPStan](https://phpstan.org/) at level 8:
  `vendor/bin/phpstan analyse`.
- **Automated refactoring** — [Rector](https://getrector.com/):
  `vendor/bin/rector process`.
- **Tests** — [PHPUnit](https://phpunit.de/): `vendor/bin/phpunit`.

See [Development](docs/development.md) for the full guide.

License
-------

[MIT](http://opensource.org/licenses/MIT)

Author
------

Simon Leblanc <contact@leblanc-simon.eu>
