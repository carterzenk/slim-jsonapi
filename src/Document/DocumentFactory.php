<?php

namespace CarterZenk\JsonApi\Document;

use CarterZenk\JsonApi\Model\Model;
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
    public function createResourceDocument(Model $model, RequestInterface $request)
    {
        return new SingleResourceDocument($model, $request, $this->jsonApiVersion);
    }

    /**
     * @inheritdoc
     */
    public function createCollectionDocument(Model $model, RequestInterface $request)
    {
        return new CollectionResourceDocument($model, $request, $this->jsonApiVersion);
    }
}
