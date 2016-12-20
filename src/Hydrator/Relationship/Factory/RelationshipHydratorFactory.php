<?php

namespace CarterZenk\JsonApi\Hydrator\Relationship\Factory;

use CarterZenk\JsonApi\Exceptions\InvalidDomainObjectException;
use CarterZenk\JsonApi\Hydrator\Relationship\RelationshipHydratorInterface;
use CarterZenk\JsonApi\Hydrator\Relationship\BelongsToHydrator;
use CarterZenk\JsonApi\Hydrator\Relationship\BelongsToManyHydrator;
use CarterZenk\JsonApi\Hydrator\Relationship\HasManyHydrator;
use CarterZenk\JsonApi\Hydrator\Relationship\HasOneHydrator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;

class RelationshipHydratorFactory implements RelationshipHydratorFactoryInterface
{
    /**
     * @param Relation $relation
     * @param $name
     * @return RelationshipHydratorInterface
     */
    public function createRelationshipHydrator(Relation $relation, $name)
    {
        if ($relation instanceof HasOne) {
            return $this->createHasOneHydrator($relation, $name);
        }

        if ($relation instanceof HasMany) {
            return $this->createHasManyHydrator($relation, $name);
        }

        if ($relation instanceof BelongsTo) {
            return $this->createBelongsToHydrator($relation, $name);
        }

        if ($relation instanceof BelongsToMany) {
            return $this->createBelongsToManyHydrator($relation, $name);
        }

        return null;
    }

    /**
     * @param HasOne $relation
     * @param $name
     * @return HasOneHydrator
     */
    protected function createHasOneHydrator(HasOne $relation, $name)
    {
        return new HasOneHydrator($relation, $name);
    }

    /**
     * @param HasMany $relation
     * @param $name
     * @return HasManyHydrator
     */
    protected function createHasManyHydrator(HasMany $relation, $name)
    {
        return new HasManyHydrator($relation, $name);
    }

    /**
     * @param BelongsTo $relation
     * @param $name
     * @return BelongsToHydrator
     */
    protected function createBelongsToHydrator(BelongsTo $relation, $name)
    {
        return new BelongsToHydrator($relation, $name);
    }

    /**
     * @param BelongsToMany $relation
     * @param $name
     * @return BelongsToManyHydrator
     */
    protected function createBelongsToManyHydrator(BelongsToMany $relation, $name)
    {
        return new BelongsToManyHydrator($relation, $name);
    }
}
