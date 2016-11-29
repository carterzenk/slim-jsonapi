<?php

namespace CarterZenk\JsonApi\Strategy\Filtering;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class ColumnOperatorValue
 *
 * This filtering strategy adds a where clause for each filter.
 * The user can decide each column, operator, and value.
 *
 * @package CarterZenk\JsonApi\Strategy\Filtering
 */
class ColumnOperatorValue implements FilteringStrategyInterface
{
    /**
     * @var array
     */
    protected $operators = [
        'eq' => '=',
        'ne' => '!=',
        'gt' => '>',
        'ge' => '>=',
        'lt' => '<',
        'le' => '<=',
        'contains' => 'LIKE'
    ];

    /**
     * @inheritdoc
     */
    public function applyFilters(Builder $builder, array $filters)
    {
        foreach ($filters as $column => $operators) {
            foreach ($operators as $operator => $value) {
                $builder = $builder->where($column, $this->operators[$operator], $value);
            }
        }

        return $builder;
    }
}
