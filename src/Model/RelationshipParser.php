<?php

namespace CarterZenk\JsonApi\Model;

use CarterZenk\JsonApi\Exceptions\RelationshipExistenceException;
use Guzzle\Tests\Service\Description\ParameterTest;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
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

        foreach ($model->getVisibleRelationships() as $name) {
            $this->visibleRelationships[$name] = $this->getRelation($name, $model);
        }

        foreach ($model->getFillableRelationships() as $name) {
            $this->fillableRelationships[$name] = $this->getRelation($name, $model);
        }

        $this->loadedRelationships = $model->getRelations();
    }

    /**
     * @param $name
     * @param Model $model
     * @return mixed
     * @throws RelationshipExistenceException
     */
    private function getRelation($name, Model $model)
    {
        if (!method_exists($model, $name)) {
            throw new RelationshipExistenceException($model, $name);
        }

        $relation = $model->$name();

        if ($relation instanceof Relation) {
            return $relation;
        } else {
            throw new RelationshipExistenceException($model, $name);
        }
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
            $keyName = Str::slug(Str::snake(ucwords($name)));

            if ($relation instanceof HasOne || $relation instanceof BelongsTo) {
                $relationshipCallable = $this->getToOneRelationshipCallable($name, $keyName, $transformer);
            } elseif ($relation instanceof HasMany || $relation instanceof BelongsToMany) {
                $relationshipCallable = $this->getToManyRelationshipCallable($name, $keyName, $transformer);
            }

            if (isset($relationshipCallable)) {
                $relationships[$keyName] = $relationshipCallable;
            }
        }

        return $relationships;
    }

    /**
     * @param string $name
     * @param string $keyName
     * @param ResourceTransformerInterface $transformer
     * @return \Closure
     */
    private function getToOneRelationshipCallable($name, $keyName, ResourceTransformerInterface $transformer)
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
    private function getToManyRelationshipCallable($name, $keyName, ResourceTransformerInterface $transformer)
    {
        return function ($domainObject) use ($name, $keyName, $transformer) {
            // TODO: Implement links.

            $dataCallable = function () use ($domainObject, $name) {
                return $domainObject->$name;
            };

            return ToManyRelationship::create()
                ->setDataAsCallable($dataCallable, $transformer)
                ->omitWhenNotIncluded();
        };
    }

    /**
     * @return array
     */
    public function getRelationshipHydrator()
    {
        $hydrators = [];

        foreach ($this->fillableRelationships as $name => $relation) {
            if ($relation instanceof HasOne || $relation instanceof BelongsTo) {
                $hydratorCallable = function (
                    Model $model,
                    ToOneHydrator $relationship,
                    $data,
                    $relationshipName
                ) use (
                    $name,
                    $relation
                ) {
                    $id = $relationship->getResourceIdentifier()->getId();
                    $relatedModel = $relation->getRelated()->newQuery()->findOrFail($id);
                    $model->$name()->associate($relatedModel);
                    return $model;
                };
            } elseif ($relation instanceof HasMany || $relation instanceof BelongsToMany) {
                $hydratorCallable = function (
                    Model $model,
                    ToManyHydrator $relationship,
                    $data,
                    $relationshipName
                ) use (
                    $name,
                    $relation
                ) {
                    $ids = $relationship->getResourceIdentifierIds();
                    foreach ($ids as $id) {
                        $relatedModel = $relation->getRelated()->newQuery()->findOrFail($id);
                        $model->$name()->save($relatedModel);
                    }
                    return $model;
                };
            }

            if (isset($hydratorCallable)) {
                $keyName = Str::slug(Str::snake(ucwords($name)));
                $hydrators[$keyName] = $hydratorCallable;
            }
        }

        return $hydrators;
    }
}
