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

use Laminas\Http\Client as HttpClient;
use Laminas\Http\Header\SetCookie;
use Laminas\Json\Server\Client;

class ZendJsonRpc extends AJsonRpc implements JsonRpcInterface
{
    /**
     * @return mixed
     */
    public function call(
        string $url,
        string $method,
        array $params = [],
        ? string $session_id = null,
        bool $long_call = false
    ) {
        if (true === $long_call) {
            $this->setTimeout(30 * 60 * 60); // 30 minutes
        }

        $this->getClient($url);
        $return_value = $this->client->call($method, $params);

        /* @var SetCookie[] cookie */
        $this->cookie = $this->client->getHttpClient()->getResponse()->getCookie();

        // Restore timeout
        $this->setTimeout();

        return $return_value;
    }

    protected function getClient(string $url)
    {
        $http_client = new HttpClient(null, ['timeout' => $this->timeout]);
        $http_client->setHeaders(['User-Agent' => 'OpenErpByJsonRpc by ZendJsonRpc']);

        if (null !== $this->cookie) {
            $http_client->addCookie($this->cookie);
        }

        $this->client = new Client($url, $http_client);

        return $this->client;
    }

    /**
     * @param int|string|\DateTime|null $expire
     */
    public function setCookie(
        ? string $name,
        ? string $value = null,
        $expire = null,
        ? string $path = null,
        ? string $domain = null,
        bool $secure = false,
        bool $httponly = true,
        ? int $max_age = null,
        ? int $version = null
    ): JsonRpcInterface {
        $this->cookie = new SetCookie($name, $value, $expire, $path, $domain, $secure, $httponly, $max_age, $version);

        return $this;
    }

    public function getCookie(): ?array
    {
        if (false === ($this->cookie instanceof \ArrayIterator) && false === ($this->cookie instanceof SetCookie)) {
            return null;
        }

        $cookie = null;

        if ($this->cookie instanceof \ArrayIterator) {
            $this->cookie->seek(0);
            $cookie = $this->cookie->current();
        }

        if ($this->cookie instanceof SetCookie) {
            $cookie = $this->cookie;
        }

        if (null === $cookie) {
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
