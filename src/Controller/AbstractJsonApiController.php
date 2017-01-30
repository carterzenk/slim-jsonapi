<?php

namespace CarterZenk\JsonApi\Controller;

use CarterZenk\JsonApi\Encoder\EncoderInterface;
use CarterZenk\JsonApi\Exceptions\BadRequest;
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
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

abstract class AbstractJsonApiController implements JsonApiControllerInterface
{
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NO_CONTENT = 204;

    /**
     * @var string[]
     */
    protected $builderColumns = ['*'];

    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * @var ExceptionFactoryInterface
     */
    protected $exceptionFactory;

    /**
     * @var FetchingBuilderInterface
     */
    protected $fetchingBuilder;

    /**
     * @var ModelHydrator
     */
    protected $modelHydrator;

    /**
     * AbstractJsonApiController constructor.
     * @param EncoderInterface $encoder
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param FetchingBuilderInterface $fetchingBuilder
     * @param ModelHydrator $modelHydrator
     */
    public function __construct(
        EncoderInterface $encoder,
        ExceptionFactoryInterface $exceptionFactory,
        FetchingBuilderInterface $fetchingBuilder,
        ModelHydrator $modelHydrator
    ) {
        $this->encoder = $encoder;
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
     * @throws InvalidDomainObjectException
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
     * @param Model $model
     * @return Model
     * @throws BadRequest
     */
    protected function saveModel(Model $model)
    {
        try {
            $model->getConnection()->transaction(function() use ($model) {
                return $model->push();
            });

            return $model->fresh();
        } catch (\Exception $e) {
            throw $this->exceptionFactory->createBadRequestException();
        }
    }

    /**
     * @inheritdoc
     */
    public function listResourceAction(RequestInterface $request, ResponseInterface $response)
    {
        $builder = $this->fetchingBuilder->applyQueryParams(
            $this->getBuilder(),
            $request
        );

        $results = $this->fetchingBuilder->paginate(
            $builder,
            $request->getPageBasedPagination(1, 20),
            $this->builderColumns
        );

        $response = $this->encode($request, $response, $results);

        return $response->withStatus(self::OK);
    }

    /**
     * @inheritdoc
     */
    public function findResourceAction(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $id = $args['id'];

        $resource = $this->findResource($id);
        $response = $this->encode($request, $response, $resource);

        return $response->withStatus(self::OK);
    }

    /**
     * @inheritdoc
     */
    public function findRelationshipAction(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $id = $args['id'];
        $relationship = $args['relationship'];

        $relationMethodName = StringHelper::camelCase($relationship);

        $resource = $this->findRelationship($id, $relationship, $relationMethodName);
        $response = $this->encode($request, $response, $resource, $relationship);

        return $response->withStatus(self::OK);
    }

    /**
     * @inheritdoc
     */
    public function findRelatedResourceAction(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $id = $args['id'];
        $relationship = $args['relationship'];

        $relationMethodName = StringHelper::camelCase($relationship);

        $resource = $this->findRelatedResource($id, $relationship, $relationMethodName);
        $response = $this->encode($request, $response, $resource);

        return $response->withStatus(self::OK);
    }

    /**
     * @inheritdoc
     */
    public function createResourceAction(RequestInterface $request, ResponseInterface $response)
    {
        $resource = $this->hydrate($request, $this->createModel());
        $resource = $this->saveModel($resource);

        $response = $this->encode($request, $response, $resource);

        return $response->withStatus(self::CREATED);

    }

    /**
     * @inheritdoc
     */
    public function createRelationshipAction(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $id = $args['id'];
        $relationship = $args['relationship'];

        $relationMethodName = StringHelper::camelCase($relationship);

        $this->validateRelationshipIsFillable($relationMethodName);

        $resource = $this->findResource($id);
        $resource = $this->hydrate($request, $resource, $relationship);
        $resource = $this->saveModel($resource);

        $response = $this->encode($request, $response, $resource);

        return $response->withStatus(self::ACCEPTED);
    }

    /**
     * @inheritdoc
     */
    public function updateResourceAction(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $id = $args['id'];

        $resource = $this->findResource($id);
        $resource = $this->hydrate($request, $resource);
        $resource = $this->saveModel($resource);

        $response = $this->encode($request, $response, $resource);

        return $response->withStatus(self::ACCEPTED);
    }

    /**
     * @inheritdoc
     */
    public function updateRelationshipAction(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $id = $args['id'];
        $relationship = $args['relationship'];

        $relationMethodName = StringHelper::camelCase($relationship);

        $this->validateRelationshipIsFillable($relationMethodName);

        $resource = $this->findRelationship($id, $relationship, $relationMethodName);
        $resource = $this->hydrate($request, $resource, $relationship);
        $resource = $this->saveModel($resource);

        $response = $this->encode($request, $response, $resource);

        return $response->withStatus(self::ACCEPTED);
    }

    /**
     * @inheritdoc
     */
    public function deleteResourceAction(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $id = $args['id'];

        $resource = $this->findResource($id);
        $resource->delete();

        return $response->withStatus(self::NO_CONTENT);
    }

    /**
     * @inheritdoc
     */
    public function deleteRelationshipAction(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $id = $args['id'];
        $relationship = $args['relationship'];

        $relationMethodName = StringHelper::camelCase($relationship);

        $this->validateRelationshipIsFillable($relationMethodName);

        $resource = $this->findRelationship($id, $relationship, $relationMethodName);
        $resource = $this->hydrate($request, $resource, $relationship);
        $resource = $this->saveModel($resource);

        $response = $this->encode($request, $response, $resource, $relationMethodName);

        return $response->withStatus(self::OK);
    }

    /**
     * @param string $id
     * @return Model|\Illuminate\Database\Eloquent\Model
     * @throws ResourceNotFound
     */
    protected function findResource($id)
    {
        try {
            return $this->getBuilder()->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }
    }

    /**
     * @param string $id
     * @param string $relationship
     * @param string $relationMethodName
     * @return Model|\Illuminate\Database\Eloquent\Model
     * @throws RelationshipNotExists
     * @throws ResourceNotFound
     */
    protected function findRelationship($id, $relationship, $relationMethodName)
    {
        $this->validateRelationshipIsVisible($relationMethodName, $relationship);

        try {
            return $this->getBuilder()
                ->with($relationMethodName)
                ->findOrFail($id);
        } catch (RelationNotFoundException $exception) {
            throw new RelationshipNotExists($relationship);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }
    }

    /**
     * @param string $id
     * @param string $relationship
     * @param string $relationMethodName
     * @return Model|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     * @throws RelationshipNotExists
     * @throws ResourceNotFound
     */
    protected function findRelatedResource($id, $relationship, $relationMethodName)
    {
        $this->validateRelationshipIsVisible($relationMethodName, $relationship);

        try {
            $result = $this->getBuilder()
                ->with($relationMethodName)
                ->findOrFail($id);
        } catch (RelationNotFoundException $exception) {
            throw new RelationshipNotExists($relationship);
        } catch (ModelNotFoundException $exception) {
            throw new ResourceNotFound();
        }

        $relatedResource = $result->$relationMethodName;

        if (is_null($relatedResource)) {
            throw new ResourceNotFound();
        }

        return $relatedResource;
    }

    /**
     * @param string $relationMethodName
     * @param string $relationship
     * @throws RelationshipNotExists
     */
    private function validateRelationshipIsVisible($relationMethodName, $relationship)
    {
        if ($this->getModel()->isRelationshipVisible($relationMethodName) === false) {
            throw new RelationshipNotExists($relationship);
        }
    }

    /**
     * @param string $relationMethodName
     * @throws Forbidden
     */
    private function validateRelationshipIsFillable($relationMethodName)
    {
        if ($this->getModel()->isRelationshipFillable($relationMethodName) === false) {
            throw new Forbidden();
        }
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Model|null $resource
     * @param string|null $relationshipName
     * @return ResponseInterface
     */
    protected function encode(
        RequestInterface $request,
        ResponseInterface $response,
        $resource,
        $relationshipName = null
    ) {
        if (isset($relationshipName)) {
            return $this->encoder->encodeRelationship(
                $resource,
                $request,
                $response,
                $relationshipName
            );
        }

        return $this->encoder->encodeResource(
            $resource,
            $this->getModel()->newInstance(),
            $request,
            $response
        );
    }

    /**
     * @param RequestInterface $request
     * @param Model $resource
     * @param string|null $relationshipName
     * @return Model
     */
    protected function hydrate(RequestInterface $request, $resource, $relationshipName = null)
    {
        if (isset($relationshipName)) {
            return $this->modelHydrator->hydrateRelationship(
                $relationshipName,
                $request,
                $this->exceptionFactory,
                $resource
            );
        }

        return $this->modelHydrator->hydrate($request, $this->exceptionFactory, $resource);
    }
}
