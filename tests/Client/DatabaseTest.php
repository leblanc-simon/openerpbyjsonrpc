<?php

declare(strict_types=1);

use OpenErpByJsonRpc\Client\Database;
use OpenErpByJsonRpc\Exception\JsonException;
use OpenErpByJsonRpc\JsonRpc\OpenERP;
use OpenErpByJsonRpc\JsonRpc\ZendJsonRpc;
use OpenErpByJsonRpc\Storage\NullStorage;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private static mixed $config;

    private Database $database;

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
        ;

        $this->database = new Database($openerp);
    }

    /**
     * This method is called after the last test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function tearDownAfterClass(): void
    {
        $jsonRpc = new ZendJsonRpc(self::$config['url']);
        $openerp = new OpenERP($jsonRpc, new NullStorage([]));
        $openerp
            ->setBaseUri(self::$config['url'])
            ->setPort(self::$config['port'])
        ;

        $database = new Database($openerp);

        $database->drop(self::$config['master_password'], self::$config['database'].'_create');
        $database->drop(self::$config['master_password'], self::$config['database'].'_duplicate');
    }

    public function testListDatabase(): void
    {
        $list = $this->database->getList();
        self::assertCount(1, $list);
        self::assertEquals([self::$config['database']], $list);
    }

    #[Depends('testListDatabase')]
    public function testCreateDatabaseSuccess(): void
    {
        self::assertTrue($this->database->create(
            self::$config['master_password'],
            self::$config['database'].'_create',
            false,
            'fr_FR',
            'admin'
        ));

        self::assertEquals(
            [
                self::$config['database'],
                self::$config['database'].'_create',
            ],
            $this->database->getList()
        );
    }

    public function testCreateDatabaseFailBecauseBadMasterPassword(): void
    {
        // Odoo 15+ handles database creation through an HTTP form: a wrong master
        // password is reported as a rendered error page ("... error: Access Denied").
        $this->expectException(JsonException::class);
        $this->expectExceptionMessage('Access Denied');

        $this->database->create(
            self::$config['master_password'].'----bad',
            self::$config['database'].'_create_bad_master',
            false,
            'fr_FR',
            'admin'
        );
    }

    #[Depends('testCreateDatabaseSuccess')]
    public function testDuplicateDatabaseSuccess(): void
    {
        self::assertTrue($this->database->duplicate(
            self::$config['master_password'],
            self::$config['database'],
            self::$config['database'].'_duplicate'
        ));

        self::assertEquals(
            [
                self::$config['database'],
                self::$config['database'].'_create',
                self::$config['database'].'_duplicate',
            ],
            $this->database->getList()
        );
    }

    #[Depends('testDuplicateDatabaseSuccess')]
    public function testDropDatabaseSuccess(): void
    {
        self::assertTrue($this->database->drop(
            self::$config['master_password'],
            self::$config['database'].'_duplicate'
        ));

        self::assertEquals(
            [
                self::$config['database'],
                self::$config['database'].'_create',
            ],
            $this->database->getList()
        );
    }
}
