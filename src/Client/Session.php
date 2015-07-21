<?php

namespace OpenErpByJsonRpc\Client;

class Session
    extends AClient
    implements IClient
{
    /**
     * @var string the base of URL for all session action
     */
    private $path = 'session/:method';

    /**
     * Return the session information
     *
     * @return array
     * @throws \OpenErpByJsonRpc\Exception\JsonException
     */
    public function getInfos()
    {
        if (true === $this->openerp_jsonrpc->isLogged()) {
            return $this->openerp_jsonrpc->call($this->getPath('get_session_info'));
        } else {
            return $this->openerp_jsonrpc->callWithoutCredential($this->getPath('get_session_info'));
        }
    }

    /**
     * Change current password
     *
     * @return array
     * @throws \OpenErpByJsonRpc\Exception\JsonException
     */
    public function changePassword($old_password, $new_password)
    {
        return $this->openerp_jsonrpc->call($this->getPath('change_password'), [
            'fields' => [
                ['name' => 'old_pwd', 'value' => $old_password],
                ['name' => 'new_password', 'value' => $new_password],
                ['name' => 'confirm_pwd', 'value' => $new_password],
            ],
        ]);
    }

    /**
     * Return the list of available language
     *
     * @return array
     * @throws \OpenErpByJsonRpc\Exception\JsonException
     */
    public function getLangList()
    {
        return $this->openerp_jsonrpc->callWithoutCredential($this->getPath('get_lang_list'));
    }

    /**
     * Return the list of available modules
     *
     * @return array
     * @throws \OpenErpByJsonRpc\Exception\JsonException
     */
    public function getModules()
    {
        return $this->openerp_jsonrpc->call($this->getPath('modules'));
    }

    /**
     * Return the path for a method
     *
     * @param string $method
     * @return string
     */
    private function getPath($method)
    {
        return str_replace(':method', $method, $this->path);
    }
}
