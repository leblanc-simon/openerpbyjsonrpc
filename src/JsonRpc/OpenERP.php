<?php

namespace OpenErpByJsonRpc\JsonRpc;

use OpenErpByJsonRpc\Exception\JsonException;
use OpenErpByJsonRpc\Exception\SessionException;
use OpenErpByJsonRpc\Storage\StorageInterface;

class OpenERP
{
    const BASE_PATH = '/web/';

    /**
     * @var IJsonRpc
     */
    private $json_rpc;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var string
     */
    private $base_uri;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $database;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $session_id;

    /**
     * @var array
     */
    private $context;

    /**
     * @var bool
     */
    private $long_call = false;

    /**
     * @param IJsonRpc         $json_rpc
     * @param StorageInterface $storage
     */
    public function __construct(IJsonRpc $json_rpc, StorageInterface $storage)
    {
        $this->json_rpc = $json_rpc;
        $this->storage = $storage;
    }

    public function call($path, $params = [])
    {
        if (null === $this->session_id) {
            if (false === $this->login()) {
                throw new JsonException('Impossible to login');
            }
        }

        $params = array_merge(['context' => $this->context], $params);

        $result = $this->json_rpc->call($this->getUri($path), 'call', $params, $this->session_id, $this->long_call);
        $this->long_call = false;

        return $result;
    }

    public function callWithoutCredential($path, $params = [])
    {
        $result = $this->json_rpc->call($this->getUri($path), 'call', $params, null, $this->long_call);
        $this->long_call = false;

        return $result;
    }

    public function callBase($model, $method, array $args = [], $kwargs = null)
    {
        if (null === $this->session_id) {
            if (false === $this->login()) {
                throw new JsonException('Impossible to login');
            }
        }

        $result = $this->json_rpc->call($this->getUri('dataset/call_kw'), 'call', [
            'model' => $model,
            'method' => $method,
            'args' => $args,
            'kwargs' => $kwargs ?: new \stdClass(),
        ], null, $this->long_call);

        $this->long_call = false;

        return $result;
    }

    public function prepareLongCall()
    {
        $this->long_call = true;
    }

    public function isLogged()
    {
        return null !== $this->session_id;
    }

    /**
     * Set the URI of the OpenERP server
     *
     * @param   string $base_uri The base URI of the OpenERP server
     * @return  self
     */
    public function setBaseUri($base_uri)
    {
        $this->base_uri = $base_uri;

        return $this;
    }

    /**
     * Set the port of the OpenERP server
     *
     * @param   int $port The port of the OpenERP server (require if it's not a standard port)
     * @return  self
     * @throws  \InvalidArgumentException   if the port is not a numeric
     */
    public function setPort($port)
    {
        if (false === is_numeric($port) && null !== $port) {
            throw new \InvalidArgumentException('port must be a numeric');
        }

        if (null !== $port) {
            $port = (int)$port;
        }

        $this->port = $port;

        return $this;
    }

    /**
     * Set the database name
     *
     * @param   string $database The database name to use
     * @return  self
     */
    public function setDatabase($database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * Set the username to connect into the OpenERP server
     *
     * @param   string $username the username to connect into the OpenERP server
     * @return  self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set the password to connect into the OpenERP server
     *
     * @param   string $password the password to connect into the OpenERP server
     * @return  self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function reconnectOrLogin($session_id)
    {
        try {
            $datas = $this->storage->read($session_id);
            if (null === $datas || false === is_array($datas)) {
                throw new SessionException();
            }

            $this->session_id = $datas['session_id'];
            $this->context = $datas['user_context'];
            call_user_func_array([$this->json_rpc, 'setCookie'], $datas['cookie']);

            $response = $this->call('session/get_session_info');

            if (false === is_array($response)) {
                throw new JsonException('response must be an array');
            }

            $required_datas = ['username', 'user_context', 'uid', 'session_id', 'db', 'company_id'];
            foreach ($required_datas as $data) {
                if (false === isset($response[$data])) {
                    throw new JsonException(sprintf('%s is not in the response', $data));
                }
            }

            $this->session_id = $response['session_id'];
            $this->context = $response['user_context'];
            $this->database = $response['db'];
            $this->username = $response['username'];

            return true;
        } catch (\Exception $e) {
            $this->session_id = null;
            $this->context = null;
        }

        return $this->login();
    }

    /**
     * Login in the OpenERP server
     *
     * @return  bool    True if the login is OK, false else
     */
    private function login()
    {
        try {
            $response = $this->callWithoutCredential('session/authenticate', [
                'base_location' => $this->base_uri,
                'db' => $this->database,
                'login' => $this->username,
                'password' => $this->password,
            ]);

            if (false === is_array($response)) {
                throw new JsonException('response must be an array');
            }

            if (false === isset($response['user_context'])) {
                throw new JsonException('context is not in the response');
            }

            if (false === isset($response['session_id'])) {
                throw new JsonException('session_id is not in the response');
            }

            $cookie = $this->json_rpc->getCookie();

            $this->context = $response['user_context'];
            $this->session_id = $cookie['value'];

            $datas = [
                'user_context' => $this->context,
                'session_id' => $this->session_id,
                'cookie' => $cookie,
            ];

            $this->storage->write($this->session_id, $datas);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Build the complete URI
     *
     * @param   string $path The path to call
     * @return  string          The complete URI to call
     * @throws  JsonException   If the base_uri is not define
     */
    private function getUri($path)
    {
        if (null === $this->base_uri) {
            throw new JsonException('base_uri must be define');
        }

        $base_uri = $this->base_uri;

        if (null !== $this->port) {
            $base_uri .= ':'.$this->port;
        }

        return $base_uri.self::BASE_PATH.$path;
    }
}
