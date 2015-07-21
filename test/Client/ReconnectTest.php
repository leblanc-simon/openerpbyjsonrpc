<?php

class ReconnectTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    static private $config;

    /**
     * @var string
     */
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
        if (is_dir(self::$directory) === false) {
            mkdir(static::$directory, 0755);
        }

        return new \OpenErpByJsonRpc\Storage\FileStorage([
            'directory' => self::$directory,
            'prefix' => 'test_',
        ]);
    }

    public function testReconnectWithoutLogin()
    {
        $json_rpc = new \OpenErpByJsonRpc\JsonRpc\ZendJsonRpc(self::$config['url']);
        $openerp = new \OpenErpByJsonRpc\JsonRpc\OpenERP($json_rpc, $this->getStorage());
        $openerp
            ->setBaseUri(self::$config['url'])
            ->setPort(self::$config['port'])
            ->setUsername(self::$config['username'])
            ->setPassword(self::$config['password'])
            ->setDatabase(self::$config['database'])
            ->reconnectOrLogin(null)
        ;

        $session = new \OpenErpByJsonRpc\Client\Session($openerp);
        $infos = $session->getInfos();
        $session_id = $infos['session_id'];

        $openerp = new \OpenErpByJsonRpc\JsonRpc\OpenERP($json_rpc, $this->getStorage());
        $openerp
            ->setBaseUri(self::$config['url'])
            ->setPort(self::$config['port'])
            ->reconnectOrLogin($session_id)
        ;

        $this->assertTrue($openerp->isLogged());
    }
}
