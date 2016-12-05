<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\RelationshipHelperTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\AbstractRelationship;
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
     * @param Model $model
     * @param ContainerInterface $container
     * @returns callable[]
     * @throws RelationshipNotExists
     */
    public function getRelationshipsFromModel(Model $model, ContainerInterface $container)
    {
        $relationships = [];

        foreach ($model->getVisibleRelationships() as $name) {
            $relation = $this->getRelation($model, $name);
            $keyName = $this->getSlugCase($name);

            $relationships[$keyName] = $this->getRelationshipCallable(
                $container,
                $relation,
                $name,
                $keyName
            );
        }

        return $relationships;
    }

    /**
     * @param ContainerInterface $container
     * @param Relation $relation
     * @param string $name
     * @param string $keyName
     * @returns callable
     * @throws RelationshipNotExists
     */
    public function getRelationshipCallable(
        ContainerInterface $container,
        Relation $relation,
        $name,
        $keyName
    ) {
        return function ($domainObject) use ($name, $keyName, $relation, $container) {
            $primaryTransformer = $container->getTransformer($domainObject);
            $relatedModel = $relation->getRelated()->newInstance();
            $relatedTransformer = $container->getTransformer($relatedModel);

            if ($this->isToOne($relation)) {
                $relationship = ToOneRelationship::create();
            } elseif ($this->isToMany($relation)) {
                $relationship = ToManyRelationship::create();
            }

            $this->setRelationshipLinks(
                $relationship,
                $primaryTransformer,
                $domainObject,
                $keyName
            );

            $this->setRelationshipData(
                $relationship,
                $relatedTransformer,
                $domainObject,
                $name
            );

            return $relationship;
        };
    }

    /**
     * @param AbstractRelationship $relationship
     * @param ResourceTransformerInterface $transformer
     * @param mixed $domainObject
     * @param string $keyName
     */
    private function setRelationshipLinks(
        AbstractRelationship &$relationship,
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

        $relationship->setLinks($links);
    }

    /**
     * @param AbstractRelationship $relationship
     * @param ResourceTransformerInterface $transformer
     * @param mixed $domainObject
     * @param string $name
     */
    private function setRelationshipData(
        AbstractRelationship &$relationship,
        ResourceTransformerInterface $transformer,
        $domainObject,
        $name
    ) {
        if ($this->isRelationshipLoaded($domainObject, $name)) {
            $data = $domainObject->$name;

            $relationship->setData($data, $transformer);
        } else {
            $dataCallable = function () use ($domainObject, $name) {
                return $domainObject->$name;
            };

            $relationship->setDataAsCallable($dataCallable, $transformer);
            $relationship->omitWhenNotIncluded();
        }
    }
}
