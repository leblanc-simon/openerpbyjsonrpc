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

use Zend\Http\Client as HttpClient;
use Zend\Http\Header\SetCookie;
use Zend\Json\Server\Client;

class ZendJsonRpc
    extends AJsonRpc
    implements JsonRpcInterface
{
    /**
     * @param            $url
     * @param            $method
     * @param array      $params
     * @param null       $session_id
     * @param bool|false $long_call
     * @return mixed
     */
    public function call($url, $method, $params = [], $session_id = null, $long_call = false)
    {
        if (true === $long_call) {
            $this->setTimeout(30 * 60 * 60); // 30 minutes
        }

        $this->getClient($url);
        $return_value = $this->client->call($method, $params);
        $this->cookie = $this->client->getHttpClient()->getResponse()->getCookie();

        // Restore timeout
        $this->setTimeout();

        return $return_value;
    }

    protected function getClient($url)
    {
        $http_client = new HttpClient(null, ['timeout' => $this->timeout]);
        $http_client->setHeaders(['User-Agent' => 'OpenErpByJsonRpc by ZendJsonRpc']);

        if (null !== $this->cookie) {
            $http_client->addCookie($this->cookie);
        }

        $this->client = new Client($url, $http_client);

        return $this->client;
    }

    public function setCookie($name, $value = null, $expire = null, $path = null, $domain = null, $secure = false,
                              $httponly = true, $max_age = null, $version = null)
    {
        $this->cookie = new SetCookie($name, $value, $expire, $path, $domain, $secure, $httponly, $max_age, $version);

        return $this;
    }

    /**
     * @return null|array
     */
    public function getCookie()
    {
        if (is_array($this->cookie) || $this->cookie instanceof \ArrayIterator) {
            $cookie = reset($this->cookie);
        } elseif ($this->cookie instanceof SetCookie) {
            $cookie = $this->cookie;
        } else {
            return null;
        }

        return [
            'name' => $cookie->getName(),
            'value' => $cookie->getValue(),
            'expire' => $cookie->getExpires(),
            'path' => $cookie->getPath(),
            'domain' => $cookie->getDomain(),
            'secure' => $cookie->isSecure(),
            'httponly' => $cookie->isHttponly(),
            'max_age' => $cookie->getMaxAge(),
            'version' => $cookie->getVersion(),
        ];
    }
}
