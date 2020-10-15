<?php

use OpenErpByJsonRpc\Storage\Exception\OptionException;
use OpenErpByJsonRpc\Storage\Exception\WriteException;
use OpenErpByJsonRpc\Storage\FileStorage;
use PHPUnit\Framework\TestCase;

class FileStorageTest extends TestCase
{
    /**
     * @var string
     */
    static private $directory = __DIR__.'/../cache';

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->removeDirectory();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        $this->removeDirectory();
    }

    /**
     * Remove the cache directory
     */
    protected function removeDirectory(): void
    {
        if (is_dir(self::$directory) === false) {
            return;
        }

        $files = glob(self::$directory.'/*');
        foreach ($files as $file) { // @phpstan-ignore-line
            unlink($file);
        }

        rmdir(self::$directory);
    }

    /**
     * @return FileStorage
     */
    protected function getStorage(): FileStorage
    {
        mkdir(static::$directory, 0755);

        return new FileStorage([
            'directory' => self::$directory,
            'prefix' => 'test_',
        ]);
    }

    public function testWriteSuccess(): void
    {
        $storage = $this->getStorage();
        self::assertEquals($storage, $storage->write('test', 'my-data'));
        self::assertEquals(json_encode('my-data'), file_get_contents(self::$directory.'/test_test'));
    }

    public function testWriteFailBecauseFileIsInReadonly(): void
    {
        $this->expectExceptionMessage("Impossible to write test");
        $this->expectException(WriteException::class);
        $storage = $this->getStorage();

        touch(self::$directory.'/test_test');
        chmod(self::$directory.'/test_test', 0444);

        $storage->write('test', 'my-data');
    }

    public function testReadSuccess(): void
    {
        $storage = $this->getStorage();
        $storage->write('test', ['key' => 'value']);
        self::assertEquals(['key' => 'value'], $storage->read('test'));
    }

    public function testReadAKeydoesntExist(): void
    {
        $storage = $this->getStorage();
        self::assertEquals(null, $storage->read('test'));
    }

    public function testReadFailBecauseFileIsNotReadable(): void
    {
        $storage = $this->getStorage();
        $storage->write('test', ['key' => 'value']);

        chmod(self::$directory.'/test_test', 0000);
        self::assertEquals(null, $storage->read('test'));
    }

    public function testFailIfDirectoryIsNotDefined(): void
    {
        $this->expectExceptionMessage("directory must be defined");
        $this->expectException(OptionException::class);
        new FileStorage([
            'prefix' => 'test_',
        ]);
    }

    public function testFailIfPrefixIsNotDefined(): void
    {
        $this->expectExceptionMessage("prefix must be defined");
        $this->expectException(OptionException::class);
        mkdir(static::$directory, 0755);

        new FileStorage([
            'directory' => self::$directory,
        ]);
    }

    public function testFailIfDirectoryDoesntExist(): void
    {
        $this->expectException(OptionException::class);
        $this->expectExceptionMessageMatches("/.+ must exists/");
        new FileStorage([
            'directory' => self::$directory,
            'prefix' => 'test_',
        ]);
    }

    public function testFailIfDirectoryIsNotWritable(): void
    {
        $this->expectExceptionMessageMatches("/.+ must be readable and writable/");
        $this->expectException(OptionException::class);
        mkdir(static::$directory, 0555);

        new FileStorage([
            'directory' => self::$directory,
            'prefix' => 'test_',
        ]);
    }
}
