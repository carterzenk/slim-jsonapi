<?php

namespace CarterZenk\JsonApi\Document;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface DocumentFactoryInterface
 * @package CarterZenk\JsonApi\Document
 */
interface DocumentFactoryInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return SingleResourceDocument
     */
    public function createResourceDocument(ServerRequestInterface $request);

    /**
     * @param ServerRequestInterface $request
     * @return CollectionResourceDocument
     */
    public function createCollectionDocument(ServerRequestInterface $request);
}
