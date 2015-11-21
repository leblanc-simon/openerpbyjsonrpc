<?php
/**
 * This file is part of the OpenErpByJsonRpc package.
 *
 * (c) Simon Leblanc <contact@leblanc-simon.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OpenErpByJsonRpc\Storage;

class NullStorage implements StorageInterface
{
    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
    }

    /**
     * Read a key in the storage
     * @param string $key
     * @return mixed
     */
    public function read($key)
    {
        return null;
    }

    /**
     * Write data into storage
     *
     * @param string $key
     * @param mixed  $data
     * @return $this
     */
    public function write($key, $data)
    {
        return $this;
    }
}
