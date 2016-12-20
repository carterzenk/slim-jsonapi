<?php

namespace CarterZenk\JsonApi\Hydrator\Relationship;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship;

class BelongsToHydrator extends AbstractToOneHydrator
{
    /**
     * BelongsToHydrator constructor.
     * @param BelongsTo $relation
     * @param $name
     */
    public function __construct(BelongsTo $relation, $name)
    {
        $this->relation = $relation;
        $this->name = $name;
    }

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param ToOneRelationship $relationship
     */
    public function hydrateForCreate(ExceptionFactoryInterface $exceptionFactory, $relationship)
    {
        if ($relationship->isEmpty() === false) {
            $this->relation->associate($this->getRelatedModelFromRelationship($relationship));
        }
    }

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param ToOneRelationship $relationship
     */
    public function hydrateForUpdate(ExceptionFactoryInterface $exceptionFactory, $relationship)
    {
        if ($relationship->isEmpty()) {
            $this->relation->dissociate();
        } else {
            $relatedModel = $this->getRelatedModelFromRelationship($relationship);
            $this->relation->associate($relatedModel);
        }
    }
}
