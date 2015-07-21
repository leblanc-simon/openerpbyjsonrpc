<?php

/**
 * Created by PhpStorm.
 * User: leviathan
 * Date: 21/07/15
 * Time: 02:12
 */
class CriteriaTest extends PHPUnit_Framework_TestCase
{
    public function testCriteriaEqual()
    {
        $criteria = \OpenErpByJsonRpc\Criteria::create();
        $criteria->equal('a', 1);
        $this->assertEquals([['a', '=', 1]], $criteria->get());
    }

    public function testCriteriaLessThan()
    {
        $criteria = \OpenErpByJsonRpc\Criteria::create();
        $criteria->lessThan('a', 1);
        $this->assertEquals([['a', '<', 1]], $criteria->get());
    }

    public function testCriteriaLessEqual()
    {
        $criteria = \OpenErpByJsonRpc\Criteria::create();
        $criteria->lessEqual('a', 1);
        $this->assertEquals([['a', '<=', 1]], $criteria->get());
    }

    public function testCriteriaGreaterThan()
    {
        $criteria = \OpenErpByJsonRpc\Criteria::create();
        $criteria->greaterThan('a', 1);
        $this->assertEquals([['a', '>', 1]], $criteria->get());
    }

    public function testCriteriaGreaterEqual()
    {
        $criteria = \OpenErpByJsonRpc\Criteria::create();
        $criteria->greaterEqual('a', 1);
        $this->assertEquals([['a', '>=', 1]], $criteria->get());
    }

    public function testCriteriaLike()
    {
        $criteria = \OpenErpByJsonRpc\Criteria::create();
        $criteria->like('a', 1);
        $this->assertEquals([['a', 'like', 1]], $criteria->get());
    }

    public function testCriteriaILike()
    {
        $criteria = \OpenErpByJsonRpc\Criteria::create();
        $criteria->ilike('a', 1);
        $this->assertEquals([['a', 'ilike', 1]], $criteria->get());
    }

    public function testCriteriaNotEqual()
    {
        $criteria = \OpenErpByJsonRpc\Criteria::create();
        $criteria->notEqual('a', 1);
        $this->assertEquals([['a', '!=', 1]], $criteria->get());
    }

    public function testCriteriaIn()
    {
        $criteria = \OpenErpByJsonRpc\Criteria::create();
        $criteria->in('a', [1, 2]);
        $this->assertEquals([['a', 'in', [1, 2]]], $criteria->get());
    }

    public function testCriteriaNotIn()
    {
        $criteria = \OpenErpByJsonRpc\Criteria::create();
        $criteria->notIn('a', [1, 2]);
        $this->assertEquals([['a', 'not in', [1, 2]]], $criteria->get());
    }

    public function testMultipleCriteria()
    {
        $criteria = \OpenErpByJsonRpc\Criteria::create();
        $criteria
            ->equal('a', 1)
            ->equal('b', 2)
        ;

        $this->assertEquals([
            ['a', '=', 1],
            ['b', '=', 2],
        ], $criteria->get());
    }
}
