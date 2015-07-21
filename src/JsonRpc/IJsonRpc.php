<?php

namespace OpenErpByJsonRpc\JsonRpc;

interface IJsonRpc
{
    public function __construct($uri = null, $username = null, $password = null);

    public function setUri($uri);

    public function setUsername($username);

    public function setPassword($password);

    public function getSessionId();

    public function getCookie();

    public function setCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = false,
                              $httponly = true, $max_age = null, $version = null);

    public function call($url, $method, $params = [], $session_id = null, $long_call = false);
}
