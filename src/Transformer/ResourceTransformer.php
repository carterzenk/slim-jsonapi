<?php

namespace CarterZenk\JsonApi\Transformer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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
     * ResourceTransformer constructor.
     * @param string $type
     * @param string $idKey
     * @param string|null $baseUri
     * @param array $hiddenAttributes
     * @param array $defaultIncludedRelationships
     * @param array $relationships
     */
    public function __construct(
        $type,
        $idKey,
        $baseUri,
        array $hiddenAttributes,
        array $defaultIncludedRelationships,
        array $relationships
    ) {
        $this->type = $type;
        $this->pluralType = Str::plural($type);
        $this->idKey = $idKey;
        $this->baseUri = $baseUri;
        $this->hiddenAttributes = $hiddenAttributes;
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
