<?php

class NullStorageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \OpenErpByJsonRpc\Storage\NullStorage
     */
    private $storage;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->storage = new \OpenErpByJsonRpc\Storage\NullStorage([]);
    }

    public function testWrite()
    {
        $this->assertEquals($this->storage, $this->storage->write('test', 'my-data'));
    }

    public function testRead()
    {
        $this->assertNull($this->storage->read('test'));
    }
}
