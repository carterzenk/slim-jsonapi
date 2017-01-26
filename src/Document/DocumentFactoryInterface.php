<?php

namespace CarterZenk\JsonApi\Document;

use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

interface DocumentFactoryInterface
{
    /**
     * @param RequestInterface $request
     * @param string $modelClass
     * @return SingleResourceDocument
     */
    public function createResourceDocument(RequestInterface $request, $modelClass);

    /**
     * @param RequestInterface $request
     * @param string $modelClass
     * @return CollectionResourceDocument
     */
    public function createCollectionDocument(RequestInterface $request, $modelClass);
}
