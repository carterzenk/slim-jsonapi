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

    /**
     *
     */
    const OK = 200;
    /**
     *
     */
    const CREATED = 201;
    /**
     *
     */
    const ACCEPTED = 202;
    /**
     *
     */
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

        return $this->respond($index, $request, $response);
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

        return $this->respond($find, $request, $response);
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
        $findRelationship = $this->findRelationshipCallable($args['id'], $relationshipName);

        return $this->respondWithRelationship(
            $findRelationship,
            $request,
            $response,
            $relationshipName
        );
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

        return $this->respond($create, $request, $response, self::CREATED);
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

        return $this->respond($update, $request, $response, self::ACCEPTED);
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

        return $this->respondWithRelationship(
            $updateRelationship,
            $request,
            $response,
            $relationshipName,
            self::ACCEPTED
        );
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

        return $this->respond($delete, $request, $response, self::NO_CONTENT);
    }

    /**
     * @param callable $resourceCallable
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param int $statusCode
     * @return ResponseInterface
     */
    protected function respond(
        callable $resourceCallable,
        RequestInterface $request,
        ResponseInterface $response,
        $statusCode = self::OK
    ) {
        $resource = $resourceCallable($request);

        if (isset($resource)) {
            $response = $this->encoder->encodeResource($resource, $request, $response);
        }

        return $response->withStatus($statusCode);
    }

    /**
     * @param callable $relationshipCallable
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param string $relationshipName
     * @param int $statusCode
     * @return ResponseInterface
     */
    protected function respondWithRelationship(
        callable $relationshipCallable,
        RequestInterface $request,
        ResponseInterface $response,
        $relationshipName,
        $statusCode = self::OK
    ) {
        $resource = $relationshipCallable($request);

        if (isset($resource)) {
            $response = $this->encoder->encodeRelationship($resource, $request, $response, $relationshipName);
        }

        return $response->withStatus($statusCode);
    }
}
