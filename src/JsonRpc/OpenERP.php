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

namespace OpenErpByJsonRpc\JsonRpc;

use OpenErpByJsonRpc\Exception\JsonException;
use OpenErpByJsonRpc\Exception\SessionException;
use OpenErpByJsonRpc\Storage\StorageInterface;

class OpenERP
{
    const BASE_PATH = '/web/';

    /**
     * @var JsonRpcInterface
     */
    private $json_rpc;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var string
     */
    private $base_uri;

    /**
     * @var int|null
     */
    private $port;

    /**
     * @var string
     */
    private $database;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string|null
     */
    private $session_id;

    /**
     * @var array|null
     */
    private $context;

    /**
     * @var bool
     */
    private $long_call = false;

    public function __construct(JsonRpcInterface $json_rpc, StorageInterface $storage)
    {
        $this->json_rpc = $json_rpc;
        $this->storage = $storage;
    }

    /**
     * @return mixed
     *
     * @throws JsonException
     */
    public function call(string $path, array $params = [])
    {
        if (null === $this->session_id) {
            if (false === $this->login()) {
                throw new JsonException('Impossible to login');
            }
        }

        $params = \array_merge(['context' => $this->context], $params);

        $result = $this->json_rpc->call($this->getUri($path), 'call', $params, $this->session_id, $this->long_call);
        $this->long_call = false;

        return $result;
    }

    /**
     * @return mixed
     *
     * @throws JsonException
     */
    public function callWithoutCredential(string $path, array $params = [])
    {
        $result = $this->json_rpc->call($this->getUri($path), 'call', $params, null, $this->long_call);
        $this->long_call = false;

        return $result;
    }

    /**
     * @return mixed
     *
     * @throws JsonException
     */
    public function callBase(string $model, string $method, array $args = [], ? array $kwargs = null)
    {
        if (null === $this->session_id) {
            if (false === $this->login()) {
                throw new JsonException('Impossible to login');
            }
        }

        $result = $this->json_rpc->call($this->getUri('dataset/call_kw'), 'call', [
            'model' => $model,
            'method' => $method,
            'args' => $args,
            'kwargs' => $kwargs ?: new \stdClass(),
        ], $this->session_id, $this->long_call);

        $this->long_call = false;

        return $result;
    }

    public function prepareLongCall(): void
    {
        $this->long_call = true;
    }

    public function isLogged(): bool
    {
        return null !== $this->session_id;
    }

    /**
     * Set the URI of the OpenERP server.
     *
     * @param string $base_uri The base URI of the OpenERP server
     */
    public function setBaseUri(string $base_uri): self
    {
        $this->base_uri = $base_uri;

        return $this;
    }

    /**
     * Set the port of the OpenERP server.
     *
     * @param int|null $port The port of the OpenERP server (require if it's not a standard port)
     */
    public function setPort(? int $port): self
    {
        if (null !== $port) {
            $port = (int) $port;
        }

        $this->port = $port;

        return $this;
    }

    /**
     * Set the database name.
     *
     * @param string $database The database name to use
     */
    public function setDatabase(string $database): self
    {
        $this->database = $database;

        return $this;
    }

    /**
     * Set the username to connect into the OpenERP server.
     *
     * @param string $username the username to connect into the OpenERP server
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set the password to connect into the OpenERP server.
     *
     * @param string $password the password to connect into the OpenERP server
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function reconnectOrLogin(? string $session_id): bool
    {
        try {
            if (null === $session_id) {
                throw new SessionException();
            }

            $datas = $this->storage->read($session_id);
            if (null === $datas || false === \is_array($datas)) {
                throw new SessionException();
            }

            $this->session_id = $datas['session_id'];
            $this->context = $datas['user_context'];
            \call_user_func_array([$this->json_rpc, 'setCookie'], $datas['cookie']);

            $response = $this->call('session/get_session_info');

            if (false === \is_array($response)) {
                throw new JsonException('response must be an array');
            }

            $required_datas = ['username', 'user_context', 'uid', 'session_id', 'db', 'company_id'];
            foreach ($required_datas as $data) {
                if (false === isset($response[$data])) {
                    throw new JsonException(\sprintf('%s is not in the response', $data));
                }
            }

            $this->session_id = $response['session_id'];
            $this->context = $response['user_context'];
            $this->database = $response['db'];
            $this->username = $response['username'];

            return true;
        } catch (\Exception $e) {
            $this->session_id = null;
            $this->context = null;
        }

        return $this->login();
    }

    /**
     * Login in the OpenERP server.
     *
     * @return bool True if the login is OK, false else
     */
    private function login(): bool
    {
        try {
            $response = $this->callWithoutCredential('session/authenticate', [
                'base_location' => $this->base_uri,
                'db' => $this->database,
                'login' => $this->username,
                'password' => $this->password,
            ]);

            if (false === \is_array($response)) {
                throw new JsonException('response must be an array');
            }

            if (false === isset($response['user_context'])) {
                throw new JsonException('context is not in the response');
            }

            if (false === isset($response['session_id'])) {
                throw new JsonException('session_id is not in the response');
            }

            $cookie = $this->json_rpc->getCookie();

            $this->context = $response['user_context'];
            $this->session_id = $cookie['value'] ?? null;

            $datas = [
                'user_context' => $this->context,
                'session_id' => $this->session_id,
                'cookie' => $cookie,
            ];

            $this->storage->write($this->session_id, $datas);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Build the complete URI.
     *
     * @param string $path The path to call
     *
     * @return string The complete URI to call
     *
     * @throws JsonException If the base_uri is not define
     */
    private function getUri(string $path): string
    {
        if (null === $this->base_uri) {
            throw new JsonException('base_uri must be define');
        }

        $base_uri = $this->base_uri;

        if (null !== $this->port) {
            $base_uri .= ':'.$this->port;
        }

        return $base_uri.self::BASE_PATH.$path;
    }
}
