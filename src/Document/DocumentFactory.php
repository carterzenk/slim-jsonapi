<?php

namespace CarterZenk\JsonApi\Document;

use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

class DocumentFactory implements DocumentFactoryInterface
{
    /**
     * @var string|null
     */
    private $jsonApiVersion;

    /**
     * DocumentFactory constructor.
     * @param string|null $jsonApiVersion
     */
    public function __construct($jsonApiVersion = null)
    {
        $this->jsonApiVersion = $jsonApiVersion;
    }

    /**
     * @inheritdoc
     */
    public function createResourceDocument(RequestInterface $request)
    {
        return new SingleResourceDocument($request, $this->jsonApiVersion);
    }

    /**
     * @inheritdoc
     */
    public function createCollectionDocument(RequestInterface $request)
    {
        return new CollectionResourceDocument($request, $this->jsonApiVersion);
    }
}
