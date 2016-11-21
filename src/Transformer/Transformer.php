<?php

namespace CarterZenk\JsonApi\Transformer;

use WoohooLabs\Yin\JsonApi\Transformer\AbstractResourceTransformer;

class Transformer extends AbstractResourceTransformer
{
    use LinksTrait;

    /**
     * @var ModelTransformer
     */
    private $modelTransformer;

    /**
     * Transformer constructor.
     * @param string $baseUri
     */
    public function __construct($baseUri = null)
    {
        $this->baseUri = $baseUri;
        $this->modelTransformer = new ModelTransformer();
    }

    /**
     * @param string $baseUri
     */
    public function setBaseUri($baseUri)
    {
        $this->baseUri = $baseUri;
    }

    /**
     * @param mixed $domainObject
     * @return string
     */
    public function getType($domainObject)
    {
        return $this->modelTransformer->getType($domainObject);
    }

    /**
     * @inheritdoc
     */
    public function getId($domainObject)
    {
        return $this->modelTransformer->getId($domainObject);
    }

    /**
     * @inheritdoc
     */
    public function getMeta($domainObject)
    {
        // TODO: Figure out how to deal with meta.
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getLinks($domainObject)
    {
        $links = $this->createLinks();
        return $this->modelTransformer->getLinks($domainObject, $links);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($domainObject)
    {
        return $this->modelTransformer->getAttributes($domainObject);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultIncludedRelationships($domainObject)
    {
        return $this->modelTransformer->getDefaultIncludedRelationships($domainObject);
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($domainObject)
    {
        return $this->modelTransformer->getRelationships($domainObject, $this);
    }
}
