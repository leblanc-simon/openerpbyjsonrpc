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
     * @param array<mixed> $params
     */
    public function call(
        string $url,
        string $method,
        array $params = [],
        ?string $sessionId = null,
        bool $longCall = false,
    ): mixed {
        if ($longCall) {
            $this->setTimeout(30 * 60 * 60); // 30 minutes
        }

        $this->getJsonClient($url);
        $returnValue = $this->client->call($method, $params);

        $this->captureCookie($this->client->getHttpClient()->getResponse()->getCookie());

        // Restore timeout
        $this->setTimeout();

        return $returnValue;
    }

    /**
     * @param array<mixed> $params
     */
    public function callHttp(
        string $url,
        string $method,
        array $params = [],
        ?string $sessionId = null,
        bool $longCall = false,
    ): mixed {
        if ($longCall) {
            $this->setTimeout(30 * 60 * 60); // 30 minutes
        }

        $client = $this->getHttpClient($url);
        $client->setUri($url);
        $client->setMethod($method);
        // Do not follow redirects: the Odoo database manager answers with a
        // redirect on success and with a rendered HTML page (HTTP 200) on error.
        $client->setOptions(['maxredirects' => 0]);

        if ([] !== $params) {
            $client->setParameterPost($params);
        }

        $response = $client->send();

        $this->captureCookie($response->getCookie());

        // Restore timeout
        $this->setTimeout();

        return $response;
    }

    protected function getJsonClient(string $url): mixed
    {
        $httpClient = new HttpClient(null, ['timeout' => $this->timeout]);
        $httpClient->setHeaders(['User-Agent' => 'OpenErpByJsonRpc by LaminasHttpClient']);

        $this->applyCookie($httpClient);

        $this->client = new Client($url, $httpClient);

        return $this->client;
    }

    protected function getHttpClient(string $url): mixed
    {
        $httpClient = new HttpClient(null, ['timeout' => $this->timeout]);
        $httpClient->setHeaders(['User-Agent' => 'OpenErpByJsonRpc by LaminasHttpClient']);

        $this->applyCookie($httpClient);

        return $httpClient;
    }

    /**
     * Store the cookie(s) returned by a response, ignoring empty results.
     *
     * Laminas returns false when the response has no Set-Cookie header (e.g. the
     * redirect answered by the Odoo database manager): in that case the previous
     * cookie must be kept.
     */
    private function captureCookie(mixed $cookie): void
    {
        if ($cookie instanceof SetCookie || $cookie instanceof \ArrayIterator) {
            $this->cookie = $cookie;

            return;
        }

        if (\is_array($cookie) && [] !== $cookie) {
            $this->cookie = $cookie;
        }
    }

    /**
     * Attach the stored cookie(s) to a HTTP client, if any valid cookie is held.
     */
    private function applyCookie(HttpClient $httpClient): void
    {
        if (
            $this->cookie instanceof SetCookie
            || $this->cookie instanceof \ArrayIterator
            || (\is_array($this->cookie) && [] !== $this->cookie)
        ) {
            $httpClient->addCookie($this->cookie);
        }
    }

    /**
     * @param int|string|\DateTime|null $expire
     */
    public function setCookie(
        ?string $name,
        ?string $value = null,
        $expire = null,
        ?string $path = null,
        ?string $domain = null,
        bool $secure = false,
        bool $httponly = true,
        ?int $maxAge = null,
        ?int $version = null,
    ): JsonRpcInterface {
        $this->cookie = new SetCookie($name, $value, $expire, $path, $domain, $secure, $httponly, $maxAge, $version);

        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
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
            'maxAge' => $cookie->getMaxAge(),
            'version' => $cookie->getVersion(),
        ];
    }
}
