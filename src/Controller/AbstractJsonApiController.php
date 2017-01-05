<?php

namespace CarterZenk\JsonApi\Controller;

use CarterZenk\JsonApi\Document\DocumentFactoryInterface;
use CarterZenk\JsonApi\Exceptions\ExceptionFactoryInterface;
use CarterZenk\JsonApi\Exceptions\Forbidden;
use CarterZenk\JsonApi\Exceptions\ResourceNotFound;
use CarterZenk\JsonApi\Hydrator\ModelHydrator;
use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\StringHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

abstract class AbstractJsonApiController implements JsonApiControllerInterface
{
    protected $documentFactory;

    protected $exceptionFactory;

    protected $fetchingBuilder;

    protected $modelHydrator;

    public function __construct(
        DocumentFactoryInterface $documentFactory,
        ExceptionFactoryInterface $exceptionFactory,
        FetchingBuilder $fetchingBuilder,
        ModelHydrator $modelHydrator
    ) {
        $this->documentFactory = $documentFactory;
        $this->exceptionFactory = $exceptionFactory;
        $this->fetchingBuilder = $fetchingBuilder;
        $this->modelHydrator = $modelHydrator;
    }

    /**
     * Returns an Eloquent Query Builder.
     *
     * @return Builder
     */
    protected abstract function getQueryForFetching();

    /**
     * Returns an Eloquent Model.
     *
     * @return Model
     */
    protected abstract function getModel();

    protected function beforeCreate(Model $model, RequestInterface $request)
    {
        return $model;
    }

    protected function beforeSave(Model $model)
    {
        return $model;
    }

    protected function afterSave(Model $model)
    {
        return $model;
    }

    public function listResourceAction(JsonApi $jsonApi)
    {
        $builder = $this->fetchingBuilder->applyQueryParams(
            $this->getQueryForFetching(),
            $jsonApi->getRequest()
        );

        $results = $this->fetchingBuilder->paginate(
            $builder,
            $jsonApi->getRequest()->getPageBasedPagination(1, 20)
        );

        $document = $this->createCollectionDocument($jsonApi);

        return $jsonApi->respond()->ok($document, $results);
    }

    public function findResourceAction(JsonApi $jsonApi, $id)
    {
        try {
            $result = $this->getQueryForFetching()->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }

        $document = $this->createResourceDocument($jsonApi, $result);

        return $jsonApi->respond()->ok($document, $result);
    }

    public function findRelationshipAction(JsonApi $jsonApi, $id, $relationship)
    {
        $relationMethodName = StringHelper::camelCase($relationship);

        if ($this->getModel()->isVisible($relationMethodName) === false) {
            throw new RelationshipNotExists($relationship);
        }

        $builder = $this->fetchingBuilder->applyIncludedRelationship($this->getQueryForFetching(), $relationship);

        try {
            $result = $builder->findOrFail($id);
        } catch (RelationNotFoundException $exception) {
            throw new RelationshipNotExists($relationship);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }

        $document = $this->createResourceDocument($jsonApi, $result);

        return $jsonApi->respondWithRelationship($relationship)->ok($document, $result);
    }

    public function findRelatedResourceAction(JsonApi $jsonApi, $id, $relationship)
    {
        $relationMethodName = StringHelper::camelCase($relationship);

        if ($this->getModel()->isVisible($relationMethodName) === false) {
            throw new RelationshipNotExists($relationship);
        }

        $builder = $this->fetchingBuilder->applyIncludedRelationship($this->getQueryForFetching(), $relationship);

        try {
            $result = $builder->findOrFail($id);
        } catch (RelationNotFoundException $exception) {
            throw new RelationshipNotExists($relationship);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }

        $relatedResource = $result->$relationMethodName;

        if (is_null($relatedResource)) {
            throw new ResourceNotFound();
        }

        $document = $this->createResourceDocument($jsonApi, $relatedResource);

        return $jsonApi->respond()->ok($document, $relatedResource);
    }

    public function createResourceAction(JsonApi $jsonApi)
    {
        $model = $this->getModel();

        $model = $this->beforeCreate($model, $jsonApi->getRequest());

        $model = $jsonApi->hydrate($this->modelHydrator, $model);

        $model = $this->beforeSave($model);

        $model->push();

        $model = $this->afterSave($model);

        $document = $this->createResourceDocument($jsonApi, $model);

        return $jsonApi->respond()->created($document, $model);

    }

