<?php

class ModelTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    static private $config;

    /**
     * @var \OpenErpByJsonRpc\Client\Model
     */
    private $model;

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
            ->setUsername(self::$config['username'])
            ->setPassword(self::$config['password'])
            ->setDatabase(self::$config['database']);

        $this->model = new \OpenErpByJsonRpc\Client\Model($openerp);
    }

    public function testReadOneRecord()
    {
        $result = $this->model->readOne('res.users', 1, ['id', 'login']);
        $this->assertInternalType('array', $result);
        $this->assertEquals(self::$config['username'], $result['login']);
        $this->assertEquals(1, $result['id']);
    }

    public function testReadOneNonExistentRecord()
    {
        $result = $this->model->readOne('res.users', 0, ['id', 'login']);
        $this->assertNull($result);
    }

    /**
     * @expectedException \OpenErpByJsonRpc\Exception\NotSingleException
     */
    public function testReadOneMoreThanOneRecord()
    {
        $this->model->readOne('res.lang', [1, 2], ['id', 'name']);
    }

    public function testReadRecord()
    {
        $result = $this->model->read('res.users', 1, ['id', 'login']);
        $this->assertInternalType('array', $result);
        $this->assertEquals(self::$config['username'], $result[0]['login']);
        $this->assertEquals(1, $result[0]['id']);
    }

    public function testReadNonExistentRecord()
    {
        $result = $this->model->read('res.users', 0, ['id', 'login']);
        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);
    }

    public function testSearchWithArrayCriteria()
    {
        $result = $this->model->search('res.users', [['login', '=', self::$config['username']]], ['id', 'login']);
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals([['id' => 1, 'login' => self::$config['username']]], $result);
    }

    public function testSearchWithCriteria()
    {
        $criteria = new \OpenErpByJsonRpc\Criteria();
        $criteria->equal('login', self::$config['username']);
        $result = $this->model->search('res.users', $criteria, ['id', 'login']);
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertEquals([['id' => 1, 'login' => self::$config['username']]], $result);
    }

    public function testSearchWithoutResult()
    {
        $criteria = new \OpenErpByJsonRpc\Criteria();
        $criteria->equal('login', self::$config['username'].'-----bad');
        $result = $this->model->search('res.users', $criteria, ['id', 'login']);
        $this->assertInternalType('array', $result);
        $this->assertCount(0, $result);
    }

    /**
     * @expectedException \OpenErpByJsonRpc\Exception\ClientException
     */
    public function testFailSearchWithBadCriteria()
    {
        $this->model->search('res.users', null, ['id', 'login']);
    }

    public function testCreateRecord()
    {
        $result = $this->model->create('res.partner', [
            'name' => 'Unit Tester',
            'function' => 'unit-test',
            'phone' => 'no-phone',
            'mobile' => 'no-mobile',
            'email' => 'test@unit-test.org',
        ]);

        $this->assertInternalType('int', $result);

        return $result;
    }

    /**
     * @depends testCreateRecord
     */
    public function testReadCreatedRecord($id)
    {
        $result = $this->model->readOne('res.partner', $id, ['name']);
        $this->assertEquals('Unit Tester', $result['name']);
    }

    /**
     * @depends testCreateRecord
     */
    public function testWriteRecord($id)
    {
        $result = $this->model->write('res.partner', $id, [
            'name' => 'Unit Tester Update',
            'function' => 'unit-test-update',
            'phone' => 'no-phone-update',
            'mobile' => 'no-mobile-update',
        ]);

        $this->assertTrue($result);

        return $id;
    }

    /**
     * @depends testWriteRecord
     */
    public function testReadWriteRecord($id)
    {
        $result = $this->model->readOne('res.partner', $id, ['name']);
        $this->assertEquals('Unit Tester Update', $result['name']);
    }

    /**
     * @depends testWriteRecord
     */
    public function testRemoveRecord($id)
    {
        $result = $this->model->remove('res.partner', $id);
        $this->assertTrue($result);
    }
}
