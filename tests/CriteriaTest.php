<?php

use OpenErpByJsonRpc\Criteria;
use PHPUnit\Framework\TestCase;

class CriteriaTest extends TestCase
{

    public function testCriteriaEqual(): void
    {
        $criteria = Criteria::create();
        $criteria->equal('a', 1);
        self::assertEquals([['a', '=', 1]], $criteria->get());
    }

    public function testCriteriaLessThan(): void
    {
        $criteria = Criteria::create();
        $criteria->lessThan('a', 1);
        self::assertEquals([['a', '<', 1]], $criteria->get());
    }

    public function testCriteriaLessEqual(): void
    {
        $criteria = Criteria::create();
        $criteria->lessEqual('a', 1);
        self::assertEquals([['a', '<=', 1]], $criteria->get());
    }

    public function testCriteriaGreaterThan(): void
    {
        $criteria = Criteria::create();
        $criteria->greaterThan('a', 1);
        self::assertEquals([['a', '>', 1]], $criteria->get());
    }

    public function testCriteriaGreaterEqual(): void
    {
        $criteria = Criteria::create();
        $criteria->greaterEqual('a', 1);
        self::assertEquals([['a', '>=', 1]], $criteria->get());
    }

    public function testCriteriaLike(): void
    {
        $criteria = Criteria::create();
        $criteria->like('a', 1);
        self::assertEquals([['a', 'like', 1]], $criteria->get());
    }

    public function testCriteriaILike(): void
    {
        $criteria = Criteria::create();
        $criteria->ilike('a', 1);
        self::assertEquals([['a', 'ilike', 1]], $criteria->get());
    }

    public function testCriteriaNotEqual(): void
    {
        $criteria = Criteria::create();
        $criteria->notEqual('a', 1);
        self::assertEquals([['a', '!=', 1]], $criteria->get());
    }

    public function testCriteriaIn(): void
    {
        $criteria = Criteria::create();
        $criteria->in('a', [1, 2]);
        self::assertEquals([['a', 'in', [1, 2]]], $criteria->get());
    }

    public function testCriteriaNotIn(): void
    {
        $criteria = Criteria::create();
        $criteria->notIn('a', [1, 2]);
        self::assertEquals([['a', 'not in', [1, 2]]], $criteria->get());
    }

    public function testMultipleCriteria(): void
    {
        $criteria = Criteria::create();
        $criteria
            ->equal('a', 1)
            ->equal('b', 2)
        ;

        self::assertEquals([
            ['a', '=', 1],
            ['b', '=', 2],
        ], $criteria->get());
    }
}
