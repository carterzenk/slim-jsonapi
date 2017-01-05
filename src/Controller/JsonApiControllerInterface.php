<?php

namespace CarterZenk\JsonApi\Controller;


use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\JsonApi;

interface JsonApiControllerInterface
{
    /**
     * @param JsonApi $jsonApi
     * @return ResponseInterface
     */
    public function listResourceAction(JsonApi $jsonApi);

    /**
     * @param JsonApi $jsonApi
     * @param string $id
     * @return ResponseInterface
     */
    public function findResourceAction(JsonApi $jsonApi, $id);

    /**
     * @param JsonApi $jsonApi
     * @param string $id
     * @param string $relationship
     * @return ResponseInterface
     */
    public function findRelationshipAction(JsonApi $jsonApi, $id, $relationship);

    /**
     * @param JsonApi $jsonApi
     * @param string $id
     * @param string $relationship
     * @return ResponseInterface
     */
    public function findRelatedResourceAction(JsonApi $jsonApi, $id, $relationship);

    /**
     * @param JsonApi $jsonApi
     * @return ResponseInterface
     */
    public function createResourceAction(JsonApi $jsonApi);

    /**
     * @param JsonApi $jsonApi
     * @param string $id
     * @param string $relationship
     * @return ResponseInterface
     */
    public function createRelatedResourceAction(JsonApi $jsonApi, $id, $relationship);

    /**
     * @param JsonApi $jsonApi
     * @param string $id
     * @param string $relationship
     * @return ResponseInterface
     */
    public function createRelationshipAction(JsonApi $jsonApi, $id, $relationship);

    /**
     * @param JsonApi $jsonApi
     * @param string $id
     * @return ResponseInterface
     */
    public function updateResourceAction(JsonApi $jsonApi, $id);

    /**
     * @param JsonApi $jsonApi
     * @param string $id
     * @param string $relationship
     * @return ResponseInterface
     */
    public function updateRelatedResourceAction(JsonApi $jsonApi, $id, $relationship);

    /**
     * @param JsonApi $jsonApi
     * @param string $id
     * @param string $relationship
     * @return ResponseInterface
     */
    public function updateRelationshipAction(JsonApi $jsonApi, $id, $relationship);

    /**
     * @param JsonApi $jsonApi
     * @param string $id
     * @return ResponseInterface
     */
    public function deleteResourceAction(JsonApi $jsonApi, $id);

    /**
     * @param JsonApi $jsonApi
     * @param string $id
     * @param string $relationship
     * @return ResponseInterface
     */
    public function deleteRelatedResourceAction(JsonApi $jsonApi, $id, $relationship);

    /**
     * @param JsonApi $jsonApi
     * @param string $id
     * @param string $relationship
     * @return ResponseInterface
     */
    public function deleteRelationshipAction(JsonApi $jsonApi, $id, $relationship);
}
