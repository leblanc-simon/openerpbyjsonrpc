<?php

use OpenErpByJsonRpc\Client\Session;
use OpenErpByJsonRpc\JsonRpc\OpenERP;
use OpenErpByJsonRpc\JsonRpc\ZendJsonRpc;
use OpenErpByJsonRpc\Storage\FileStorage;
use PHPUnit\Framework\TestCase;

class ReconnectTest extends TestCase
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
        if (is_dir(self::$directory) === false) {
            mkdir(static::$directory, 0755);
        }

        return new FileStorage([
            'directory' => self::$directory,
            'prefix' => 'test_',
        ]);
    }

    public function testReconnectWithoutLogin(): void
    {
        $json_rpc = new ZendJsonRpc(self::$config['url']);
        $openerp = new OpenERP($json_rpc, $this->getStorage());
        $openerp
            ->setBaseUri(self::$config['url'])
            ->setPort(self::$config['port'])
            ->setUsername(self::$config['username'])
            ->setPassword(self::$config['password'])
            ->setDatabase(self::$config['database'])
            ->reconnectOrLogin(null)
        ;

        $session = new Session($openerp);
        $infos = $session->getInfos();
        $session_id = $infos['session_id'];

        $openerp = new OpenERP($json_rpc, $this->getStorage());
        $openerp
            ->setBaseUri(self::$config['url'])
            ->setPort(self::$config['port'])
            ->reconnectOrLogin($session_id)
        ;

        self::assertTrue($openerp->isLogged());
    }
}
