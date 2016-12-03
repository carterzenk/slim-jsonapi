<?php

namespace CarterZenk\JsonApi\Hydrator;

use CarterZenk\JsonApi\Exceptions\RelatedResourceNotFound;
use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\RelationshipHelperTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;
use WoohooLabs\Yin\JsonApi\Schema\ResourceIdentifier;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship as ToManyHydrator;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship as ToOneHydrator;

trait RelationshipHydratorTrait
{
    use RelationshipHelperTrait;

    /**
     * @inheritdoc
     * @throws RelationshipNotExists
     */
    public function getRelationshipHydrators(Model $model)
    {
        $hydrators = [];

        foreach ($model->getFillableRelationships() as $name) {
            $relation = $this->getRelation($name);

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
        $pointer = '/data/relationships/'.$this->getSlugCase($name);
        $source = ErrorSource::fromPointer($pointer);

        return new RelatedResourceNotFound($identifier, $source);
    }
}
