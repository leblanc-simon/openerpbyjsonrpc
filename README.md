OpenErpByJsonRpc
================

Communicate with Odoo via JSON-RPC

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/8e2d665f-a756-4bb7-ad81-1850b0d5a1d0/mini.png)](https://insight.sensiolabs.com/projects/8e2d665f-a756-4bb7-ad81-1850b0d5a1d0)

Installation
------------

```bash
composer require leblanc-simon/openerpbyjsonrpc
```

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
// $session_id can be retrieve via Session::getInfos()
$odoo->reconnectOrLogin($session_id);


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
$model->create('res.partner', $id, [
    'name' => 'Jean Dupond',
]);

// Delete record
$model->create('res.partner', $id);


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

License
-------

[MIT](http://opensource.org/licenses/MIT)

Author
------

Simon Leblanc <contact@leblanc-simon.eu>