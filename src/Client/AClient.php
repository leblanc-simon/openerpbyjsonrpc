<?php

namespace OpenErpByJsonRpc\Client;

use OpenErpByJsonRpc\JsonRpc\OpenERP;

abstract class AClient
{
    protected $openerp_jsonrpc;

    public function __construct(OpenERP $openerp_jsonrpc)
    {
        $this->openerp_jsonrpc = $openerp_jsonrpc;
    }
}
