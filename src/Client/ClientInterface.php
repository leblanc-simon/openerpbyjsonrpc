<?php
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

interface ClientInterface
{
    public function __construct(OpenERP $json_rpc);
}
