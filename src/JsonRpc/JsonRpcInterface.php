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

interface JsonRpcInterface
{
    public function __construct(?string $uri = null, ?string $username = null, ?string $password = null);

    public function setUri(string $uri): self;

    public function setUsername(string $username): self;

    public function setPassword(string $password): self;

    public function getSessionId(): ?string;

    /**
     * @return array<string, mixed>|null
     */
    public function getCookie(): ?array;

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
    ): self;

    /**
     * @param array<mixed> $params
     */
    public function call(
        string $url,
        string $method,
        array $params = [],
        ?string $sessionId = null,
        bool $longCall = false,
    ): mixed;

    /**
     * @param array<mixed> $params
     */
    public function callHttp(
        string $url,
        string $method,
        array $params = [],
        ?string $sessionId = null,
        bool $longCall = false,
    ): mixed;
}
