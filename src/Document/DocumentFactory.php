<?php

namespace CarterZenk\JsonApi\Document;

use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\UriInterface;

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
    public function createResourceDocument(UriInterface $uri, Model $model)
    {
        return new SingleResourceDocument($uri, $model, $this->jsonApiVersion);
    }

    /**
     * @inheritdoc
     */
    public function createCollectionDocument(UriInterface $uri, Model $model)
    {
        return new CollectionResourceDocument($uri, $model, $this->jsonApiVersion);
    }
}
