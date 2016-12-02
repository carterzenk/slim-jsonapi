<?php

namespace CarterZenk\JsonApi\Transformer;

use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Transformer\AbstractResourceTransformer;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class ResourceTransformer extends AbstractResourceTransformer implements ResourceTransformerInterface
{
    use LinksTrait;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $pluralType;

    /**
     * @var string
     */
    protected $idKey;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var array
     */
    protected $defaultIncludedRelationships;

    /**
     * @var array
     */
    protected $relationships;

    /**
     * ResourceTransformer constructor.
     * @param string $type
     * @param string $pluralType
     * @param string $idKey
     * @param string|null $baseUri
     * @param array $attributes
     * @param array $defaultIncludedRelationships
     * @param array $relationships
     */
    public function __construct(
        $type,
        $pluralType,
        $idKey,
        $baseUri,
        array $attributes,
        array $defaultIncludedRelationships,
        array $relationships
    ) {
        $this->type = $type;
        $this->pluralType = $pluralType;
        $this->idKey = $idKey;
        $this->baseUri = $baseUri;
        $this->attributes = $attributes;
        $this->defaultIncludedRelationships = $defaultIncludedRelationships;
        $this->relationships = $relationships;
    }

    /**
     * @inheritdoc
     */
    public function getType($domainObject)
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getId($domainObject)
    {
        return $domainObject->$this->idKey;
    }

    /**
     * @inheritdoc
     */
    public function getMeta($domainObject)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getLinks($domainObject)
    {
        $links = $this->createLinks();

        $resourceId = $this->getId($domainObject);
        $links->setSelf(new Link('/'.$this->pluralType.'/'.$resourceId));

        return $links;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($domainObject)
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultIncludedRelationships($domainObject)
    {
        return $this->defaultIncludedRelationships;
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($domainObject)
    {
        return $this->relationships;
    }
}
