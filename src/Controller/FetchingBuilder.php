<?php

namespace CarterZenk\JsonApi\Controller;

use CarterZenk\JsonApi\Model\Paginator;
use CarterZenk\JsonApi\Model\StringHelper;
use CarterZenk\JsonApi\Strategy\Filtering\FilteringStrategyInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use WoohooLabs\Yin\JsonApi\Request\Pagination\PageBasedPagination;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

class FetchingBuilder implements FetchingBuilderInterface
{
    /**
     * @var FilteringStrategyInterface
     */
    protected $filteringStrategy;

    /**
     * FetchingBuilder constructor.
     * @param FilteringStrategyInterface $filteringStrategy
     */
    public function __construct(FilteringStrategyInterface $filteringStrategy)
    {
        $this->filteringStrategy = $filteringStrategy;
    }

    /**
     * @param Builder $builder
     * @param RequestInterface $request
     * @return Builder
     */
    public function applyQueryParams(Builder $builder, RequestInterface $request)
    {
        $builder = $this->applyFiltering($builder, $request->getFiltering());
        $builder = $this->applySorting($builder, $request->getSorting());
        $builder = $this->applyIncludes($builder, $request->getQueryParam('include'));

        return $builder;
    }

    /**
     * @param Builder $builder
     * @param array $filters
     * @return Builder
     */
    protected function applyFiltering(Builder $builder, array $filters)
    {
        return $this->filteringStrategy->applyFilters($builder, $filters);
    }

    /**
     * @param Builder $builder
     * @param array $sorting
     * @return Builder
     */
    protected function applySorting(Builder $builder, array $sorting)
    {
        foreach ($sorting as $sort) {
            $direction = substr($sort, 0, 1) == '-' ? 'DESC' : 'ASC';
            $column = str_replace('-', '', $sort);

            $builder = $builder->orderBy($column, $direction);
        }

        return $builder;
    }

    /**
     * @param Builder $builder
     * @param string|null $included
     * @return Builder
     */
    protected function applyIncludes(Builder $builder, $included)
    {
        if ($included === null) {
            return $builder;
        }

        $relationshipNames = explode(",", $included);

        foreach ($relationshipNames as $relationship) {
            $builder = $this->applyIncludedRelationship($builder, $relationship);
        }

        return $builder;
    }

    /**
     * @param Builder $builder
     * @param string $relationshipName
     * @return Builder
     */
    public function applyIncludedRelationship(Builder $builder, $relationshipName)
    {
        return $builder->with(StringHelper::camelCase($relationshipName));
    }

    /**
     * @param Builder $builder
     * @param PageBasedPagination $pagination
     * @return Paginator
     */
    public function paginate(Builder $builder, PageBasedPagination $pagination)
    {
        $page = $pagination->getPage();
        $perPage = $pagination->getSize();

        $builder->toBase();

        $total = $builder->toBase()->getCountForPagination();

        $results = $total ? $builder->forPage($page, $perPage)->get() : new Collection;

        return new Paginator($results, $total, $perPage, $page);
    }
}