    public function createRelatedResourceAction(JsonApi $jsonApi, $id, $relationship)
    {
        $relationMethodName = StringHelper::camelCase($relationship);
        $model = $this->getModel();

        if ($model->isVisible($relationMethodName) === false) {
            throw new RelationshipNotExists($relationship);
        }

        if ($model->isAssignable($relationMethodName) === false) {
            throw new Forbidden();
        }

        try {
            $result = $this->getQueryForFetching()->findOrFail($id);
        } catch (RelationNotFoundException $exception) {
            throw new RelationshipNotExists($relationship);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }

        $relation = $result->$relationMethodName();

        $relatedModel = $relation->getRelated();

        $relatedModel = $jsonApi->hydrate($this->modelHydrator, $relatedModel);

        $relation->save($relatedModel);

        $document = $this->createResourceDocument($jsonApi, $relatedModel);

        return $jsonApi->respond()->ok($document, $relatedModel);

    }

    public function createRelationshipAction(JsonApi $jsonApi, $id, $relationship)
    {
        try {
            $model = $this->getQueryForFetching()->findOrFail($id);
        } catch (RelationNotFoundException $exception) {
            throw new RelationshipNotExists($relationship);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }

        $model = $jsonApi->hydrateRelationship($relationship, $this->modelHydrator, $model);

        $this->beforeSave($model);

        $model->push();

        $this->afterSave($model);

        $document = $this->createResourceDocument($jsonApi, $model);

        return $jsonApi->respond()->created($document, $model);
    }

    public function updateResourceAction(JsonApi $jsonApi, $id)
    {
        try {
            $model = $this->getQueryForFetching()->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }

        $model = $jsonApi->hydrate($this->modelHydrator, $model);

        $this->beforeSave($model);

        $model->push();

        $this->afterSave($model);

        $document = $this->createResourceDocument($jsonApi, $model);

        return $jsonApi->respond()->ok($document, $model);
    }

    public function updateRelatedResourceAction(JsonApi $jsonApi, $id, $relationship)
    {
        $relationMethodName = StringHelper::camelCase($relationship);
        $model = $this->getModel();

        if ($model->isVisible($relationMethodName) === false) {
            throw new RelationshipNotExists($relationship);
        }

        if ($model->isAssignable($relationMethodName) === false) {
            throw new Forbidden();
        }

        $builder = $this->fetchingBuilder->applyIncludedRelationship($this->getQueryForFetching(), $relationship);

        try {
            $model = $builder->findOrFail($id);
        } catch (RelationNotFoundException $exception) {
            throw new RelationshipNotExists($relationship);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }

        $relatedResource = $model->$relationMethodName;

        if (is_null($relatedResource)) {
            throw new ResourceNotFound();
        }

        $model = $jsonApi->hydrate($this->modelHydrator, $model);

        $this->beforeSave($model);

        $model->push();

        $this->afterSave($model);

        $document = $this->createResourceDocument($jsonApi, $model);

        return $jsonApi->respond()->ok($document, $model);
    }

    public function updateRelationshipAction(JsonApi $jsonApi, $id, $relationship)
    {
        // TODO: Implement updateRelationshipAction() method.
    }

    public function deleteResourceAction(JsonApi $jsonApi, $id)
    {
        // TODO: Implement deleteResourceAction() method.
    }

    public function deleteRelatedResourceAction(JsonApi $jsonApi, $id, $relationship)
    {
        // TODO: Implement deleteRelatedResourceAction() method.
    }

    public function deleteRelationshipAction(JsonApi $jsonApi, $id, $relationship)
    {
        // TODO: Implement deleteRelationshipAction() method.
    }

    protected function createResourceDocument(JsonApi $jsonApi, Model $model)
    {
        return $this->documentFactory->createResourceDocument(
            $jsonApi->getRequest()->getUri(),
            $model
        );
    }

    protected function createCollectionDocument(JsonApi $jsonApi)
    {
        return $this->documentFactory->createCollectionDocument(
            $jsonApi->getRequest()->getUri(),
            $this->getModel()
        );
    }
}
