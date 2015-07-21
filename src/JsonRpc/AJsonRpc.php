<?php

namespace OpenErpByJsonRpc\JsonRpc;

abstract class AJsonRpc
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
     * @var mixed
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
    public function __construct($uri = null, $username = null, $password = null)
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
     * @param string $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->session_id;
    }

    /**
     * @return mixed
     */
    public function getCookie()
    {
        return $this->cookie;
    }

    /**
     * @param int $timeout
     * @return $this
     */
    public function setTimeout($timeout = 10)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * @param $url
     * @return mixed
     */
    abstract protected function getClient($url);
}
