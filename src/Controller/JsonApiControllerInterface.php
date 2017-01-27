<?php

namespace CarterZenk\JsonApi\Controller;


use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

interface JsonApiControllerInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function listResourceAction(RequestInterface $request, ResponseInterface $response);

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function findResourceAction(RequestInterface $request, ResponseInterface $response, array $args);

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function findRelationshipAction(RequestInterface $request, ResponseInterface $response, array $args);

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function findRelatedResourceAction(RequestInterface $request, ResponseInterface $response, array $args);

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function createResourceAction(RequestInterface $request, ResponseInterface $response);

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function createRelationshipAction(RequestInterface $request, ResponseInterface $response, array $args);

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function updateResourceAction(RequestInterface $request, ResponseInterface $response, array $args);

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function updateRelationshipAction(RequestInterface $request, ResponseInterface $response, array $args);

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function deleteResourceAction(RequestInterface $request, ResponseInterface $response, array $args);

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return ResponseInterface
     */
    public function deleteRelationshipAction(RequestInterface $request, ResponseInterface $response, array $args);
}
