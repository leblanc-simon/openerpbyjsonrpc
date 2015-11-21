<?php
/**
 * This file is part of the OpenErpByJsonRpc package.
 *
 * (c) Simon Leblanc <contact@leblanc-simon.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OpenErpByJsonRpc\JsonRpc;

interface JsonRpcInterface
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
