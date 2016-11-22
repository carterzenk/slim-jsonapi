<?php

namespace CarterZenk\JsonApi\Controller;

use CarterZenk\JsonApi\Hydrator\HydratorInterface;
use CarterZenk\JsonApi\Model\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Str;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

trait JsonApiTrait
{
    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @var ExceptionFactoryInterface
     */
    protected $exceptionFactory;

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
     * @return callable
     * @codeCoverageIgnore
     */
    protected function indexResourceCallable()
    {
        return function (RequestInterface $request) {
            $pagination = $request->getPageBasedPagination(1, 20);


            $builder = $this->getBuilder();

            $builder = $this->applyFilters($builder, $request);
            $builder = $this->applySorting($builder, $request);
            $builder = $this->applyIncludes($builder, $request);

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
     * @param RequestInterface $request
     * @return Builder
     */
    protected function applyFilters(Builder $builder, RequestInterface $request)
    {
        $filters = $request->getFiltering();

        foreach ($filters as $filterKey => $filterValue) {
            $builder = $builder->where($filterKey, '=', $filterValue);
        }

        return $builder;
    }

    /**
     * @param Builder $builder
     * @param RequestInterface $request
     * @return Builder
     */
    protected function applySorting(Builder $builder, RequestInterface $request)
    {
        $sorting = $request->getSorting();

        foreach ($sorting as $sort) {
            $direction = substr($sort, 0, 1) == '-' ? 'DESC' : 'ASC';
            $column = str_replace('-', '', $sort);

            $builder = $builder->orderBy($column, $direction);
        }

        return $builder;
    }

    /**
     * @param Builder $builder
     * @param RequestInterface $request
     * @return Builder
     */
    protected function applyIncludes(Builder $builder, RequestInterface $request)
    {
        $includeQueryParam = $request->getQueryParam("include", "");
        if ($includeQueryParam === "") {
            return $builder;
        }

        $relationshipNames = explode(",", $includeQueryParam);
        foreach ($relationshipNames as $relationship) {
            $builder = $builder->with(Str::camel($relationship));
        }

        return $builder;
    }

    /**
     * Finds a resource.
     *
     * @param $id
     * @return callable
     */
    protected function findResourceCallable($id)
    {
        return function (RequestInterface $request) use ($id) {
            $model = $this->getBuilder()->find($id);

            if (is_null($model)) {
                throw $this->exceptionFactory->createResourceNotFoundException($request);
            }

            return $model;
        };
    }

    /**
     * Finds a relationship on a resource.
     *
     * @param int $id
     * @param string $relationship
     * @return callable
     */
    protected function findRelationshipCallable($id, $relationship)
    {
        return function (RequestInterface $request) use ($id, $relationship) {
            try {
                $model = $this->getBuilder()->with(Str::camel($relationship))->find($id);
            } catch (RelationNotFoundException $e) {
                throw $this->exceptionFactory->createRelationshipNotExists($relationship);
            }

            if (is_null($model)) {
                throw $this->exceptionFactory->createResourceNotFoundException($request);
            }

            return $model;
        };
    }

    /**
     * Creates a new instance of the model, hydrates, and saves.
     *
     * @return callable
     */
    protected function createResourceCallable()
    {
        return function (RequestInterface $request) {
            $model = $this->getModel()->newInstance();

            $model = $this->hydrate($model, $request);
            return $this->saveModel($model);
        };
    }

    /**
     * Retrieves the model, hydrates, and saves.
     *
     * @param string $id
     * @return callable
     */
    protected function updateResourceCallable($id)
    {
        return function (RequestInterface $request) use ($id) {
            $find = $this->findResourceCallable($id);
            $model = $find($request);

            $model = $this->hydrate($model, $request);
            return $this->saveModel($model);
        };
    }

    /**
     * Retrieves a model, checks relationship, hydrates, and saves.
     *
     * @param string $id
     * @param string $relationship
     * @return callable
     */
    protected function updateRelationshipCallalbe($id, $relationship)
    {
        return function (RequestInterface $request) use ($id, $relationship) {
            $find = $this->findRelationshipCallable($id, $relationship);
            $model = $find($request);

            $model = $this->hydrateRelationship($model, $relationship, $request);

            return $this->saveModel($model);
        };
    }

    /**
     * Deletes a model.
     *
     * @param $id
     * @return callable
     */
    protected function deleteResourceCallable($id)
    {
        return function (RequestInterface $request) use ($id) {
            $find = $this->findResourceCallable($id);
            $model = $find($request);

            return $model->delete();
        };
    }

    /**
     * @param $domainObject
     * @param RequestInterface $request
     * @return mixed
     */
    protected function hydrate($domainObject, RequestInterface $request)
    {
        return $this->hydrator->hydrate($request, $this->exceptionFactory, $domainObject);
    }

    /**
     * @param $domainObject
     * @param $relationshipName
     * @param RequestInterface $request
     * @return mixed
     */
    protected function hydrateRelationship($domainObject, $relationshipName, RequestInterface $request)
    {
        return $this->hydrator->hydrateRelationship(
            $relationshipName,
            $request,
            $this->exceptionFactory,
            $domainObject
        );
    }

    /**
     * @param Model $model
     * @return Model
     */
    private function saveModel(Model $model)
    {
        $model->save();
        return $model->fresh();
    }
}
