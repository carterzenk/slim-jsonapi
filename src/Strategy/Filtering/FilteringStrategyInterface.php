<?php

namespace CarterZenk\JsonApi\Strategy\Filtering;

use Illuminate\Database\Eloquent\Builder;

interface FilteringStrategyInterface
{
    /**
     * This function should apply an array of filters to an Eloquent Builder.
     *
     * @param Builder $builder
     * @param array $filters
     * @return Builder
     */
    public function applyFilters(Builder $builder, array $filters);
}
