<?php

declare(strict_types=1);
/**
 * This file is part of the OpenErpByJsonRpc package.
 *
 * (c) Simon Leblanc <contact@leblanc-simon.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OpenErpByJsonRpc\Client;

class Database extends AClient implements ClientInterface
{
    /**
     * @var string the base of URL for all database action
     */
    private const PATH = 'database/:method';

    /**
     * Return the available database name.
     */
    public function getList(): array
    {
        return $this->openerp_jsonrpc->callWithoutCredential(self::getPath('get_list'));
    }

    /**
     * Create a new database.
     *
     * @param string $password       admin password (master password in the config file)
     * @param string $name           the name of the new database
     * @param bool   $demo           populate with demo data or not
     * @param string $language       language to use
     * @param string $admin_password admin password to initialize
     */
    public function create(string $password, string $name, bool $demo, string $language, string $admin_password): bool
    {
        $this->openerp_jsonrpc->prepareLongCall();

        return $this->openerp_jsonrpc->callWithoutCredential(self::getPath('create'), [
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
     * Duplicate a database.
     *
     * @param string $password    admin password (master password in the config file)
     * @param string $source_name the source database
     * @param string $name        the destination database
     */
    public function duplicate(string $password, string $source_name, string $name): bool
    {
        $this->openerp_jsonrpc->prepareLongCall();

        return $this->openerp_jsonrpc->callWithoutCredential(self::getPath('duplicate'), [
            'fields' => [
                ['name' => 'super_admin_pwd', 'value' => $password],
                ['name' => 'db_original_name', 'value' => $source_name],
                ['name' => 'db_name', 'value' => $name],
            ],
        ]);
    }

    /**
     * Drop a database.
     *
     * @param string $password admin password (master password in the config file)
     * @param string $name     the database to drop
     */
    public function drop(string $password, string $name): bool
    {
        $this->openerp_jsonrpc->prepareLongCall();

        return $this->openerp_jsonrpc->callWithoutCredential(self::getPath('drop'), [
            'fields' => [
                ['name' => 'drop_pwd', 'value' => $password],
                ['name' => 'drop_db', 'value' => $name],
            ],
        ]);
    }

    /**
     * Return the path for a method.
     */
    private static function getPath(string $method): string
    {
        return \str_replace(':method', $method, self::PATH);
    }
}
