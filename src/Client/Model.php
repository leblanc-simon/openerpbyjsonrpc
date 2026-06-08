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
use OpenErpByJsonRpc\Exception\JsonException;
use OpenErpByJsonRpc\Exception\NotSingleException;

class Model extends AClient implements ClientInterface
{
    /**
     * @param array<mixed>|Criteria $criteria
     * @param string[]              $fields
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws ClientException
     * @throws JsonException
     */
    public function search(
        string $model,
        $criteria = [],
        array $fields = [],
        int $offset = 0,
        ?int $limit = null,
        string $sort = '',
    ): array {
        if ($criteria instanceof Criteria) {
            $criteria = $criteria->get();
        }

        // @phpstan-ignore-next-line
        if (false === \is_array($criteria)) {
            throw new ClientException('criteria must be an array or Criteria instance');
        }

        // Odoo 15+ removed the legacy /web/dataset/search_read controller, so we
        // go through /web/dataset/call_kw and invoke the model's search_read method.
        $kwargs = ['offset' => $offset];

        if (null !== $limit) {
            $kwargs['limit'] = $limit;
        }

        if ('' !== $sort) {
            $kwargs['order'] = $sort;
        }

        $result = $this->openerpJsonrpc->callBase($model, 'search_read', [$criteria, $fields], $kwargs);

        return \is_array($result) ? $result : [];
    }

    /**
     * @param int|int[] $ids
     * @param string[]  $fields
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws ClientException
     * @throws JsonException
     */
    public function read(string $model, $ids, array $fields = []): array
    {
        if (\is_numeric($ids)) {
            $ids = [$ids];
        }

        $criteria = new Criteria();
        $criteria->in('id', $ids);

        return $this->search($model, $criteria, $fields);
    }

    /**
     * @param int|int[] $id
     * @param string[]  $fields
     *
     * @return array<string, mixed>|null
     *
     * @throws ClientException
     * @throws NotSingleException
     * @throws JsonException
     */
    public function readOne(string $model, $id, array $fields = []): ?array
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

    /**
     * @param array<string, mixed> $datas
     */
    public function create(string $model, array $datas): int
    {
        return $this->openerpJsonrpc->callBase($model, 'create', [$datas]);
    }

    /**
     * @param array<string, mixed> $datas
     */
    public function write(string $model, int $id, array $datas): bool
    {
        return $this->openerpJsonrpc->callBase($model, 'write', [$id, $datas]);
    }

    public function remove(string $model, int $id): bool
    {
        return $this->openerpJsonrpc->callBase($model, 'unlink', [[$id]]);
    }
}
