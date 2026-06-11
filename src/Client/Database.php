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

use Laminas\Http\Response;
use OpenErpByJsonRpc\Exception\JsonException;

class Database extends AClient implements ClientInterface
{
    /**
     * @var string the base of URL for all database action
     */
    private const PATH = 'database/:method';

    /**
     * Return the available database name.
     *
     * @return string[]
     */
    public function getList(): array
    {
        if ($this->openerpJsonrpc->isOdoo15OrMore()) {
            // Odoo 15+ exposes the list through the JSON controller /web/database/list.
            return $this->openerpJsonrpc->callWithoutCredential('database/list');
        }

        return $this->openerpJsonrpc->callWithoutCredential($this->getPath('get_list'));
    }

    /**
     * Create a new database.
     *
     * @param string $password      admin password (master password in the config file)
     * @param string $name          the name of the new database
     * @param bool   $demo          populate with demo data or not
     * @param string $language      language to use
     * @param string $adminPassword admin password to initialize
     * @param string $login         login of the administrator account to create
     */
    public function create(
        string $password,
        string $name,
        bool $demo,
        string $language,
        string $adminPassword,
        string $login = 'admin',
    ): bool {
        $this->openerpJsonrpc->prepareLongCall();

        if ($this->openerpJsonrpc->isOdoo15OrMore()) {
            $params = [
                'master_pwd' => $password,
                'name' => $name,
                'lang' => $language,
                'password' => $adminPassword,
                'login' => $login,
                'phone' => '',
                'country_code' => '',
            ];

            // The "demo" field is read as a checkbox: any non-empty value is
            // truthy, so it must only be sent when demo data is requested.
            if ($demo) {
                $params['demo'] = 'on';
            }

            return $this->handleManagerResponse(
                $this->openerpJsonrpc->httpCallWithoutCredential($this->getPath('create'), $params),
                'creation'
            );
        }

        return $this->openerpJsonrpc->callWithoutCredential($this->getPath('create'), [
            'fields' => [
                ['name' => 'super_admin_pwd', 'value' => $password],
                ['name' => 'db_name', 'value' => $name],
                ['name' => 'demo_data', 'value' => $demo],
                ['name' => 'db_lang', 'value' => $language],
                ['name' => 'create_admin_pwd', 'value' => $adminPassword],
            ],
        ]);
    }

    /**
     * Duplicate a database.
     *
     * @param string $password   admin password (master password in the config file)
     * @param string $sourceName the source database
     * @param string $name       the destination database
     */
    public function duplicate(string $password, string $sourceName, string $name): bool
    {
        $this->openerpJsonrpc->prepareLongCall();

        if ($this->openerpJsonrpc->isOdoo15OrMore()) {
            return $this->handleManagerResponse(
                $this->openerpJsonrpc->httpCallWithoutCredential($this->getPath('duplicate'), [
                    'master_pwd' => $password,
                    'name' => $sourceName,
                    'new_name' => $name,
                ]),
                'duplication'
            );
        }

        return $this->openerpJsonrpc->callWithoutCredential($this->getPath('duplicate'), [
            'fields' => [
                ['name' => 'super_admin_pwd', 'value' => $password],
                ['name' => 'db_original_name', 'value' => $sourceName],
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
        $this->openerpJsonrpc->prepareLongCall();

        if ($this->openerpJsonrpc->isOdoo15OrMore()) {
            return $this->handleManagerResponse(
                $this->openerpJsonrpc->httpCallWithoutCredential($this->getPath('drop'), [
                    'master_pwd' => $password,
                    'name' => $name,
                ]),
                'deletion'
            );
        }

        return $this->openerpJsonrpc->callWithoutCredential($this->getPath('drop'), [
            'fields' => [
                ['name' => 'drop_pwd', 'value' => $password],
                ['name' => 'drop_db', 'value' => $name],
            ],
        ]);
    }

    /**
     * Interpret the response of the Odoo 15+ HTTP database manager.
     *
     * On success the manager answers with a redirect; on failure it renders an
     * HTML page (HTTP 200) containing the error message.
     *
     * @param Response $response
     *
     * @throws JsonException when the operation failed
     */
    private function handleManagerResponse(mixed $response, string $action): bool
    {
        if (true === $response->isRedirect()) {
            return true;
        }

        $body = (string) $response->getBody();

        if (1 === \preg_match('#Database\s+\w+\s+error:[^<\n]*#i', $body, $matches)) {
            throw new JsonException(\trim($matches[0]));
        }

        throw new JsonException(\sprintf('Database %s failed', $action));
    }

    /**
     * Return the path for a method.
     */
    private function getPath(string $method): string
    {
        return \str_replace(':method', $method, self::PATH);
    }
}
