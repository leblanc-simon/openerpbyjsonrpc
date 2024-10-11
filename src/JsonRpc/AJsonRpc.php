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

use Laminas\Json\Server\Client;

abstract class AJsonRpc implements JsonRpcInterface
{
    protected string $port;

    protected string $session_id;

    /**
     * @var mixed|Client
     */
    protected mixed $client;

    protected mixed $cookie = null;

    protected int $timeout = 10;

    public function __construct(
        protected ?string $uri = null,
        protected ?string $username = null,
        protected ?string $password = null,
    ) {
    }

    public function setUri(string $uri): JsonRpcInterface
    {
        $this->uri = $uri;

        return $this;
    }

    public function setUsername(string $username): JsonRpcInterface
    {
        $this->username = $username;

        return $this;
    }

    public function setPassword(string $password): JsonRpcInterface
    {
        $this->password = $password;

        return $this;
    }

    public function getSessionId(): ?string
    {
        return $this->session_id;
    }

    public function getCookie(): ?array
    {
        return $this->cookie;
    }

    /**
     * @return $this
     */
    public function setTimeout(int $timeout = 10): JsonRpcInterface
    {
        $this->timeout = $timeout;

        return $this;
    }

    abstract protected function getJsonClient(string $url): mixed;

    abstract protected function getHttpClient(string $url): mixed;
}
