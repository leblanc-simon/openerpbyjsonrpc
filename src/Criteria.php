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

namespace OpenErpByJsonRpc;

/**
 * Class to build a search criteria.
 *
 * @license MIT
 * @author  Simon Leblanc <contact@leblanc-simon.eu>
 */
class Criteria
{
    public const EQUAL = '=';
    public const LESS_THAN = '<';
    public const LESS_EQUAL = '<=';
    public const GREATER_THAN = '>';
    public const GREATER_EQUAL = '>=';
    public const LIKE = 'like';
    public const ILIKE = 'ilike';
    public const NOT_EQUAL = '!=';
    public const IN = 'in';
    public const NOT_IN = 'not in';

    /**
     * List of criterions in the criteria.
     *
     * @var array<int, array{string, string, mixed}>
     */
    private array $criterions = [];

    /**
     * Get an instance of Criteria.
     */
    public static function create(): Criteria
    {
        return new self();
    }

    /**
     * Add a criterion in the criteria.
     *
     * @param string $field   The field name
     * @param mixed  $value   The value to search
     * @param string $compare The comparator
     */
    public function add(string $field, $value, string $compare = self::EQUAL): Criteria
    {
        $this->criterions[] = [$field, $compare, $value];

        return $this;
    }

    /**
     * Get the criteria in the good format for OpenERP.
     *
     * @return array<int, array{string, string, mixed}> The criteria of search
     */
    public function get(): array
    {
        return $this->criterions;
    }

    /**
     * Add an equal criterion in the criteria.
     *
     * @param string $field The field name
     * @param mixed  $value The value to search
     */
    public function equal(string $field, $value): Criteria
    {
        return $this->add($field, $value, self::EQUAL);
    }

    /**
     * Add a less than criterion in the criteria.
     *
     * @param string $field The field name
     * @param mixed  $value The value to search
     */
    public function lessThan(string $field, $value): Criteria
    {
        return $this->add($field, $value, self::LESS_THAN);
    }

    /**
     * Add a less equal criterion in the criteria.
     *
     * @param string $field The field name
     * @param mixed  $value The value to search
     */
    public function lessEqual(string $field, $value): Criteria
    {
        return $this->add($field, $value, self::LESS_EQUAL);
    }

    /**
     * Add a greater than criterion in the criteria.
     *
     * @param string $field The field name
     * @param mixed  $value The value to search
     */
    public function greaterThan(string $field, $value): Criteria
    {
        return $this->add($field, $value, self::GREATER_THAN);
    }

    /**
     * Add a greater equal criterion in the criteria.
     *
     * @param string $field The field name
     * @param mixed  $value The value to search
     */
    public function greaterEqual(string $field, $value): Criteria
    {
        return $this->add($field, $value, self::GREATER_EQUAL);
    }

    /**
     * Add a like criterion in the criteria.
     *
     * @param string $field The field name
     * @param mixed  $value The value to search
     */
    public function like(string $field, $value): Criteria
    {
        return $this->add($field, $value, self::LIKE);
    }

    /**
     * Add a ilike criterion in the criteria.
     *
     * @param string $field The field name
     * @param mixed  $value The value to search
     */
    public function ilike(string $field, $value): Criteria
    {
        return $this->add($field, $value, self::ILIKE);
    }

    /**
     * Add a not equal criterion in the criteria.
     *
     * @param string $field The field name
     * @param mixed  $value The value to search
     */
    public function notEqual(string $field, $value): Criteria
    {
        return $this->add($field, $value, self::NOT_EQUAL);
    }

    /**
     * Add a in criterion in the criteria.
     *
     * @param string $field The field name
     * @param mixed  $value The value to search
     */
    public function in(string $field, $value): Criteria
    {
        return $this->add($field, $value, self::IN);
    }

    /**
     * Add a not in criterion in the criteria.
     *
     * @param string $field The field name
     * @param mixed  $value The value to search
     */
    public function notIn(string $field, $value): Criteria
    {
        return $this->add($field, $value, self::NOT_IN);
    }
}
