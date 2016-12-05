<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\RelationshipHelperTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

trait RelationshipBuilderTrait
{
    use LinksTrait;
    use RelationshipHelperTrait;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @param Model $model
     * @return \string[]
     */
    protected function getForeignKeys(Model $model)
    {
        $foreignKeys = [];

        foreach ($model->getVisibleRelationships() as $name) {
            $relation = $this->getRelation($model, $name);

            if ($relation instanceof BelongsTo) {
                $foreignKeys[] = $relation->getForeignKey();
            }
        }

        return $foreignKeys;
    }

    /**
     * @inheritdoc
     * @throws RelationshipNotExists
     */
    public function getRelationshipsFromModel(Model $model, Container $container)
    {
        $relationships = [];

        foreach ($model->getVisibleRelationships() as $name) {
            $relation = $this->getRelation($model, $name);
            $keyName = $this->getSlugCase($name);

            if ($this->isToOne($relation)) {
                $relationships[$keyName] = $this->getToOneRelationshipCallable(
                    $container,
                    $relation,
                    $name,
                    $keyName
                );
            } elseif ($this->isToMany($relation)) {
                $relationships[$keyName] = $this->getToManyRelationshipCallable(
                    $container,
                    $relation,
                    $name,
                    $keyName
                );
            }
        }

        return $relationships;
    }

    public function getToOneRelationshipCallable(
        ContainerInterface $container,
        Relation $relation,
        $name,
        $keyName
    ) {
        return function ($domainObject) use ($name, $keyName, $relation, $container) {
            $primaryTransformer = $container->getTransformer($domainObject);
            $relatedModel = $relation->getRelated()->newInstance();
            $relatedTransformer = $container->getTransformer($relatedModel);

            // Links
            $links = $this->getRelationshipLinks($primaryTransformer, $domainObject, $keyName);

            $relationship = ToOneRelationship::createWithLinks($links);

            // Data
            if ($this->isRelationshipLoaded($domainObject, $name)) {
                $relationship = $relationship->setData($domainObject->$name, $relatedTransformer);
            } else {
                $relationship = $relationship->setDataAsCallable(function () use ($domainObject, $name) {
                    return $domainObject->$name;
                }, $relatedTransformer);
            }

            return $relationship;
        };
    }

    /**
     * @param ContainerInterface $container
     * @param Relation $relation,
     * @param string $name
     * @param string $keyName
     * @return callable
     * @internal param ResourceTransformerInterface $transformer
     */
    protected function getToManyRelationshipCallable(
        ContainerInterface $container,
        Relation $relation,
        $name,
        $keyName
    ) {
        return function ($domainObject) use ($name, $keyName, $relation, $container) {
            $primaryTransformer = $container->getTransformer($domainObject);
            $relatedModel = $relation->getRelated()->newInstance();
            $relatedTransformer = $container->getTransformer($relatedModel);

            // Links
            $links = $this->getRelationshipLinks($primaryTransformer, $domainObject, $keyName);

            $relationship = ToManyRelationship::createWithLinks($links);

            // Data
            if ($this->isRelationshipLoaded($domainObject, $name)) {
                $relationship = $relationship->setData($domainObject->$name, $relatedTransformer);
            } else {
                $relationship = $relationship->setDataAsCallable(function () use ($domainObject, $name) {
                    return $domainObject->$name;
                }, $relatedTransformer);
            }

            return $relationship;
        };
    }

    /**
     * @param ResourceTransformerInterface $transformer
     * @param mixed $domainObject
     * @param string $keyName
     * @return \WoohooLabs\Yin\JsonApi\Schema\Links
     */
    private function getRelationshipLinks(
        ResourceTransformerInterface $transformer,
        $domainObject,
        $keyName
    ) {
        $pluralType = Str::plural($transformer->getType($domainObject));
        $modelId = $transformer->getId($domainObject);

        $links = $this->createLinks();

        $selfLink = new Link('/'.$pluralType.'/'.$modelId.'/relationships/'.$keyName);
        $links->setSelf($selfLink);

        $relatedLink = new Link('/'.$pluralType.'/'.$modelId.'/'.$keyName);
        $links->setRelated($relatedLink);

        return $links;
    }
}
