<?php

declare(strict_types=1);

use OpenErpByJsonRpc\Client\Model;
use OpenErpByJsonRpc\Criteria;
use OpenErpByJsonRpc\Exception\ClientException;
use OpenErpByJsonRpc\Exception\NotSingleException;
use OpenErpByJsonRpc\JsonRpc\OpenERP;
use OpenErpByJsonRpc\JsonRpc\ZendJsonRpc;
use OpenErpByJsonRpc\Storage\NullStorage;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private static $config;

    private Model $model;

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
        }

        self::$config = \json_decode($content, true);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $jsonRpc = new ZendJsonRpc(self::$config['url']);
        $openerp = new OpenERP($jsonRpc, new NullStorage([]));
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
        // Since Odoo 12 the user id 1 is the internal "__system__" account; the
        // administrator created with the database is the user id 2.
        /** @var array<string, mixed> $result */
        $result = $this->model->readOne('res.users', 2, ['id', 'login']);
        self::assertEquals(self::$config['username'], $result['login']);
        self::assertEquals(2, $result['id']);
    }

    public function testReadOneNonExistentRecord(): void
    {
        $result = $this->model->readOne('res.users', 0, ['id', 'login']);
        self::assertNull($result);
    }

    public function testReadOneMoreThanOneRecord(): void
    {
        // res.country always contains several active records (res.lang only has
        // fr_FR active on a fresh Odoo 18 database).
        $this->expectException(NotSingleException::class);
        $this->model->readOne('res.country', [1, 2], ['id', 'name']);
    }

    public function testReadRecord(): void
    {
        $result = $this->model->read('res.users', 2, ['id', 'login']);
        self::assertEquals(self::$config['username'], $result[0]['login']);
        self::assertEquals(2, $result[0]['id']);
    }

    public function testReadNonExistentRecord(): void
    {
        $result = $this->model->read('res.users', 0, ['id', 'login']);
        self::assertCount(0, $result);
    }

    public function testSearchWithArrayCriteria(): void
    {
        $result = $this->model->search('res.users', [['login', '=', self::$config['username']]], ['id', 'login']);
        self::assertCount(1, $result);
        self::assertEquals([['id' => 2, 'login' => self::$config['username']]], $result);
    }

    public function testSearchWithCriteria(): void
    {
        $criteria = new Criteria();
        $criteria->equal('login', self::$config['username']);
        $result = $this->model->search('res.users', $criteria, ['id', 'login']);
        self::assertCount(1, $result);
        self::assertEquals([['id' => 2, 'login' => self::$config['username']]], $result);
    }

    public function testSearchWithoutResult(): void
    {
        $criteria = new Criteria();
        $criteria->equal('login', self::$config['username'].'-----bad');
        $result = $this->model->search('res.users', $criteria, ['id', 'login']);
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

        self::assertGreaterThan(0, $result);

        return $result;
    }

    #[Depends('testCreateRecord')]
    public function testReadCreatedRecord(int $id): void
    {
        /** @var array<string, mixed> $result */
        $result = $this->model->readOne('res.partner', $id, ['name']);
        self::assertEquals('Unit Tester', $result['name']);
    }

    #[Depends('testCreateRecord')]
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

    #[Depends('testWriteRecord')]
    public function testReadWriteRecord(int $id): void
    {
        /** @var array<string, mixed> $result */
        $result = $this->model->readOne('res.partner', $id, ['name']);
        self::assertEquals('Unit Tester Update', $result['name']);
    }

    #[Depends('testWriteRecord')]
    public function testRemoveRecord(int $id): void
    {
        $result = $this->model->remove('res.partner', $id);
        self::assertTrue($result);
    }
}
