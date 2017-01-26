<?php

namespace CarterZenk\JsonApi\Controller;

use CarterZenk\JsonApi\Document\DocumentFactoryInterface;
use CarterZenk\JsonApi\Exceptions\ExceptionFactoryInterface;
use CarterZenk\JsonApi\Exceptions\Forbidden;
use CarterZenk\JsonApi\Exceptions\InvalidDomainObjectException;
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
        FetchingBuilderInterface $fetchingBuilder,
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
    protected abstract function getBuilder();

    /**
     * Returns an Eloquent Model.
     * @return Model
     * @throws \CarterZenk\JsonApi\Exceptions\InvalidDomainObjectException
     */
    public function getModel()
    {
        $model = $this->getBuilder()->getModel();

        if ($model instanceof Model)
        {
            return $model;
        }

        throw new InvalidDomainObjectException($model);
    }

    public function listResourceAction(JsonApi $jsonApi)
    {
        $builder = $this->fetchingBuilder->applyQueryParams(
            $this->getBuilder(),
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
            $result = $this->getBuilder()->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }

        $document = $this->createResourceDocument($jsonApi, $result);

        return $jsonApi->respond()->ok($document, $result);
    }

    public function findRelationshipAction(JsonApi $jsonApi, $id, $relationship)
    {
        $relationMethodName = StringHelper::camelCase($relationship);

        if ($this->getModel()->isRelationshipVisible($relationMethodName) === false) {
            throw new RelationshipNotExists($relationship);
        }

        $builder = $this->fetchingBuilder->applyIncludedRelationship($this->getBuilder(), $relationship);

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

        if ($this->getModel()->isRelationshipVisible($relationMethodName) === false) {
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

        $model = $jsonApi->hydrate($this->modelHydrator, $model);
        $model->push();

        $document = $this->createResourceDocument($jsonApi, $model);

        return $jsonApi->respond()->created($document, $model);

    }

    public function createRelationshipAction(JsonApi $jsonApi, $id, $relationship)
    {
        $relationMethodName = StringHelper::camelCase($relationship);

        if ($this->getModel()->isRelationshipFillable($relationMethodName) === false) {
            throw new Forbidden();
        }

        try {
            $model = $this->getBuilder()->findOrFail($id);
        } catch (RelationNotFoundException $exception) {
            throw new RelationshipNotExists($relationship);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }

        $model = $jsonApi->hydrateRelationship($relationship, $this->modelHydrator, $model);
        $model->push();

        $document = $this->createResourceDocument($jsonApi, $model);

        return $jsonApi->respond()->created($document, $model);
    }

    public function updateResourceAction(JsonApi $jsonApi, $id)
    {
        try {
            $model = $this->getBuilder()->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }

        $model = $jsonApi->hydrate($this->modelHydrator, $model);
        $model->push();

        $document = $this->createResourceDocument($jsonApi, $model);

        return $jsonApi->respond()->ok($document, $model);
    }

    public function updateRelationshipAction(JsonApi $jsonApi, $id, $relationship)
    {
        $relationMethodName = StringHelper::camelCase($relationship);

        if ($this->getModel()->isRelationshipFillable($relationMethodName) === false) {
            throw new Forbidden();
        }

        try {
            $model = $this->getBuilder()->findOrFail($id);
        } catch (RelationNotFoundException $exception) {
            throw new RelationshipNotExists($relationship);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }

        $model = $jsonApi->hydrateRelationship($relationship, $this->modelHydrator, $model);
        $model->push();

        $document = $this->createResourceDocument($jsonApi, $model);

        return $jsonApi->respond()->ok($document, $model);
    }

    public function deleteResourceAction(JsonApi $jsonApi, $id)
    {
        try {
            $model = $this->getBuilder()->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }

        $model->delete();

        return $jsonApi->respond()->noContent();
    }

    public function deleteRelationshipAction(JsonApi $jsonApi, $id, $relationship)
    {
        $relationMethodName = StringHelper::camelCase($relationship);

        if ($this->getModel()->isRelationshipFillable($relationMethodName) === false) {
            throw new Forbidden();
        }

        try {
            $model = $this->getBuilder()->findOrFail($id);
        } catch (RelationNotFoundException $exception) {
            throw new RelationshipNotExists($relationship);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }

        $model = $jsonApi->hydrateRelationship($relationship, $this->modelHydrator, $model);
        $model->push();

        $document = $this->createResourceDocument($jsonApi, $model);

        return $jsonApi->respond()->ok($document, $model);
    }

    protected function createResourceDocument(JsonApi $jsonApi, Model $model)
    {
        return $this->documentFactory->createResourceDocument(
            $jsonApi->getRequest(),
            $model
        );
    }

    protected function createCollectionDocument(JsonApi $jsonApi)
    {
        return $this->documentFactory->createCollectionDocument(
            $jsonApi->getRequest(),
            $this->getModel()
        );
    }
}
