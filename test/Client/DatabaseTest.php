<?php

class DatabaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    static private $config;

    /**
     * @var \OpenErpByJsonRpc\Client\Database
     */
    private $database;

    /**
     * This method is called before the first test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function setUpBeforeClass()
    {
        self::$config = json_decode(
            file_get_contents(dirname(__DIR__).'/config.test.json'),
            true
        );
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $json_rpc = new \OpenErpByJsonRpc\JsonRpc\ZendJsonRpc(self::$config['url']);
        $openerp = new \OpenErpByJsonRpc\JsonRpc\OpenERP($json_rpc, new \OpenErpByJsonRpc\Storage\NullStorage([]));
        $openerp
            ->setBaseUri(self::$config['url'])
            ->setPort(self::$config['port'])
        ;

        $this->database = new \OpenErpByJsonRpc\Client\Database($openerp);
    }

    /**
     * This method is called after the last test of this test class is run.
     *
     * @since Method available since Release 3.4.0
     */
    public static function tearDownAfterClass()
    {
        $json_rpc = new \OpenErpByJsonRpc\JsonRpc\ZendJsonRpc(self::$config['url']);
        $openerp = new \OpenErpByJsonRpc\JsonRpc\OpenERP($json_rpc, new \OpenErpByJsonRpc\Storage\NullStorage([]));
        $openerp
            ->setBaseUri(self::$config['url'])
            ->setPort(self::$config['port'])
        ;

        $database = new \OpenErpByJsonRpc\Client\Database($openerp);

        $database->drop(self::$config['master_password'], self::$config['database'].'_create');
        $database->drop(self::$config['master_password'], self::$config['database'].'_duplicate');
    }

    public function testListDatabase()
    {
        $list = $this->database->getList();
        $this->assertInternalType('array', $list);
        $this->assertCount(1, $list);
        $this->assertEquals([self::$config['database']], $list);
    }

    /**
     * @large
     */
    public function testCreateDatabaseSuccess()
    {
        $this->assertTrue($this->database->create(
            self::$config['master_password'],
            self::$config['database'].'_create',
            false,
            'fr_FR',
            'admin'
        ));

        $this->assertEquals(
            [
                self::$config['database'],
                self::$config['database'].'_create',
            ],
            $this->database->getList()
        );
    }

    /**
     * @expectedException \Zend\Json\Server\Exception\ErrorException
     * @expectedExceptionMessage Odoo Server Error
     */
    public function testCreateDatabaseFailBecauseBadMasterPassword()
    {
        $this->database->create(
            self::$config['master_password'].'----bad',
            self::$config['database'].'_create_bad_master',
            false,
            'fr_FR',
            'admin'
        );
    }

    /**
     * @large
     */
    public function testDuplicateDatabaseSuccess()
    {
        $this->assertTrue($this->database->duplicate(
            self::$config['master_password'],
            self::$config['database'],
            self::$config['database'].'_duplicate'
        ));

        $this->assertEquals(
            [
                self::$config['database'],
                self::$config['database'].'_create',
                self::$config['database'].'_duplicate',
            ],
            $this->database->getList()
        );
    }

    /**
     * @large
     */
    public function testDropDatabaseSuccess()
    {
        $this->assertTrue($this->database->drop(
            self::$config['master_password'],
            self::$config['database'].'_duplicate'
        ));

        $this->assertEquals(
            [
                self::$config['database'],
                self::$config['database'].'_create',
            ],
            $this->database->getList()
        );
    }

}
