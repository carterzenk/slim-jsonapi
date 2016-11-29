<?php

namespace CarterZenk\JsonApi\Strategy\Filtering;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class ColumnEqualsValue
 *
 * This filtering strategy adds a where clause for each filter.
 * The operator is '=', the column is the array key, and the value
 * is the array value.
 *
 * @package CarterZenk\JsonApi\Strategy\Filtering
 */
class ColumnEqualsValue implements FilteringStrategyInterface
{
    /**
     * @inheritdoc
     */
    public function applyFilters(Builder $builder, array $filters)
    {
        foreach ($filters as $filterKey => $filterValue) {
            $builder = $builder->where($filterKey, '=', $filterValue);
        }

        return $builder;
    }
}
