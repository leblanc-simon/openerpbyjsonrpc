<?php

namespace OpenErpByJsonRpc;

/**
 * Class to build a search criteria
 *
 * @package OpenErpByJsonRpc
 * @license MIT
 * @author  Simon Leblanc <contact@leblanc-simon.eu>
 */
class Criteria
{
    const EQUAL = '=';
    const LESS_THAN = '<';
    const LESS_EQUAL = '<=';
    const GREATER_THAN = '>';
    const GREATER_EQUAL = '>=';
    const LIKE = 'like';
    const ILIKE = 'ilike';
    const NOT_EQUAL = '!=';
    const IN = 'in';
    const NOT_IN = 'not in';

    /**
     * List of criterion in the criteria
     * @var array
     */
    private $criterions = [];

    /**
     * Get an instance of Criteria
     *
     * @return Criteria
     */
    static public function create()
    {
        return new self();
    }

    /**
     * Add a criterion in the criteria
     *
     * @param   string $field   The field name
     * @param   mixed  $value   The value to search
     * @param   string $compare The comparator
     * @return  $this
     */
    public function add($field, $value, $compare = self::EQUAL)
    {
        $this->criterions[] = [$field, $compare, $value];

        return $this;
    }

    /**
     * Get the criteria in the good format for OpenERP
     *
     * @return array    The criteria of search
     */
    public function get()
    {
        return $this->criterions;
    }

    /**
     * Add an equal criterion in the criteria
     *
     * @param   string $field The field name
     * @param   mixed  $value The value to search
     * @return  $this
     */
    public function equal($field, $value)
    {
        return $this->add($field, $value, self::EQUAL);
    }

    /**
     * Add a less than criterion in the criteria
     *
     * @param   string $field The field name
     * @param   mixed  $value The value to search
     * @return  $this
     */
    public function lessThan($field, $value)
    {
        return $this->add($field, $value, self::LESS_THAN);
    }

    /**
     * Add a less equal criterion in the criteria
     *
     * @param   string $field The field name
     * @param   mixed  $value The value to search
     * @return  $this
     */
    public function lessEqual($field, $value)
    {
        return $this->add($field, $value, self::LESS_EQUAL);
    }

    /**
     * Add a greater than criterion in the criteria
     *
     * @param   string $field The field name
     * @param   mixed  $value The value to search
     * @return  $this
     */
    public function greaterThan($field, $value)
    {
        return $this->add($field, $value, self::GREATER_THAN);
    }

    /**
     * Add a greater equal criterion in the criteria
     *
     * @param   string $field The field name
     * @param   mixed  $value The value to search
     * @return  $this
     */
    public function greaterEqual($field, $value)
    {
        return $this->add($field, $value, self::GREATER_EQUAL);
    }

    /**
     * Add a like criterion in the criteria
     *
     * @param   string $field The field name
     * @param   mixed  $value The value to search
     * @return  $this
     */
    public function like($field, $value)
    {
        return $this->add($field, $value, self::LIKE);
    }

    /**
     * Add a ilike criterion in the criteria
     *
     * @param   string $field The field name
     * @param   mixed  $value The value to search
     * @return  $this
     */
    public function ilike($field, $value)
    {
        return $this->add($field, $value, self::ILIKE);
    }

    /**
     * Add a not equal criterion in the criteria
     *
     * @param   string $field The field name
     * @param   mixed  $value The value to search
     * @return  $this
     */
    public function notEqual($field, $value)
    {
        return $this->add($field, $value, self::NOT_EQUAL);
    }

    /**
     * Add a in criterion in the criteria
     *
     * @param   string $field The field name
     * @param   mixed  $value The value to search
     * @return  $this
     */
    public function in($field, $value)
    {
        return $this->add($field, $value, self::IN);
    }

    /**
     * Add a not in criterion in the criteria
     *
     * @param   string $field The field name
     * @param   mixed  $value The value to search
     * @return  $this
     */
    public function notIn($field, $value)
    {
        return $this->add($field, $value, self::NOT_IN);
    }
}
