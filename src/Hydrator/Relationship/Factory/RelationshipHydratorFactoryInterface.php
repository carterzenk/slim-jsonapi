<?php

namespace CarterZenk\JsonApi\Hydrator\Relationship\Factory;

use Illuminate\Database\Eloquent\Relations\Relation;

interface RelationshipHydratorFactoryInterface
{
    /**
     * @param Relation $relation
     * @param $name
     * @return mixed
     */
    function createRelationshipHydrator(Relation $relation, $name);
}
