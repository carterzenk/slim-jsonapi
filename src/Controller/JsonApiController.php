<?php

namespace CarterZenk\JsonApi\Controller;

use CarterZenk\JsonApi\Encoder\EncoderInterface;
use CarterZenk\JsonApi\Exceptions\ExceptionFactoryInterface;
use CarterZenk\JsonApi\Hydrator\HydratorInterface;
use CarterZenk\JsonApi\Strategy\Filtering\FilteringStrategyInterface;
use Psr\Http\Message\ResponseInterface;
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
     * @param FilteringStrategyInterface $filteringStrategy
     */
    public function __construct(
        EncoderInterface $encoder,
        ExceptionFactoryInterface $exceptionFactory,
        HydratorInterface $hydrator,
        FilteringStrategyInterface $filteringStrategy
    ) {
        $this->encoder = $encoder;
        $this->exceptionFactory = $exceptionFactory;
        $this->hydrator = $hydrator;
        $this->filteringStrategy = $filteringStrategy;
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

        return $this->respond(
            $findRelationship,
            $request,
            $response,
            self::OK,
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

        return $this->respond($updateRelationship, $request, $response, self::ACCEPTED, $relationshipName);
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
     * @param null $relationshipName
     * @return ResponseInterface
     */
    protected function respond(
        callable $resourceCallable,
        RequestInterface $request,
        ResponseInterface $response,
        $statusCode = self::OK,
        $relationshipName = null
    ) {
        $resource = $resourceCallable($request);

        if (isset($resource)) {
            if (isset($relationshipName)) {
                $response = $this->encoder->encodeRelationship($resource, $request, $response, $relationshipName);
            } else {
                $response = $this->encoder->encodeResource($resource, $request, $response);
            }
        }

        return $response->withStatus($statusCode);
    }
}
