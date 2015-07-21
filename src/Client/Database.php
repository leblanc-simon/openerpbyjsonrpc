<?php

namespace OpenErpByJsonRpc\Client;

class Database
    extends AClient
    implements IClient
{
    /**
     * @var string the base of URL for all database action
     */
    private $path = 'database/:method';

    /**
     * Return the available database name
     *
     * @return array
     */
    public function getList()
    {
        return $this->openerp_jsonrpc->callWithoutCredential($this->getPath('get_list'));
    }

    /**
     * Create a new database
     *
     * @param string $password       admin password (master password in the config file)
     * @param string $name           the name of the new database
     * @param bool   $demo           populate with demo data or not
     * @param string $language       language to use
     * @param string $admin_password admin password to initialize
     * @return bool
     */
    public function create($password, $name, $demo, $language, $admin_password)
    {
        $this->openerp_jsonrpc->prepareLongCall();

        return $this->openerp_jsonrpc->callWithoutCredential($this->getPath('create'), [
            'fields' => [
                ['name' => 'super_admin_pwd', 'value' => $password],
                ['name' => 'db_name', 'value' => $name],
                ['name' => 'demo_data', 'value' => $demo],
                ['name' => 'db_lang', 'value' => $language],
                ['name' => 'create_admin_pwd', 'value' => $admin_password],
            ],
        ]);
    }

    /**
     * Duplicate a database
     *
     * @param string $password    admin password (master password in the config file)
     * @param string $source_name the source database
     * @param string $name        the destination database
     * @return bool
     */
    public function duplicate($password, $source_name, $name)
    {
        $this->openerp_jsonrpc->prepareLongCall();

        return $this->openerp_jsonrpc->callWithoutCredential($this->getPath('duplicate'), [
            'fields' => [
                ['name' => 'super_admin_pwd', 'value' => $password],
                ['name' => 'db_original_name', 'value' => $source_name],
                ['name' => 'db_name', 'value' => $name],
            ],
        ]);
    }

    /**
     * Drop a database
     *
     * @param string $password admin password (master password in the config file)
     * @param string $name     the database to drop
     * @return bool
     */
    public function drop($password, $name)
    {
        $this->openerp_jsonrpc->prepareLongCall();

        return $this->openerp_jsonrpc->callWithoutCredential($this->getPath('drop'), [
            'fields' => [
                ['name' => 'drop_pwd', 'value' => $password],
                ['name' => 'drop_db', 'value' => $name],
            ],
        ]);
    }

    /**
     * Return the path for a method
     *
     * @param string $method
     * @return string
     */
    private function getPath($method)
    {
        return str_replace(':method', $method, $this->path);
    }
}
