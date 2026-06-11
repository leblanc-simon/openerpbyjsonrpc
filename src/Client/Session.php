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

use OpenErpByJsonRpc\Exception\JsonException;

class Session extends AClient implements ClientInterface
{
    /**
     * @var string the base of URL for all session action
     */
    private const PATH = 'session/:method';

    /**
     * Return the session information.
     *
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    public function getInfos(): array
    {
        if (false === $this->openerpJsonrpc->isLogged()) {
            // Odoo 15+ raises a "Session Expired" error when get_session_info is
            // called without an active session: there is no information to return.
            return ['uid' => null];
        }

        $infos = $this->openerpJsonrpc->call($this->getPath('get_session_info'));

        // Since Odoo 15 the session identifier is no longer part of the response
        // (the session is tracked through the cookie). We expose it so callers
        // can store it and reconnect later.
        if (false === isset($infos['session_id'])) {
            $infos['session_id'] = $this->openerpJsonrpc->getSessionId();
        }

        return $infos;
    }

    /**
     * Change current password.
     *
     * @return array<string, string>
     *
     * @throws JsonException
     */
    public function changePassword(string $oldPassword, string $newPassword): array
    {
        // Odoo 15+ removed the /web/session/change_password controller; the
        // password of the current user is now changed through res.users.
        $this->openerpJsonrpc->callBase('res.users', 'change_password', [$oldPassword, $newPassword]);

        return ['new_password' => $newPassword];
    }

    /**
     * Return the list of available language.
     *
     * @return array<mixed>
     *
     * @throws JsonException
     */
    public function getLangList(): array
    {
        return $this->openerpJsonrpc->callWithoutCredential($this->getPath('get_lang_list'));
    }

    /**
     * Return the list of available modules.
     *
     * @return array<mixed>
     *
     * @throws JsonException
     */
    public function getModules(): array
    {
        return $this->openerpJsonrpc->call($this->getPath('modules'));
    }

    /**
     * Return the path for a method.
     */
    private function getPath(string $method): string
    {
        return \str_replace(':method', $method, self::PATH);
    }
}
