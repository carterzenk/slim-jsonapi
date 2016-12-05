<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\RelationshipHelperTrait;
use CarterZenk\JsonApi\Model\StringHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\AbstractRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class Builder implements BuilderInterface
{
    use RelationshipHelperTrait;
    use LinksTrait;
    use TypeTrait;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * TransformerBuilder constructor.
     * @param Model $model
     * @param ContainerInterface $container
     * @param string $baseUri
     */
    public function __construct(Model $model, ContainerInterface $container, $baseUri)
    {
        $this->model = $model;
        $this->container = $container;
        $this->baseUri = $baseUri;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->getModelType($this->model);
    }

    /**
     * @inheritdoc
     */
    public function getIdKey()
    {
        return $this->model->getKeyName();
    }

    /**
     * @inheritdoc
     */
    public function getDefaultIncludedRelationships()
    {
        $defaultIncludedRelationships = [];

        foreach ($this->model->getDefaultIncludedRelationships() as $includedRelationship) {
            $defaultIncludedRelationships[] = StringHelper::slugCase($includedRelationship);
        }

        return $defaultIncludedRelationships;
    }

    /**
     * @inheritdoc
     */
    public function getAttributesToHide()
    {
        $hiddenAttributes = $this->getForeignKeys($this->model);
        $hiddenAttributes[] = $this->getIdKey();

        return $hiddenAttributes;
    }

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
     */
    public function getRelationshipsTransformer(ContainerInterface $container)
    {
        return $this->getRelationshipsFromModel($this->model, $container);
    }

    /**
     * @param Model $model
     * @param ContainerInterface $container
     * @returns callable[]
     * @throws RelationshipNotExists
     */
    protected function getRelationshipsFromModel(Model $model, ContainerInterface $container)
    {
        $relationships = [];

        foreach ($model->getVisibleRelationships() as $name) {
            $relation = $this->getRelation($model, $name);
            $keyName = StringHelper::slugCase($name);

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
    protected function getRelationshipCallable(
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
