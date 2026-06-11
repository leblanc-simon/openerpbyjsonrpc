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

use OpenErpByJsonRpc\Storage\Exception\OptionException;
use OpenErpByJsonRpc\Storage\Exception\WriteException;

class FileStorage implements StorageInterface
{
    private string $directory;

    private string $prefix;

    /**
     * @param array<string, mixed> $options
     *
     * @throws OptionException
     */
    public function __construct(array $options = [])
    {
        if (false === isset($options['directory'])) {
            throw new OptionException('directory must be defined');
        }
        $this->setDirectory($options['directory']);

        if (false === isset($options['prefix'])) {
            throw new OptionException('prefix must be defined');
        }
        $this->prefix = $options['prefix'];
    }

    /**
     * Read a key in the storage.
     */
    public function read(string $key): mixed
    {
        $filename = $this->directory.'/'.$this->prefix.$key;
        if (false === \is_file($filename) || false === \is_readable($filename)) {
            return null;
        }

        $content = \file_get_contents($filename);
        if (false === $content) {
            return null;
        }

        return \json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Write data into storage.
     *
     * @throws WriteException
     */
    public function write(string $key, mixed $data): StorageInterface
    {
        $content = \json_encode($data);
        if (false === @\file_put_contents($this->directory.'/'.$this->prefix.$key, $content)) {
            throw new WriteException(\sprintf('Impossible to write %s', $key));
        }

        return $this;
    }

    /**
     * @throws OptionException
     */
    private function setDirectory(string $directory): void
    {
        $realDirectory = \realpath($directory);
        if (false === $realDirectory || false === \is_dir($realDirectory)) {
            throw new OptionException(\sprintf('%s must exists', $directory));
        }

        if (false === \is_readable($realDirectory) || false === \is_writable($realDirectory)) {
            throw new OptionException(\sprintf('%s must be readable and writable', $directory));
        }

        $this->directory = $realDirectory;
    }
}
