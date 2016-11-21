<?php

namespace CarterZenk\JsonApi\Document;

use WoohooLabs\Yin\JsonApi\Document\AbstractCollectionDocument;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class CollectionResourceDocument extends AbstractCollectionDocument
{
    use ResourceDocumentTrait;

    /**
     * CollectionResourceDocument constructor.
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
        return [
            'page' => [
                'number' => $this->domainObject->getPage(),
                'size' => $this->domainObject->getSize(),
                'total' => $this->domainObject->getTotalItems()
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getLinks()
    {
        $links = $this->createLinks();
        $links->setSelf(new Link($this->path));
        $links->setPagination($this->path, $this->domainObject);

        return $links;
    }
}
