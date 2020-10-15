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

use OpenErpByJsonRpc\Criteria;
use OpenErpByJsonRpc\Exception\ClientException;
use OpenErpByJsonRpc\Exception\NotSingleException;

class Model extends AClient implements ClientInterface
{
    private const PATH = 'dataset/:method';

    /**
     * @param array|Criteria $criteria
     *
     * @throws ClientException
     * @throws \OpenErpByJsonRpc\Exception\JsonException
     */
    public function search(
        string $model,
        $criteria = [],
        array $fields = [],
        int $offset = 0,
        ? int $limit = null,
        string $sort = ''
    ): array {
        if ($criteria instanceof Criteria) {
            $criteria = $criteria->get();
        }

        // @phpstan-ignore-next-line
        if (false === \is_array($criteria)) {
            throw new ClientException('criteria must be an array or Criteria instance');
        }

        $result = $this->openerp_jsonrpc->call(self::getPath('search_read'), [
            'model' => $model,
            'fields' => $fields,
            'domain' => $criteria,
            'offset' => $offset,
            'limit' => $limit,
            'sort' => $sort,
        ]);

        if (true === isset($result['records']) && true === \is_array($result['records'])) {
            return $result['records'];
        }

        return [];
    }

    /**
     * @param int|int[] $ids
     *
     * @throws ClientException
     * @throws \OpenErpByJsonRpc\Exception\JsonException
     */
    public function read(string $model, $ids, array $fields = []): array
    {
        if (true === \is_numeric($ids)) {
            $ids = [$ids];
        }

        $criteria = new Criteria();
        $criteria->in('id', $ids);

        return $this->search($model, $criteria, $fields);
    }

    /**
     * @param int|int[] $id
     *
     * @throws ClientException
     * @throws NotSingleException
     * @throws \OpenErpByJsonRpc\Exception\JsonException
     */
    public function readOne(string $model, $id, array $fields = []): ? array
    {
        $result = $this->read($model, $id, $fields);

        if (0 === \count($result)) {
            return null;
        }

        if (1 === \count($result)) {
            return $result[0];
        }

        throw new NotSingleException();
    }

    public function create(string $model, array $datas): int
    {
        return $this->openerp_jsonrpc->callBase($model, 'create', [$datas]);
    }

    public function write(string $model, int $id, array $datas): bool
    {
        return $this->openerp_jsonrpc->callBase($model, 'write', [$id, $datas]);
    }

    public function remove(string $model, int $id): bool
    {
        return $this->openerp_jsonrpc->callBase($model, 'unlink', [[$id]]);
    }

    private static function getPath(string $method): string
    {
        return \str_replace(':method', $method, self::PATH);
    }
}
