<?php

namespace CarterZenk\JsonApi\Hydrator;

use CarterZenk\JsonApi\Exceptions\RelatedResourceNotFound;
use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\RelationshipHelperTrait;
use CarterZenk\JsonApi\Model\StringHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;
use WoohooLabs\Yin\JsonApi\Schema\ResourceIdentifier;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship as ToManyHydrator;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship as ToOneHydrator;

trait ModelHydratorTrait
{
    use RelationshipHelperTrait;

    /**
     * @param Model $model
     * @param $id
     * @return Model
     */
    public function setModelId(Model $model, $id)
    {
        return $model;
    }

    /**
     * @param Model $model
     * @return callable[]
     */
    public function getModelAttributeHydrator(Model $model)
    {
        $hydrators = [];

        foreach ($model->getFillable() as $fillableAttribute) {
            $hydrators[$fillableAttribute] = function (Model $model, $attribute, $data, $attributeName) {
                $model->setAttribute($attributeName, $attribute);
                return $model;
            };
        }

        return $hydrators;
    }

    /**
     * @param Model $model
     * @return callable[]
     * @throws RelationshipNotExists
     */
    public function getModelRelationshipHydrators(Model $model)
    {
        $hydrators = [];

        foreach ($model->getFillableRelationships() as $name) {
            $relation = $this->getRelation($model, $name);

            if ($this->isToOne($relation)) {
                $hydratorCallable = $this->getToOneHydratorCallable($name, $relation);
            } elseif ($this->isToMany($relation)) {
                $hydratorCallable = $this->getToManyHydratorCallable($name, $relation);
            }

            if (isset($hydratorCallable)) {
                $keyName = StringHelper::slugCase($name);
                $hydrators[$keyName] = $hydratorCallable;
            }
        }

        return $hydrators;
    }

    /**
     * @param $name
     * @param Relation $relation
     * @return callable
     */
    protected function getToOneHydratorCallable($name, Relation $relation)
    {
        return function (
            Model $model,
            ToOneHydrator $relationship
        ) use (
            $name,
            $relation
        ) {
            $resourceIdentifier = $relationship->getResourceIdentifier();

            try {
                $relatedModel = $relation
                    ->getRelated()
                    ->newQuery()
                    ->findOrFail($resourceIdentifier->getId());

                if ($relation instanceof HasOne) {
                    $model->$name()->save($relatedModel);
                } else {
                    $model->$name()->associate($relatedModel);
                }

                return $model;
            } catch (ModelNotFoundException $modelNotFoundException) {
                throw $this->createRelatedResourceNotExists($name, $resourceIdentifier);
            }
        };
    }

    /**
     * @param string $name
     * @param Relation $relation
     * @return callable
     */
    protected function getToManyHydratorCallable($name, Relation $relation)
    {
        return function (
            Model $model,
            ToManyHydrator $relationship
        ) use (
            $name,
            $relation
        ) {
            $relatedModels = $relation
                ->getRelated()
                ->newQuery()
                ->findMany($relationship->getResourceIdentifierIds());

            foreach ($relationship->getResourceIdentifiers() as $resourceIdentifier) {
                if (!$relatedModels->contains($resourceIdentifier->getId())) {
                    throw $this->createRelatedResourceNotExists($name, $resourceIdentifier);
                }
            }

            $model->$name()->saveMany($relatedModels);

            return $model;
        };
    }

    /**
     * @param string $name
     * @param ResourceIdentifier $identifier
     * @return RelatedResourceNotFound
     */
    private function createRelatedResourceNotExists($name, ResourceIdentifier $identifier)
    {
        $pointer = '/data/relationships/'.StringHelper::slugCase($name);
        $source = ErrorSource::fromPointer($pointer);

        return new RelatedResourceNotFound($identifier, $source);
    }
}
