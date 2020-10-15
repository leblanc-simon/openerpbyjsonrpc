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

namespace OpenErpByJsonRpc\Storage;

interface StorageInterface
{
    /**
     * @param array $options Option to initialize the storage
     *
     * @throws Exception\OptionException
     */
    public function __construct(array $options = []);

    /**
     * Read a key in the storage.
     *
     * @return mixed
     */
    public function read(string $key);

    /**
     * Write data into storage.
     *
     * @param mixed $data
     *
     * @return $this
     *
     * @throws Exception\WriteException
     */
    public function write(string $key, $data): self;
}
