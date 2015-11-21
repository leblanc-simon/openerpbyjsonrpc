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

use OpenErpByJsonRpc\Criteria;
use OpenErpByJsonRpc\Exception\ClientException;
use OpenErpByJsonRpc\Exception\NotSingleException;

class Model
    extends AClient
    implements ClientInterface
{
    private $path = 'dataset/:method';

    public function search($model, $criteria = [], array $fields = [], $offset = 0, $limit = null, $sort = '')
    {
        if ($criteria instanceof Criteria) {
            $criteria = $criteria->get();
        }

        if (false === is_array($criteria)) {
            throw new ClientException('criteria must be an array or Criteria instance');
        }

        $result = $this->openerp_jsonrpc->call($this->getPath('search_read'), [
            'model' => $model,
            'fields' => $fields,
            'domain' => $criteria,
            'offset' => $offset,
            'limit' => $limit,
            'sort' => $sort,
        ]);

        if (isset($result['records']) === true && is_array($result['records']) === true) {
            return $result['records'];
        }

        return [];
    }

    public function read($model, $ids, array $fields = [])
    {
        if (true === is_numeric($ids)) {
            $ids = [$ids];
        }

        $criteria = new Criteria();
        $criteria->in('id', $ids);

        return $this->search($model, $criteria, $fields);
    }

    public function readOne($model, $id, array $fields = [])
    {
        $result = $this->read($model, $id, $fields);

        if (count($result) === 0) {
            return null;
        } elseif (count($result) === 1) {
            return $result[0];
        }

        throw new NotSingleException();
    }

    public function create($model, array $datas)
    {
        return $this->openerp_jsonrpc->callBase($model, 'create', [$datas]);
    }

    public function write($model, $id, array $datas)
    {
        return $this->openerp_jsonrpc->callBase($model, 'write', [$id, $datas]);
    }

    public function remove($model, $id)
    {
        return $this->openerp_jsonrpc->callBase($model, 'unlink', [[$id]]);
    }

    private function getPath($method)
    {
        return str_replace(':method', $method, $this->path);
    }
}
