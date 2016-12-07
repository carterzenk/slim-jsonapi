<?php

namespace CarterZenk\JsonApi\Hydrator;

use CarterZenk\JsonApi\Exceptions\RelatedResourceNotFound;
use CarterZenk\JsonApi\Model\RelationshipHelperTrait;
use CarterZenk\JsonApi\Model\StringHelper;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
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
        $fillable = $model->getFillable();

        if (!empty($fillable)) {
            return $this->getAttributeHydratorFromFillable($fillable);
        } else {
            return $this->getAttributeHydratorFromGuarded($model);
        }
    }


    /**
     * @param array $fillable
     * @return callable[]
     */
    protected function getAttributeHydratorFromFillable(array $fillable)
    {
        $hydrators = [];

        foreach ($fillable as $fillableAttribute) {
            $hydrators[$fillableAttribute] = $this->getAttributeHydratorCallable();
        }

        return $hydrators;
    }

    /**
     * @param Model $model
     * @return callable[]
     */
    protected function getAttributeHydratorFromGuarded(Model $model)
    {
        $hydrators = [];

        $table = $model->getTable();
        $columns = Manager::schema()->getColumnListing($table);

        foreach ($columns as $column) {
            if ($model->isFillable($column)) {
                $hydrators[$column] = $this->getAttributeHydratorCallable();
            }
        }

        return $hydrators;
    }

    /**
     * @return callable
     */
    protected function getAttributeHydratorCallable()
    {
        return function (Model $model, $attribute, $data, $attributeName) {
            $model->setAttribute($attributeName, $attribute);
            return $model;
        };
    }


    /**
     * @param Model $model
     * @return callable[]
     * @throws RelationshipNotExists
     */
    public function getModelRelationshipHydrators(Model $model)
    {
        $hydrators = [];

        foreach ([] as $name) {
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
