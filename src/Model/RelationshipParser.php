<?php

namespace CarterZenk\JsonApi\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship as ToOneHydrator;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship as ToManyHydrator;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class RelationshipParser implements RelationshipParserInterface
{
    /**
     * @var string|null
     */
    private $baseUrl;

    /**
     * @var array
     */
    private $visibleRelationships = [];

    /**
     * @var array
     */
    private $fillableRelationships = [];

    /**
     * @var array
     */
    private $loadedRelationships = [];

    /**
     * RelationshipParser constructor.
     * @param Model $model
     * @param string|null $baseUrl
     */
    public function __construct(Model $model, $baseUrl = null)
    {
        $this->baseUrl = $baseUrl;
        $this->loadedRelationships = $model->getRelations();

        foreach ($model->getVisibleRelationships() as $name) {
            $this->visibleRelationships[$name] = $this->getRelation($name, $model);
        }

        foreach ($model->getFillableRelationships() as $name) {
            $this->fillableRelationships[$name] = $this->getRelation($name, $model);
        }
    }

    /**
     * @param $name
     * @param Model $model
     * @return mixed
     * @throws RelationshipNotExists
     */
    private function getRelation($name, Model $model)
    {
        if (!method_exists($model, $name)) {
            throw new RelationshipNotExists($this->getSlugCase($name));
        }

        $relation = $model->$name();

        if ($relation instanceof Relation) {
            return $relation;
        } else {
            throw new RelationshipNotExists($this->getSlugCase($name));
        }
    }

    private function getSlugCase($name)
    {
        return Str::slug(Str::snake(ucwords($name)));
    }

    /**
     * @param ResourceTransformerInterface $transformer
     * @return array
     * @throws \Exception
     */
    public function getRelationships(ResourceTransformerInterface $transformer)
    {
        $relationships = [];

        foreach ($this->visibleRelationships as $name => $relation) {
            $keyName = $this->getSlugCase($name);

            if ($this->isToOne($relation)) {
                $relationshipCallable = $this->getToOneRelationshipCallable($name, $keyName, $transformer);
            } elseif ($this->isToMany($relation)) {
                $relationshipCallable = $this->getToManyRelationshipCallable($name, $keyName, $transformer);
            }

            if (isset($relationshipCallable)) {
                $relationships[$keyName] = $relationshipCallable;
            }
        }

        return $relationships;
    }

    /**
     * @param Relation $relation
     * @return bool
     */
    private function isToOne(Relation $relation)
    {
        return $relation instanceof HasOne || $relation instanceof BelongsTo;
    }

    /**
     * @param Relation $relation
     * @return bool
     */
    private function isToMany(Relation $relation)
    {
        return $relation instanceof HasMany || $relation instanceof BelongsToMany;
    }

    /**
     * @param string $name
     * @param string $keyName
     * @param ResourceTransformerInterface $transformer
     * @return \Closure
     */
    protected function getToOneRelationshipCallable($name, $keyName, ResourceTransformerInterface $transformer)
    {
        // TODO: Implement links.
        return function ($domainObject) use ($name, $keyName, $transformer) {

            $data = $domainObject->$name;

            return ToOneRelationship::create()
                ->setData($data, $transformer);
        };
    }

    /**
     * @param $name
     * @param string $keyName
     * @param ResourceTransformerInterface $transformer
     * @return \Closure
     */
    protected function getToManyRelationshipCallable($name, $keyName, ResourceTransformerInterface $transformer)
    {
        return function ($domainObject) use ($name, $keyName, $transformer) {
            // TODO: Implement links.
            $relationship = ToManyRelationship::create();

            if ($this->isLoaded($name)) {
                $data = $domainObject->$name;
                $relationship->setData($data, $transformer);
            } else {
                $dataCallable = function () use ($domainObject, $name) {
                    return $domainObject->$name;
                };

                $relationship->setDataAsCallable($dataCallable, $transformer);
                $relationship->omitWhenNotIncluded();
            }

            return $relationship;
        };
    }

    /**
     * @param $relationship
     * @return bool
     */
    private function isLoaded($relationship)
    {
        return array_key_exists($relationship, $this->loadedRelationships);
    }

    /**
     * @return array
     */
    public function getRelationshipHydrator()
    {
        $hydrators = [];

        foreach ($this->fillableRelationships as $name => $relation) {
            if ($this->isToOne($relation)) {
                $hydratorCallable = $this->getToOneHydratorCallable($name, $relation);
            } elseif ($this->isToMany($relation)) {
                $hydratorCallable = $this->getToManyHydratorCallable($name, $relation);
            }

            if (isset($hydratorCallable)) {
                $keyName = $this->getSlugCase($name);
                $hydrators[$keyName] = $hydratorCallable;
            }
        }

        return $hydrators;
    }

    protected function getToOneHydratorCallable($name, Relation $relation)
    {
        return function (
            Model $model,
            ToOneHydrator $relationship
        ) use (
            $name,
            $relation
        ) {
            $id = $relationship->getResourceIdentifier()->getId();
            $relatedModel = $relation->getRelated()->newQuery()->findOrFail($id);
            $model->$name()->associate($relatedModel);

            return $model;
        };
    }

    protected function getToManyHydratorCallable($name, Relation $relation)
    {
        return function (
            Model $model,
            ToManyHydrator $relationship
        ) use (
            $name,
            $relation
        ) {
            foreach ($relationship->getResourceIdentifierIds() as $id) {
                $relatedModel = $relation->getRelated()->newQuery()->findOrFail($id);
                $model->$name()->save($relatedModel);
            }

            return $model;
        };
    }
}
