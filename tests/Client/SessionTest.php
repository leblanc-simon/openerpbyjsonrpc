<?php

declare(strict_types=1);

use OpenErpByJsonRpc\Client\Session;
use OpenErpByJsonRpc\JsonRpc\OpenERP;
use OpenErpByJsonRpc\JsonRpc\ZendJsonRpc;
use OpenErpByJsonRpc\Storage\NullStorage;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private static $config;

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

    private function getSession(bool $login = false): Session
    {
        $jsonRpc = new ZendJsonRpc(self::$config['url']);
        $openerp = new OpenERP($jsonRpc, new NullStorage([]));
        $openerp
            ->setBaseUri(self::$config['url'])
            ->setPort(self::$config['port'])
        ;

        if ($login) {
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
        self::assertEquals(null, $informations['uid']);
    }

    public function testGetLoggedSessionInformation(): void
    {
        $session = $this->getSession(true);
        $informations = $session->getInfos();
        // The administrator created with the database is the user id 2
        // (id 1 being the internal "__system__" account since Odoo 12).
        self::assertEquals(2, $informations['uid']);
    }

    public function testGetLanguages(): void
    {
        $session = $this->getSession();
        $languages = $session->getLangList();
        self::assertContains(['fr_FR', 'French / Français'], $languages);
    }

    public function testGetModules(): void
    {
        $session = $this->getSession(true);
        $modules = $session->getModules();
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
