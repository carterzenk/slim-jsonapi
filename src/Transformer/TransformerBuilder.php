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

class TransformerBuilder implements TransformerBuilderInterface
{
    use RelationshipHelperTrait;
    use TypeTrait;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $relationMethods;

    /**
     * @var ContainerInterface
     */
    protected $transformerContainer;

    /**
     * @var LinksFactoryInterface
     */
    protected $linksFactory;

    /**
     * TransformerBuilder constructor.
     * @param Model $model
     * @param ContainerInterface $transformerContainer
     * @param LinksFactoryInterface $linksFactory
     * @internal param string $baseUri
     */
    public function __construct(
        Model $model,
        ContainerInterface $transformerContainer,
        LinksFactoryInterface $linksFactory
    ) {
        $this->model = $model;
        $this->transformerContainer = $transformerContainer;
        $this->linksFactory = $linksFactory;
        $this->relationMethods = $this->getRelationMethods($model);
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

        foreach ($this->relationMethods as $name => $relation) {
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
                $this->relationMethods[$name],
                $name,
                $keyName
            );
        }

        return $relationships;
    }

    protected function getVisibleRelations()
    {
        $values = array_keys($this->relationMethods);

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

    private function createRelationshipFromRelation(Relation $relation)
    {
        if ($this->isToOne($relation)) {
            return ToOneRelationship::create();
        } elseif ($this->isToMany($relation)) {
            return ToManyRelationship::create();
        } else {
            throw new \InvalidArgumentException(
                'Relation of type '.get_class($relation).' is not supported.'
            );
        }
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
