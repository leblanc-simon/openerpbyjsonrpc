<?php

declare(strict_types=1);

use OpenErpByJsonRpc\Client\Session;
use OpenErpByJsonRpc\JsonRpc\OpenERP;
use OpenErpByJsonRpc\JsonRpc\ZendJsonRpc;
use OpenErpByJsonRpc\Storage\FileStorage;
use PHPUnit\Framework\TestCase;

class ReconnectTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private static $config;

    private static string $directory = __DIR__.'/../cache';

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
        }

        self::$config = \json_decode($content, true);
    }

    /**
     * Remove the cache directory.
     */
    protected function removeDirectory(): void
    {
        if (false === is_dir(self::$directory)) {
            return;
        }

        $files = glob(self::$directory.'/*');
        foreach ($files as $file) { // @phpstan-ignore-line
            unlink($file);
        }

        rmdir(self::$directory);
    }

    protected function getStorage(): FileStorage
    {
        if (false === is_dir(self::$directory)) {
            mkdir(self::$directory, 0755);
        }

        return new FileStorage([
            'directory' => self::$directory,
            'prefix' => 'test_',
        ]);
    }

    public function testReconnectWithoutLogin(): void
    {
        $jsonRpc = new ZendJsonRpc(self::$config['url']);
        $openerp = new OpenERP($jsonRpc, $this->getStorage());
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
        $sessionId = $infos['session_id'];

        $openerp = new OpenERP($jsonRpc, $this->getStorage());
        $openerp
            ->setBaseUri(self::$config['url'])
            ->setPort(self::$config['port'])
            ->reconnectOrLogin($sessionId)
        ;

        self::assertTrue($openerp->isLogged());
    }
}
