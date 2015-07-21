<?php

/**
 * Created by PhpStorm.
 * User: leviathan
 * Date: 20/07/15
 * Time: 04:10
 */
class FileStorageTest extends PHPUnit_Framework_TestCase
{
    static private $directory = __DIR__.'/../cache';

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->removeDirectory();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->removeDirectory();
    }

    /**
     * Remove the cache directory
     */
    protected function removeDirectory()
    {
        if (is_dir(self::$directory) === false) {
            return;
        }

        $files = glob(self::$directory.'/*');
        foreach ($files as $file) {
            unlink($file);
        }

        rmdir(self::$directory);
    }

    /**
     * @return \OpenErpByJsonRpc\Storage\FileStorage
     */
    protected function getStorage()
    {
        mkdir(static::$directory, 0755);

        return new \OpenErpByJsonRpc\Storage\FileStorage([
            'directory' => self::$directory,
            'prefix' => 'test_',
        ]);
    }

    public function testWriteSuccess()
    {
        $storage = $this->getStorage();
        $this->assertEquals($storage, $storage->write('test', 'my-data'));
        $this->assertEquals(json_encode('my-data'), file_get_contents(self::$directory.'/test_test'));
    }

    /**
     * @expectedException \OpenErpByJsonRpc\Storage\Exception\WriteException
     * @expectedExceptionMessage Impossible to write test
     */
    public function testWriteFailBecauseFileIsInReadonly()
    {
        $storage = $this->getStorage();

        touch(self::$directory.'/test_test');
        chmod(self::$directory.'/test_test', 0444);

        $storage->write('test', 'my-data');
    }

    public function testReadSuccess()
    {
        $storage = $this->getStorage();
        $storage->write('test', ['key' => 'value']);
        $this->assertEquals(['key' => 'value'], $storage->read('test'));
    }

    public function testReadAKeydoesntExist()
    {
        $storage = $this->getStorage();
        $this->assertEquals(null, $storage->read('test'));
    }

    public function testReadFailBecauseFileIsNotReadable()
    {
        $storage = $this->getStorage();
        $storage->write('test', ['key' => 'value']);

        chmod(self::$directory.'/test_test', 0000);
        $this->assertEquals(null, $storage->read('test'));
    }

    /**
     * @expectedException \OpenErpByJsonRpc\Storage\Exception\OptionException
     * @expectedExceptionMessage directory must be defined
     */
    public function testFailIfDirectoryIsNotDefined()
    {
        new \OpenErpByJsonRpc\Storage\FileStorage([
            'prefix' => 'test_',
        ]);
    }

    /**
     * @expectedException \OpenErpByJsonRpc\Storage\Exception\OptionException
     * @expectedExceptionMessage prefix must be defined
     */
    public function testFailIfPrefixIsNotDefined()
    {
        mkdir(static::$directory, 0755);

        new \OpenErpByJsonRpc\Storage\FileStorage([
            'directory' => self::$directory,
        ]);
    }

    /**
     * @expectedException \OpenErpByJsonRpc\Storage\Exception\OptionException
     * @expectedExceptionMessageRegExp /.+ must exists/
     */
    public function testFailIfDirectoryDoesntExist()
    {
        new \OpenErpByJsonRpc\Storage\FileStorage([
            'directory' => self::$directory,
            'prefix' => 'test_',
        ]);
    }

    /**
     * @expectedException \OpenErpByJsonRpc\Storage\Exception\OptionException
     * @expectedExceptionMessageRegExp /.+ must be readable and writable/
     */
    public function testFailIfDirectoryIsNotWritable()
    {
        mkdir(static::$directory, 0555);

        new \OpenErpByJsonRpc\Storage\FileStorage([
            'directory' => self::$directory,
            'prefix' => 'test_',
        ]);
    }
}
