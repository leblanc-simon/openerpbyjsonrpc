<?php

use OpenErpByJsonRpc\Client\Session;
use OpenErpByJsonRpc\JsonRpc\OpenERP;
use OpenErpByJsonRpc\JsonRpc\ZendJsonRpc;
use OpenErpByJsonRpc\Storage\NullStorage;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    /**
     * @var array
     */
    static private $config;

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
     * @param bool|false $login
     * @return Session
     */
    private function getSession($login = false): Session
    {
        $json_rpc = new ZendJsonRpc(self::$config['url']);
        $openerp = new OpenERP($json_rpc, new NullStorage([]));
        $openerp
            ->setBaseUri(self::$config['url'])
            ->setPort(self::$config['port'])
        ;

        if (true === $login) {
            $openerp
                ->setUsername(self::$config['username'])
                ->setPassword(self::$config['password'])
                ->setDatabase(self::$config['database'])
            ;
            $openerp->reconnectOrLogin(null);
        }

        return new Session($openerp);
    }

    public function testGetNotLoggedSessionInformation(): void
    {
        $session = $this->getSession();
        $informations = $session->getInfos();
        self::assertIsArray($informations);
        self::assertEquals(null, $informations['uid']);
    }

    public function testGetLoggedSessionInformation(): void
    {
        $session = $this->getSession(true);
        $informations = $session->getInfos();
        self::assertIsArray($informations);
        self::assertEquals(1, $informations['uid']);
    }

    public function testGetLanguages(): void
    {
        $session = $this->getSession();
        $languages = $session->getLangList();
        self::assertIsArray($languages);
        self::assertContains(['fr_FR', 'French / Français'], $languages);
    }

    public function testGetModules(): void
    {
        $session = $this->getSession(true);
        $modules = $session->getModules();
        self::assertIsArray($modules);
        self::assertContains('web', $modules);
    }

    public function testChangePassword(): void
    {
        $session = $this->getSession(true);
        $response = $session->changePassword(self::$config['password'], 'new-password');
        self::assertEquals(['new_password' => 'new-password'], $response);

        // Restore password
        $password = self::$config['password'];
        self::$config['password'] = 'new-password';
        $session = $this->getSession(true);
        $session->changePassword(self::$config['password'], $password);
        self::$config['password'] = $password;
    }
}
