<?php

namespace CarterZenk\JsonApi\Document;

use CarterZenk\JsonApi\Transformer\ResourceTransformerInterface;
use CarterZenk\JsonApi\Transformer\Transformer;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

class DocumentFactory implements DocumentFactoryInterface
{
    /**
     * @var ResourceTransformerInterface
     */
    private $transformer;

    /**
     * @var string|null
     */
    private $jsonApiVersion;

    /**
     * DocumentFactory constructor.
     * @param ResourceTransformerInterface $transformer
     * @param string|null $jsonApiVersion
     */
    public function __construct(ResourceTransformerInterface $transformer = null, $jsonApiVersion = null)
    {
        $this->transformer = isset($transformer) ? $transformer : new Transformer();
        $this->jsonApiVersion = $jsonApiVersion;
    }

    /**
     * @inheritdoc
     */
    public function createResourceDocument(RequestInterface $request)
    {
        return new SingleResourceDocument($this->transformer, $request, $this->jsonApiVersion);
    }

    /**
     * @inheritdoc
     */
    public function createCollectionDocument(RequestInterface $request)
    {
        return new CollectionResourceDocument($this->transformer, $request, $this->jsonApiVersion);
    }
}
