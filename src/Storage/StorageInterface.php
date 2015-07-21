<?php

namespace OpenErpByJsonRpc\Storage;

interface StorageInterface
{
    /**
     * @param array $options Option to initialize the storage
     * @throws Exception\OptionException
     */
    public function __construct(array $options = []);

    /**
     * Read a key in the storage
     * @param string $key
     * @return mixed
     */
    public function read($key);

    /**
     * Write data into storage
     * @param string $key
     * @param mixed $data
     * @return $this
     * @throws Exception\WriteException
     */
    public function write($key, $data);
}
