<?php

namespace CarterZenk\JsonApi\Controller;

use CarterZenk\JsonApi\Exceptions\ExceptionFactoryInterface;
use CarterZenk\JsonApi\Hydrator\HydratorInterface;
use CarterZenk\JsonApi\Model\Paginator;
use CarterZenk\JsonApi\Transformer\Transformer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Str;
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
     * Returns the instance of a new model to hydrate.
     *
     * @return Model
     */
    public function createModel()
    {
        return $this->getModel()->newInstance();
    }

    /**
     * Returns a list of resources based on pagination criteria.
     *
     * @return callable
     */
    protected function indexResourceCallable()
    {
        return function (RequestInterface $request) {
            $builder = $this->getBuilder();
            $builder = $this->applyQueryParams($builder, $request);

            $items = $builder->get();

            $pagination = $request->getPageBasedPagination(1, 20);
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
     * Applies JSON-API query parameters to the builder.
     *
     * @param Builder $builder
     * @param RequestInterface $request
     * @return Builder
     */
    protected function applyQueryParams(Builder $builder, RequestInterface $request)
    {
        $filters = $request->getFiltering();
        $builder = $this->applyFilters($builder, $filters);

        $sorting = $request->getSorting();
        $builder = $this->applySorting($builder, $sorting);

        $included = $request->getQueryParam('include', '');
        if (is_string($included)) {
            $builder = $this->applyIncludes($builder, $included);
        }

        return $builder;
    }

    /**
     * Applies filtering to the builder.
     *
     * @param Builder $builder
     * @param array $filters
     * @return Builder
     */
    protected function applyFilters(Builder $builder, array $filters)
    {
        foreach ($filters as $filterKey => $filterValue) {
            $builder = $builder->where($filterKey, '=', $filterValue);
        }

        return $builder;
    }

    /**
     * Applies sorting to the builder.
     *
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
     * Applies includes to the builder.
     *
     * @param Builder $builder
     * @param string $included
     * @return Builder
     */
    protected function applyIncludes(Builder $builder, $included)
    {
        if ($included === "") {
            return $builder;
        }

        $relationshipNames = explode(",", $included);
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
                $transformer = new Transformer();
                $type = $transformer->getType($this->getModel());
                throw $this->exceptionFactory->createResourceNotExistsException($type, $id);
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
                $transformer = new Transformer();
                $type = $transformer->getType($this->getModel());
                throw $this->exceptionFactory->createResourceNotExistsException($type, $id);
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
            $model = $this->createModel();

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

            $model->delete();
        };
    }

    /**
     * Hydrates a model from the request.
     *
     * @param $domainObject
     * @param RequestInterface $request
     * @return mixed
     */
    protected function hydrate($domainObject, RequestInterface $request)
    {
        return $this->hydrator->hydrate($request, $this->exceptionFactory, $domainObject);
    }

    /**
     * Hydrates a relationship from the request.
     *
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
     * Saves the model and returns a fresh instance loaded from the database.
     *
     * @param Model $model
     * @return Model
     */
    protected function saveModel(Model $model)
    {
        $model->save();
        return $model->fresh();
    }
}
