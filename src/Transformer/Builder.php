<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\RelationshipHelperTrait;
use CarterZenk\JsonApi\Model\StringHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\AbstractRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class Builder implements BuilderInterface
{
    use RelationshipHelperTrait;
    use TypeTrait;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Relation[]
     */
    protected $relations;

    /**
     * @var LinksFactoryInterface
     */
    protected $linksFactory;

    /**
     * TransformerBuilder constructor.
     * @param Model $model
     * @param LinksFactoryInterface $linksFactory
     * @internal param string $baseUri
     */
    public function __construct(
        Model $model,
        LinksFactoryInterface $linksFactory
    ) {
        $this->model = $model;
        $this->linksFactory = $linksFactory;
        $this->relations = $this->getRelationMethods($model);
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

        $query = $this->model->newQueryWithoutScopes();

        foreach (array_keys($query->getEagerLoads()) as $includedRelationship) {
            $defaultIncludedRelationships[] = StringHelper::slugCase($includedRelationship);
        }

        return $defaultIncludedRelationships;
    }

    /**
     * @inheritdoc
     */
    public function getAttributesToHide()
    {
        $hiddenAttributes = $this->getForeignKeys();
        $hiddenAttributes[] = $this->getIdKey();

        return $hiddenAttributes;
    }

    /**
     * @return \string[]
     */
    protected function getForeignKeys()
    {
        $foreignKeys = [];

        foreach ($this->relations as $name => $relation) {
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
        $relationships = [];

        foreach ($this->getVisibleRelations() as $name) {
            $keyName = StringHelper::slugCase($name);

            $relationships[$keyName] = $this->getRelationshipCallable(
                $container,
                $this->relations[$name],
                $name,
                $keyName
            );
        }

        return $relationships;
    }

    /**
     * @return array
     */
    protected function getVisibleRelations()
    {
        $values = array_keys($this->relations);

        if (count($this->model->getVisible()) > 0) {
            $values = array_intersect_key($values, array_flip($this->model->getVisible()));
        }

        if (count($this->model->getHidden()) > 0) {
            $values = array_diff_key($values, array_flip($this->model->getHidden()));
        }

        return $values;
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
            $primaryTransformer = $container->get($domainObject);
            $relatedModel = $relation->getRelated()->newInstance();
            $relatedTransformer = $container->get($relatedModel);

            $relationship = $this->createRelationshipFromRelation($relation);

            // Links
            $links = $this->linksFactory->createRelationshipLinks(
                $keyName,
                $domainObject,
                $primaryTransformer
            );
            $relationship->setLinks($links);

            // Data
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
     * @param Relation $relation
     * @return AbstractRelationship
     */
    private function createRelationshipFromRelation(Relation $relation)
    {
        if ($this->isToOne($relation)) {
            return ToOneRelationship::create();
        } else {
            return ToManyRelationship::create();
        }
    }

    /**
     * @param AbstractRelationship $relationship
     * @param ResourceTransformerInterface $transformer
     * @param Model $model
     * @param string $name
     */
    private function setRelationshipData(
        AbstractRelationship &$relationship,
        ResourceTransformerInterface $transformer,
        Model $model,
        $name
    ) {
        if ($model->relationLoaded($name)) {
            $data = $model->$name;

            $relationship->setData($data, $transformer);
        } else {
            $dataCallable = function () use ($model, $name) {
                return $model->$name;
            };

            $relationship->setDataAsCallable($dataCallable, $transformer);
            $relationship->omitWhenNotIncluded();
        }
    }
}
