<?php

namespace CarterZenk\JsonApi\Hydrator\Relationship;

use Illuminate\Database\Eloquent\Relations\HasMany;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship;

class HasManyHydrator extends AbstractToManyHydrator
{
    /**
     * HasManyHydrator constructor.
     * @param HasMany $relation
     * @param $name
     */
    public function __construct(HasMany $relation, $name)
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
            $relatedModels = $this->getRelatedModelsFromRelationship($relationship);
            $this->relation->saveMany($relatedModels);
        }
    }

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param ToManyRelationship $relationship
     * @throws \Exception
     */
    public function hydrateForUpdate(ExceptionFactoryInterface $exceptionFactory, $relationship)
    {
        throw $exceptionFactory->createFullReplacementProhibitedException($this->name);
    }

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param ToManyRelationship $relationship
     * @throws \Exception
     */
    public function hydrateForDelete(ExceptionFactoryInterface $exceptionFactory, $relationship)
    {
        throw $exceptionFactory->createRemovalProhibitedException($this->name);
    }
}
