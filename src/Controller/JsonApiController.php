<?php

namespace CarterZenk\JsonApi\Controller;

use CarterZenk\JsonApi\Document\DocumentFactoryInterface;
use CarterZenk\JsonApi\Hydrator\HydratorInterface;
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Exception\ResourceNotFound;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Serializer\SerializerInterface;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

abstract class JsonApiController
{
    use JsonApiTrait;

    /**
     * @var DocumentFactoryInterface
     */
    protected $documentFactory;

    /**
     * @var ExceptionFactoryInterface
     */
    protected $exceptionFactory;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ResourceTransformerInterface
     */
    protected $transformer;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * @param DocumentFactoryInterface $documentFactory
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param SerializerInterface $serializer
     * @param HydratorInterface $hydrator
     */
    public function __construct(
        DocumentFactoryInterface $documentFactory,
        ExceptionFactoryInterface $exceptionFactory,
        SerializerInterface $serializer,
        HydratorInterface $hydrator
    ) {
        $this->documentFactory = $documentFactory;
        $this->exceptionFactory = $exceptionFactory;
        $this->serializer = $serializer;
        $this->hydrator = $hydrator;
    }

    /**
     * Get many resources.
     *
     * @param JsonApi $jsonApi
     * @param array $args
     * @return ResponseInterface
     */
    public function indexResourceAction(JsonApi $jsonApi, array $args)
    {
        $request = $jsonApi->getRequest();

        $pagination = $request->getPageBasedPagination(1, 20);
        $filters = $request->getFiltering();
        $sorting = $request->getSorting();

        $index = $this->indexResourceCallable($pagination, $filters, $sorting);
        $results = $index();

        $document = $this->documentFactory->createCollectionDocument($request);

        return $jsonApi->respond()->ok($document, $results);
    }

    /**
     * Get single resource.
     *
     * @param JsonApi $jsonApi
     * @param array $args
     * @return ResponseInterface
     * @throws \Exception
     */
    public function findResourceAction(JsonApi $jsonApi, array $args)
    {
        $find = $this->findResourceCallable($args['id']);
        $results = $find();

        $request = $jsonApi->getRequest();

        if (is_null($results)) {
            throw $this->exceptionFactory->createResourceNotFoundException($request);
        }

        $document = $this->documentFactory->createResourceDocument($request);
        $response = $jsonApi->respond()->ok($document, $results);
        return $response;
    }

    /**
     * Get a relationship on a resource.
     *
     * @param JsonApi $jsonApi
     * @param array $args
     *
     * @return ResponseInterface
     */
    public function findRelationshipAction(JsonApi $jsonApi, array $args)
    {
        $id = $args['id'];
        $relationshipName = $args['relationship'];

        $find = $this->findResourceCallable($id);
        $result = $find();

        $request = $jsonApi->getRequest();
        $document = $this->documentFactory->createResourceDocument($request);

        return $jsonApi->respondWithRelationship($relationshipName)->ok($document, $result);
    }

    /**
     * Create a resource.
     *
     * @param JsonApi $jsonApi
     *
     * @return ResponseInterface
     */
    public function createResourceAction(JsonApi $jsonApi)
    {
        $create = $this->createResourceCallable();
        $model = $create();

        $model = $jsonApi->hydrate($this->hydrator, $model);
        $model->save();
        $model = $model->fresh();

        $request = $jsonApi->getRequest();
        $document = $this->documentFactory->createResourceDocument($request);

        return $jsonApi->respond()->created($document, $model);
    }

    /**
     * Update a resource.
     *
     * @param JsonApi $jsonApi
     * @param array $args
     *
     * @return ResponseInterface
     */
    public function updateResourceAction(JsonApi $jsonApi, array $args)
    {
        $id = $args['id'];

        $find = $this->findResourceCallable($id);
        $model = $find();

        $model = $jsonApi->hydrate($this->hydrator, $model);
        $model->save();
        $model = $model->fresh();

        $request = $jsonApi->getRequest();
        $document = $this->documentFactory->createResourceDocument($request);

        return $jsonApi->respond()->ok($document, $model);
    }

    /**
     * Update a relationship.
     *
     * @param JsonApi $jsonApi
     * @param array $args
     *
     * @return ResponseInterface
     */
    public function updateRelationshipAction(JsonApi $jsonApi, array $args)
    {
        $id = $args['id'];
        $relationshipName = $args['relationship'];

        $find = $this->findResourceCallable($id);
        $model = $find();

        $model = $jsonApi->hydrateRelationship(
            $relationshipName,
            $this->hydrator,
            $model
        );
        $model->save();
        $model = $model->fresh();

        $request = $jsonApi->getRequest();
        $document = $this->documentFactory->createResourceDocument($request);

        return $jsonApi->respond()->ok($document, $model);
    }

    /**
     * Delete a resource.
     *
     * @param JsonApi $jsonApi
     * @param array $args
     *
     * @return ResponseInterface
     */
    public function deleteResourceAction(JsonApi $jsonApi, array $args)
    {
        $id = $args['id'];

        $delete = $this->deleteResourceCallable($id);
        $delete();

        return $jsonApi->respond()->noContent();
    }
}
