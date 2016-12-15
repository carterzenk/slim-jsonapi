<?php

namespace CarterZenk\JsonApi\Transformer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use WoohooLabs\Yin\JsonApi\Transformer\AbstractResourceTransformer;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class ResourceTransformer extends AbstractResourceTransformer implements ResourceTransformerInterface
{
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
     * @var string[]
     */
    protected $hiddenAttributes;

    /**
     * @var string[]
     */
    protected $defaultIncludedRelationships;

    /**
     * @var string[]
     */
    protected $relationships;

    /**
     * @var LinksFactoryInterface
     */
    protected $linksFactory;

    /**
     * ResourceTransformer constructor.
     * @param string $type
     * @param string $idKey
     * @param array $hiddenAttributes
     * @param array $defaultIncludedRelationships
     * @param array $relationships
     * @param LinksFactoryInterface $linksFactory
     */
    public function __construct(
        $type,
        $idKey,
        array $hiddenAttributes,
        array $defaultIncludedRelationships,
        array $relationships,
        LinksFactoryInterface $linksFactory
    ) {
        $this->type = $type;
        $this->pluralType = Str::plural($type);
        $this->idKey = $idKey;
        $this->hiddenAttributes = $hiddenAttributes;
        $this->defaultIncludedRelationships = $defaultIncludedRelationships;
        $this->relationships = $relationships;
        $this->linksFactory = $linksFactory;
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
        return $domainObject->{$this->idKey};
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
        return $this->linksFactory->createResourceLinks(
            $this->pluralType,
            $this->getId($domainObject)
        );
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($domainObject)
    {
        if (!$domainObject instanceof Model) {
            return [];
        }

        return $this->getModelAttributes($domainObject);
    }

    /**
     * @param Model $model
     * @return array
     */
    protected function getModelAttributes(Model $model)
    {
        $attributes = [];

        foreach ($model->attributesToArray() as $key => $value) {
            if (in_array($key, $this->hiddenAttributes)) {
                continue;
            }

            $attributes[$key] = function ($domainObject) use ($value) {
                return $value;
            };
        }

        return $attributes;
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
