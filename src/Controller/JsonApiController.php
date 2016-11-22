<?php

namespace CarterZenk\JsonApi\Controller;

use CarterZenk\JsonApi\Encoder\EncoderInterface;
use CarterZenk\JsonApi\Hydrator\HydratorInterface;
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

abstract class JsonApiController
{
    use JsonApiTrait;

    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NO_CONTENT = 204;

    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * @param EncoderInterface $encoder
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param HydratorInterface $hydrator
     */
    public function __construct(
        EncoderInterface $encoder,
        ExceptionFactoryInterface $exceptionFactory,
        HydratorInterface $hydrator
    ) {
        $this->encoder = $encoder;
        $this->exceptionFactory = $exceptionFactory;
        $this->hydrator = $hydrator;
    }

    /**
     * Get many resources.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function indexResourceAction(RequestInterface $request, ResponseInterface $response)
    {
        $index = $this->indexResourceCallable();
        $results = $index($request);

        $response = $this->encoder->encodeResource($results, $request, $response);

        return $response->withStatus(self::OK);
    }

    /**
     * Get single resource.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     * @throws \Exception
     */
    public function findResourceAction(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $find = $this->findResourceCallable($args['id']);
        $results = $find($request);
        $response = $this->encoder->encodeResource($results, $request, $response);

        return $response->withStatus(self::OK);
    }

    /**
     * Get a relationship on a resource.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function findRelationshipAction(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $relationshipName = $args['relationship'];
        $find = $this->findRelationshipCallable($args['id'], $relationshipName);
        $result = $find($request);

        $response = $this->encoder->encodeRelationship($result, $request, $response, $relationshipName);

        return $response->withStatus(self::OK);
    }

    /**
     * Create a resource.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     *
     */
    public function createResourceAction(RequestInterface $request, ResponseInterface $response)
    {
        $create = $this->createResourceCallable();
        $model = $create($request);

        $response = $this->encoder->encodeResource($model, $request, $response);

        return $response->withStatus(self::CREATED);
    }

    /**
     * Update a resource.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function updateResourceAction(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $update = $this->updateResourceCallable($args['id']);
        $model = $update($request);

        $response = $this->encoder->encodeResource($model, $request, $response);

        return $response->withStatus(self::ACCEPTED);
    }

    /**
     * Update a relationship.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function updateRelationshipAction(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $relationshipName = $args['relationship'];
        $updateRelationship = $this->updateRelationshipCallalbe($args['id'], $relationshipName);
        $model = $updateRelationship($request);

        $response = $this->encoder->encodeRelationship($model, $request, $response, $relationshipName);

        return $response->withStatus(self::ACCEPTED);
    }

    /**
     * Delete a resource.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function deleteResourceAction(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $delete = $this->deleteResourceCallable($args['id']);
        $delete($request);

        return $response->withStatus(self::NO_CONTENT);
    }
}
