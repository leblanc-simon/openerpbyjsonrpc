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

use OpenErpByJsonRpc\Storage\Exception\OptionException;
use OpenErpByJsonRpc\Storage\Exception\WriteException;

class FileStorage implements StorageInterface
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param array $options
     * @throws OptionException
     */
    public function __construct(array $options = [])
    {
        if (isset($options['directory']) === false) {
            throw new OptionException('directory must be defined');
        }
        $this->setDirectory($options['directory']);

        if (isset($options['prefix']) === false) {
            throw new OptionException('prefix must be defined');
        }
        $this->prefix = $options['prefix'];
    }

    /**
     * Read a key in the storage
     * @param string $key
     * @return mixed
     */
    public function read($key)
    {
        $filename = $this->directory.'/'.$this->prefix.$key;
        if (false === is_file($filename) || false === is_readable($filename)) {
            return null;
        }

        $content = file_get_contents($filename);
        if (false === $content) {
            return null;
        }

        return json_decode($content, true);
    }

    /**
     * Write data into storage
     *
     * @param string $key
     * @param mixed  $data
     * @return $this
     * @throws WriteException
     */
    public function write($key, $data)
    {
        $content = json_encode($data);
        if (false === @file_put_contents($this->directory.'/'.$this->prefix.$key, $content)) {
            throw new WriteException(sprintf('Impossible to write %s', $key));
        }

        return $this;
    }

    /**
     * @param string $directory
     * @throws Exception\OptionException
     */
    private function setDirectory($directory)
    {
        $real_directory = realpath($directory);
        if (is_dir($real_directory) === false) {
            throw new OptionException(sprintf('%s must exists', $directory));
        }

        if (is_readable($real_directory) === false || is_writable($real_directory) === false) {
            throw new OptionException(sprintf('%s must be readable and writable', $directory));
        }

        $this->directory = $real_directory;
    }
}
