<?php

use OpenErpByJsonRpc\Storage\NullStorage;
use PHPUnit\Framework\TestCase;

class NullStorageTest extends TestCase
{
    /**
     * @var NullStorage
     */
    private $storage;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->storage = new NullStorage([]);
    }

    public function testWrite(): void
    {
        self::assertEquals($this->storage, $this->storage->write('test', 'my-data'));
    }

    public function testRead(): void
    {
        self::assertNull($this->storage->read('test'));
    }
}
