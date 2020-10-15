<?php

declare(strict_types=1);
/**
 * This file is part of the OpenErpByJsonRpc package.
 *
 * (c) Simon Leblanc <contact@leblanc-simon.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OpenErpByJsonRpc\Client;

use OpenErpByJsonRpc\JsonRpc\OpenERP;

abstract class AClient
{
    /**
     * @var OpenERP
     */
    protected $openerp_jsonrpc;

    public function __construct(OpenERP $openerp_jsonrpc)
    {
        $this->openerp_jsonrpc = $openerp_jsonrpc;
    }
}
