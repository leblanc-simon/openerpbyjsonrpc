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

class Session extends AClient implements ClientInterface
{
    /**
     * @var string the base of URL for all session action
     */
    private const PATH = 'session/:method';

    /**
     * Return the session information.
     *
     * @throws \OpenErpByJsonRpc\Exception\JsonException
     */
    public function getInfos(): array
    {
        if (true === $this->openerp_jsonrpc->isLogged()) {
            return $this->openerp_jsonrpc->call(self::getPath('get_session_info'));
        }

        return $this->openerp_jsonrpc->callWithoutCredential(self::getPath('get_session_info'));
    }

    /**
     * Change current password.
     *
     * @throws \OpenErpByJsonRpc\Exception\JsonException
     */
    public function changePassword(string $old_password, string $new_password): array
    {
        return $this->openerp_jsonrpc->call(self::getPath('change_password'), [
            'fields' => [
                ['name' => 'old_pwd', 'value' => $old_password],
                ['name' => 'new_password', 'value' => $new_password],
                ['name' => 'confirm_pwd', 'value' => $new_password],
            ],
        ]);
    }

    /**
     * Return the list of available language.
     *
     * @throws \OpenErpByJsonRpc\Exception\JsonException
     */
    public function getLangList(): array
    {
        return $this->openerp_jsonrpc->callWithoutCredential(self::getPath('get_lang_list'));
    }

    /**
     * Return the list of available modules.
     *
     * @throws \OpenErpByJsonRpc\Exception\JsonException
     */
    public function getModules(): array
    {
        return $this->openerp_jsonrpc->call(self::getPath('modules'));
    }

    /**
     * Return the path for a method.
     */
    private static function getPath(string $method): string
    {
        return \str_replace(':method', $method, self::PATH);
    }
}
