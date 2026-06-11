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
    public const BASE_PATH = '/web/';

    private JsonRpcInterface $jsonRpc;

    private StorageInterface $storage;

    private ?string $baseUri = null;

    private ?int $port = null;

    /**
     * @var string
     */
    private $database;

    /**
     * @var string
     */
    private $username;

    private ?string $password = null;

    /**
     * @var string|null
     */
    private $sessionId;

    /**
     * @var array<string, mixed>|null
     */
    private $context;

    private ?bool $isOdoo15OrMore = null;

    private bool $longCall = false;

    public function __construct(JsonRpcInterface $jsonRpc, StorageInterface $storage)
    {
        $this->jsonRpc = $jsonRpc;
        $this->storage = $storage;
    }

    /**
     * @param array<mixed> $params
     *
     * @throws JsonException
     */
    public function call(string $path, array $params = []): mixed
    {
        if (null === $this->sessionId && false === $this->login()) {
            throw new JsonException('Impossible to login');
        }

        $params = \array_merge(['context' => $this->context], $params);

        $result = $this->jsonRpc->call($this->getUri($path), 'call', $params, $this->sessionId, $this->longCall);
        $this->longCall = false;

        return $result;
    }

    /**
     * @param array<mixed> $params
     *
     * @throws JsonException
     */
    public function callWithoutCredential(string $path, array $params = []): mixed
    {
        $result = $this->jsonRpc->call($this->getUri($path), 'call', $params, null, $this->longCall);
        $this->longCall = false;

        return $result;
    }

    /**
     * @param array<mixed> $params
     *
     * @throws JsonException
     */
    public function httpCallWithoutCredential(string $path, array $params = []): mixed
    {
        $result = $this->jsonRpc->callHttp($this->getUri($path), 'POST', $params, null, $this->longCall);
        $this->longCall = false;

        return $result;
    }

    /**
     * @param array<mixed>              $args
     * @param array<string, mixed>|null $kwargs
     *
     * @throws JsonException
     */
    public function callBase(string $model, string $method, array $args = [], ?array $kwargs = null): mixed
    {
        if (null === $this->sessionId && false === $this->login()) {
            throw new JsonException('Impossible to login');
        }

        $result = $this->jsonRpc->call($this->getUri('dataset/call_kw'), 'call', [
            'model' => $model,
            'method' => $method,
            'args' => $args,
            'kwargs' => $kwargs ?: new \stdClass(),
        ], $this->sessionId, $this->longCall);

        $this->longCall = false;

        return $result;
    }

    public function prepareLongCall(): void
    {
        $this->longCall = true;
    }

    public function isLogged(): bool
    {
        return null !== $this->sessionId;
    }

    /**
     * Return the current session identifier (the OpenERP session cookie value).
     */
    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    /**
     * Set the URI of the OpenERP server.
     *
     * @param string $baseUri The base URI of the OpenERP server
     */
    public function setBaseUri(string $baseUri): self
    {
        $this->baseUri = $baseUri;

        return $this;
    }

    /**
     * Set the port of the OpenERP server.
     *
     * @param int|null $port The port of the OpenERP server (require if it's not a standard port)
     */
    public function setPort(?int $port): self
    {
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

    public function reconnectOrLogin(?string $sessionId): bool
    {
        try {
            if (null === $sessionId) {
                throw new SessionException();
            }

            $datas = $this->storage->read($sessionId);
            if (null === $datas || false === \is_array($datas)) {
                throw new SessionException();
            }

            $this->sessionId = $datas['session_id'];
            $this->context = $datas['user_context'];
            \call_user_func_array([$this->jsonRpc, 'setCookie'], $datas['cookie']);

            $response = $this->call('session/get_session_info');

            if (false === \is_array($response)) {
                throw new JsonException('response must be an array');
            }

            // Since Odoo 15 the session is tracked through the cookie: the
            // response of get_session_info no longer carries "session_id" nor
            // "company_id", so we keep the session_id restored from the storage.
            $requiredDatas = ['username', 'user_context', 'uid', 'db'];
            foreach ($requiredDatas as $data) {
                if (false === isset($response[$data])) {
                    throw new JsonException(\sprintf('%s is not in the response', $data));
                }
            }

            $this->context = $response['user_context'];
            $this->database = $response['db'];
            $this->username = $response['username'];

            return true;
        } catch (\Exception $e) {
            $this->sessionId = null;
            $this->context = null;
        }

        return $this->login();
    }

    public function isOdoo15OrMore(): bool
    {
        if (null === $this->isOdoo15OrMore) {
            $versionInfo = $this->callWithoutCredential('webclient/version_info');

            $serverVersion = (\is_array($versionInfo) && isset($versionInfo['server_version']))
                ? (string) $versionInfo['server_version']
                : '0';

            $this->isOdoo15OrMore = \version_compare(
                (string) \preg_replace('#[^0-9\.]#', '', $serverVersion),
                '15.0'
            ) >= 0;
        }

        return $this->isOdoo15OrMore;
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
                'base_location' => $this->baseUri,
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

            if (
                isset($response['server_version'])
                && version_compare(
                    preg_replace('#[^0-9\.]#', '', $response['server_version']),
                    '15.0'
                ) >= 0
            ) {
                $this->isOdoo15OrMore = true;
                $response['session_id'] = \password_hash(\json_encode($response['user_context'], JSON_THROW_ON_ERROR), \PASSWORD_DEFAULT);
            }

            if (false === isset($response['session_id'])) {
                throw new JsonException('session_id is not in the response');
            }

            $cookie = $this->jsonRpc->getCookie();

            $this->context = $response['user_context'];
            $this->sessionId = $cookie['value'] ?? null;

            $datas = [
                'user_context' => $this->context,
                'session_id' => $this->sessionId,
                'cookie' => $cookie,
            ];

            $this->storage->write($this->sessionId, $datas);

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
        if (null === $this->baseUri) {
            throw new JsonException('base_uri must be define');
        }

        $baseUri = $this->baseUri;

        if (null !== $this->port) {
            $baseUri .= ':'.$this->port;
        }

        return $baseUri.self::BASE_PATH.$path;
    }
}
