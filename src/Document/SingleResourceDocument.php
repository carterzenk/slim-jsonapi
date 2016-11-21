<?php

namespace CarterZenk\JsonApi\Document;

use WoohooLabs\Yin\JsonApi\Document\AbstractSingleResourceDocument;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class SingleResourceDocument extends AbstractSingleResourceDocument
{
    use ResourceDocumentTrait;

    /**
     * SingleResourceDocument constructor.
     * @param ResourceTransformerInterface $transformer
     * @param string $path
     * @param string|null $baseUri
     */
    public function __construct(ResourceTransformerInterface $transformer, $path, $baseUri = null)
    {
        $this->path = $path;
        $this->baseUri = $baseUri;

        parent::__construct($transformer);
    }

    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getLinks()
    {
        $links = $this->createLinks();
        $links->setSelf(new Link($this->path));

        return $links;
    }
}
