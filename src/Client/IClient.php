<?php

namespace OpenErpByJsonRpc\Client;

use OpenErpByJsonRpc\JsonRpc\OpenERP;

interface IClient
{
    public function __construct(OpenERP $json_rpc);
}
