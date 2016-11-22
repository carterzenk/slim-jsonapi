<?php

namespace CarterZenk\JsonApi\Document;

use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

interface DocumentFactoryInterface
{
    /**
     * @param RequestInterface $request
     * @return SingleResourceDocument
     */
    public function createResourceDocument(RequestInterface $request);

    /**
     * @param RequestInterface $request
     * @return CollectionResourceDocument
     */
    public function createCollectionDocument(RequestInterface $request);

    public function createRelationshipDocument(RequestInterface $request, $relationshipName);
}
