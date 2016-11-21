<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\RelationshipParser;
use Illuminate\Support\Str;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Schema\Links;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class ModelTransformer
{
    /**
     * @param Model $model
     * @return null|string
     */
    public function getType(Model $model)
    {
        $resourceType = $model->getResourceType();

        if (isset($resourceType) && is_string($resourceType)) {
            return $resourceType;
        } else {
            // By default, use a slug-cased string representing the model's class.
            $reflection = new \ReflectionClass($model);
            return Str::slug(Str::snake($reflection->getShortName()));
        }
    }

    /**
     * @param Model $model
     * @return string
     */
    public function getId(Model $model)
    {
        return (string) $model->getKey();
    }

    /**
     * @param Model $model
     * @param Links $links
     * @return Links
     */
    public function getLinks(Model $model, Links $links)
    {
        $resourceType = Str::plural($this->getType($model));
        $resourceId = $this->getId($model);

        $links->setSelf(new Link('/'.$resourceType.'/'.$resourceId));

        return $links;
    }

    /**
     * @param Model $model
     * @return callable[]
     */
    public function getAttributes(Model $model)
    {
        $attributes = [];

        foreach ($model->attributesToArray() as $key => $value) {
            $attributes[$key] = function ($domainObject) use ($value) {
                return $value;
            };
        }

        return $attributes;
    }

    /**
     * @param Model $model
     * @return array
     */
    public function getDefaultIncludedRelationships(Model $model)
    {
        return $model->getDefaultIncludedRelationships();
    }

    /**
     * @param Model $model
     * @param ResourceTransformerInterface $transformer
     * @return callable[]
     */
    public function getRelationships(Model $model, ResourceTransformerInterface $transformer)
    {
        $relationshipParser = new RelationshipParser($model);
        return $relationshipParser->getRelationships($transformer);
    }
}
