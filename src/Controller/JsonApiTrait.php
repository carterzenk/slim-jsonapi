<?php

namespace CarterZenk\JsonApi\Controller;

use CarterZenk\JsonApi\Model\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use WoohooLabs\Yin\JsonApi\Request\Pagination\PageBasedPagination;

trait JsonApiTrait
{
    /**
     * Returns an Eloquent Query Builder.
     *
     * @return Builder
     */
    abstract public function getBuilder();

    /**
     * Returns an Eloquent Model.
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->getBuilder()->getModel();
    }

    /**
     * Returns a list of resources based on pagination criteria.
     *
     * @param array|PageBasedPagination $pagination
     * @param array $filters
     * @param array $sorting
     * @return callable
     * @codeCoverageIgnore
     */
    protected function indexResourceCallable(PageBasedPagination $pagination, $filters, $sorting)
    {
        return function () use ($pagination, $filters, $sorting) {
            $builder = $this->getBuilder();

            $builder = $this->applyFilters($builder, $filters);
            $builder = $this->applySorting($builder, $sorting);

            $items = $builder->get();

            $pageSize = $pagination->getSize();
            $pageNumber = $pagination->getPage();

            return new Paginator(
                $items->forPage($pageNumber, $pageSize),
                $items->count(),
                $pageSize,
                $pageNumber
            );
        };
    }

    /**
     * @param Builder $builder
     * @param $filters
     * @return Builder
     */
    protected function applyFilters(Builder $builder, $filters)
    {
        foreach ($filters as $filterKey => $filterValue) {
            $builder = $builder->where($filterKey, '=', $filterValue);
        }

        return $builder;
    }

    /**
     * @param Builder $builder
     * @param $sorting
     * @return Builder
     */
    protected function applySorting(Builder $builder, $sorting)
    {
        foreach ($sorting as $sort) {
            $direction = substr($sort, 0, 1) == '-' ? 'DESC' : 'ASC';
            $column = str_replace('-', '', $sort);

            $builder = $builder->orderBy($column, $direction);
        }

        return $builder;
    }

    /**
     * @param $id
     *
     * @return callable
     * @codeCoverageIgnore
     */
    protected function findResourceCallable($id)
    {
        return function () use ($id) {
            return $this->getBuilder()->find($id);
        };
    }

    /**
     * @param int $id
     * @param string $relationship
     *
     * @return callable
     * @codeCoverageIgnore
     */
    protected function findRelationshipCallable($id, $relationship)
    {
        return function () use ($id, $relationship) {
            $model = $this->getBuilder()->find($id);

            // TODO: Implement this function to first check if the relationship exists, and then return results.

            return $model;
        };
    }

    /**
     * Creates a new instance of the model.
     *
     * @return callable
     * @codeCoverageIgnore
     */
    protected function createResourceCallable()
    {
        return function () {
            $model = $this->getModel()->newInstance();
            return $model;
        };
    }

    /**
     * @param $id
     *
     * @return callable
     * @codeCoverageIgnore
     */
    protected function deleteResourceCallable($id)
    {
        return function () use ($id) {
            $model = $this->getBuilder()->find($id);
            return $model->delete();
        };
    }
}
