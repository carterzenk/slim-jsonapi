<?php

namespace CarterZenk\JsonApi\Hydrator\Relationship;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship;

class BelongsToManyHydrator extends AbstractToManyHydrator
{
    /**
     * BelongsToManyHydrator constructor.
     * @param BelongsToMany $relation
     * @param $name
     */
    public function __construct(BelongsToMany $relation, $name)
    {
        $this->relation = $relation;
        $this->name = $name;
    }

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param ToManyRelationship $relationship
     */
    public function hydrateForCreate(ExceptionFactoryInterface $exceptionFactory, $relationship)
    {
        if ($relationship->isEmpty() === false) {
            $this->getRelatedModelsFromRelationship($relationship);
            $this->relation->syncWithoutDetaching($relationship->getResourceIdentifierIds());

        }
    }

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param ToManyRelationship $relationship
     */
    public function hydrateForUpdate(ExceptionFactoryInterface $exceptionFactory, $relationship)
    {
        if ($relationship->isEmpty()) {
            $this->relation->detach();
        } else {
            $this->relation->sync($relationship->getResourceIdentifierIds());
        }
    }

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param ToManyRelationship $relationship
     */
    public function hydrateForDelete(ExceptionFactoryInterface $exceptionFactory, $relationship)
    {
        if ($relationship->isEmpty() === false) {
            $this->relation->detach($relationship->getResourceIdentifierIds());
        }
    }
}
