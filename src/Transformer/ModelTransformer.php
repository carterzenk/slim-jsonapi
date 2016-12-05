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
        $relationshipParser = new RelationshipParser($model, null);
        $foreignKeys = $relationshipParser->getForeignKeys();

        foreach ($model->attributesToArray() as $key => $value) {
            if ($key === $model->getKeyName()) {
                continue;
            }

            if (in_array($key, $foreignKeys)) {
                continue;
            }

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
        $defaultIncludedRelationships = [];

        foreach ($model->getDefaultIncludedRelationships() as $includedRelationship) {
            $defaultIncludedRelationships[] = Str::slug(Str::snake(ucwords($includedRelationship)));
        }

        return $defaultIncludedRelationships;
    }

    /**
     * @param Model $model
     * @param ResourceTransformerInterface $transformer
     * @param null|string $baseUri
     * @return \callable[]
     */
    public function getRelationships(
        Model $model,
        ResourceTransformerInterface $transformer,
        $baseUri = null
    ) {
        $relationshipParser = new RelationshipParser($model, $baseUri);
        return $relationshipParser->getRelationships($transformer);
    }
}
