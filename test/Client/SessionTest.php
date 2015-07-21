<?php

class SessionTest extends PHPUnit_Framework_TestCase
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
    public static function setUpBeforeClass()
    {
        self::$config = json_decode(
            file_get_contents(dirname(__DIR__).'/config.test.json'),
            true
        );
    }

    /**
     * @param bool|false $login
     * @return \OpenErpByJsonRpc\Client\Session
     */
    private function getSession($login = false)
    {
        $json_rpc = new \OpenErpByJsonRpc\JsonRpc\ZendJsonRpc(self::$config['url']);
        $openerp = new \OpenErpByJsonRpc\JsonRpc\OpenERP($json_rpc, new \OpenErpByJsonRpc\Storage\NullStorage([]));
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

        return new \OpenErpByJsonRpc\Client\Session($openerp);
    }

    public function testGetNotLoggedSessionInformation()
    {
        $session = $this->getSession();
        $informations = $session->getInfos();
        $this->assertInternalType('array', $informations);
        $this->assertEquals(null, $informations['uid']);
    }

    public function testGetLoggedSessionInformation()
    {
        $session = $this->getSession(true);
        $informations = $session->getInfos();
        $this->assertInternalType('array', $informations);
        $this->assertEquals(1, $informations['uid']);
    }

    public function testGetLanguages()
    {
        $session = $this->getSession();
        $languages = $session->getLangList();
        $this->assertInternalType('array', $languages);
        $this->assertContains(['fr_FR', 'French / Français'], $languages);
    }

    public function testGetModules()
    {
        $session = $this->getSession(true);
        $modules = $session->getModules();
        $this->assertInternalType('array', $modules);
        $this->assertContains('web', $modules);
    }

    public function testChangePassword()
    {
        $session = $this->getSession(true);
        $response = $session->changePassword(self::$config['password'], 'new-password');
        $this->assertEquals(['new_password' => 'new-password'], $response);

        // Restore password
        $password = self::$config['password'];
        self::$config['password'] = 'new-password';
        $session = $this->getSession(true);
        $session->changePassword(self::$config['password'], $password);
        self::$config['password'] = $password;
    }

}
