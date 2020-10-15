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
    /**
     * @var string
     */
    protected $uri;

    /**
     * @var string
     */
    protected $port;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $session_id;

    /**
     * @var mixed|Client
     */
    protected $client;

    /**
     * @var mixed
     */
    protected $cookie;

    /**
     * @var int
     */
    protected $timeout = 10;

    /**
     * @param string $uri
     * @param string $username
     * @param string $password
     */
    public function __construct(? string $uri = null, ? string $username = null, ? string $password = null)
    {
        if (null !== $uri) {
            $this->setUri($uri);
        }

        if (null !== $username) {
            $this->setUsername($username);
        }

        if (null !== $password) {
            $this->setPassword($password);
        }
    }

    /**
     * @return $this
     */
    public function setUri(string $uri): JsonRpcInterface
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @return $this
     */
    public function setUsername(string $username): JsonRpcInterface
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return $this
     */
    public function setPassword(string $password): JsonRpcInterface
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId(): ? string
    {
        return $this->session_id;
    }

    public function getCookie(): ? array
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

    /**
     * @return mixed
     */
    abstract protected function getClient(string $url);
}
