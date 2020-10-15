<?php

use OpenErpByJsonRpc\Client\Model;
use OpenErpByJsonRpc\Criteria;
use OpenErpByJsonRpc\Exception\ClientException;
use OpenErpByJsonRpc\Exception\NotSingleException;
use OpenErpByJsonRpc\JsonRpc\OpenERP;
use OpenErpByJsonRpc\JsonRpc\ZendJsonRpc;
use OpenErpByJsonRpc\Storage\NullStorage;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    /**
     * @var array
     */
    static private $config;

    /**
     * @var Model
     */
    private $model;

    /**
     * This method is called before the first test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function setUpBeforeClass(): void
    {
        $content = \file_get_contents(\dirname(__DIR__).'/config.test.json');
        if (false === $content) {
            self::fail('Impossible to read '.\dirname(__DIR__).'/config.test.json');
            return;
        }

        self::$config = \json_decode($content, true);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $json_rpc = new ZendJsonRpc(self::$config['url']);
        $openerp = new OpenERP($json_rpc, new NullStorage([]));
        $openerp
            ->setBaseUri(self::$config['url'])
            ->setPort(self::$config['port'])
            ->setUsername(self::$config['username'])
            ->setPassword(self::$config['password'])
            ->setDatabase(self::$config['database']);

        $this->model = new Model($openerp);
    }

    public function testReadOneRecord(): void
    {
        /** @var array $result */
        $result = $this->model->readOne('res.users', 1, ['id', 'login']);
        self::assertIsArray($result);
        self::assertEquals(self::$config['username'], $result['login']);
        self::assertEquals(1, $result['id']);
    }

    public function testReadOneNonExistentRecord(): void
    {
        $result = $this->model->readOne('res.users', 0, ['id', 'login']);
        self::assertNull($result);
    }

    public function testReadOneMoreThanOneRecord(): void
    {
        $this->expectException(NotSingleException::class);
        $this->model->readOne('res.lang', [1, 2], ['id', 'name']);
    }

    public function testReadRecord(): void
    {
        $result = $this->model->read('res.users', 1, ['id', 'login']);
        self::assertIsArray($result);
        self::assertEquals(self::$config['username'], $result[0]['login']);
        self::assertEquals(1, $result[0]['id']);
    }

    public function testReadNonExistentRecord(): void
    {
        $result = $this->model->read('res.users', 0, ['id', 'login']);
        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testSearchWithArrayCriteria(): void
    {
        $result = $this->model->search('res.users', [['login', '=', self::$config['username']]], ['id', 'login']);
        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertEquals([['id' => 1, 'login' => self::$config['username']]], $result);
    }

    public function testSearchWithCriteria(): void
    {
        $criteria = new Criteria();
        $criteria->equal('login', self::$config['username']);
        $result = $this->model->search('res.users', $criteria, ['id', 'login']);
        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertEquals([['id' => 1, 'login' => self::$config['username']]], $result);
    }

    public function testSearchWithoutResult(): void
    {
        $criteria = new Criteria();
        $criteria->equal('login', self::$config['username'].'-----bad');
        $result = $this->model->search('res.users', $criteria, ['id', 'login']);
        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testFailSearchWithBadCriteria(): void
    {
        $this->expectException(ClientException::class);
        // @phpstan-ignore-next-line
        $this->model->search('res.users', null, ['id', 'login']);
    }

    public function testCreateRecord(): int
    {
        $result = $this->model->create('res.partner', [
            'name' => 'Unit Tester',
            'function' => 'unit-test',
            'phone' => 'no-phone',
            'mobile' => 'no-mobile',
            'email' => 'test@unit-test.org',
        ]);

        self::assertIsInt($result);

        return $result;
    }

    /**
     * @depends testCreateRecord

     */
    public function testReadCreatedRecord(int $id): void
    {
        /** @var array $result */
        $result = $this->model->readOne('res.partner', $id, ['name']);
        self::assertEquals('Unit Tester', $result['name']);
    }

    /**
     * @depends testCreateRecord

     */
    public function testWriteRecord(int $id): int
    {
        $result = $this->model->write('res.partner', $id, [
            'name' => 'Unit Tester Update',
            'function' => 'unit-test-update',
            'phone' => 'no-phone-update',
            'mobile' => 'no-mobile-update',
        ]);

        self::assertTrue($result);

        return $id;
    }

    /**
     * @depends testWriteRecord

     */
    public function testReadWriteRecord(int $id): void
    {
        $result = $this->model->readOne('res.partner', $id, ['name']);
        /** @var array $result */
        self::assertEquals('Unit Tester Update', $result['name']);
    }

    /**
     * @depends testWriteRecord

     */
    public function testRemoveRecord(int $id): void
    {
        $result = $this->model->remove('res.partner', $id);
        self::assertTrue($result);
    }
}
