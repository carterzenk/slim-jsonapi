<?php

namespace CarterZenk\JsonApi\Hydrator\Relationship;

use Illuminate\Database\Eloquent\Relations\HasOne;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship;

class HasOneHydrator extends AbstractToOneHydrator
{
    /**
     * HasOneHydrator constructor.
     * @param HasOne $relation
     * @param $name
     */
    public function __construct(HasOne $relation, $name)
    {
        $this->relation = $relation;
        $this->name = $name;
    }

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param ToOneRelationship $relationship
     * @throws \Exception
     */
    protected function hydrateForCreate(ExceptionFactoryInterface $exceptionFactory, $relationship)
    {
        if ($relationship->isEmpty() === false) {
            $this->saveRelatedFromRelationship($relationship);
        }
    }

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param ToOneRelationship $relationship
     * @throws \Exception
     */
    protected function hydrateForUpdate(ExceptionFactoryInterface $exceptionFactory, $relationship)
    {
        if ($relationship->isEmpty() || $this->relation->getResults() !== null) {
            throw $exceptionFactory->createRemovalProhibitedException($this->name);
        } else {
            $this->saveRelatedFromRelationship($relationship);
        }
    }

    /**
     * @param ToOneRelationship $relationship
     */
    private function saveRelatedFromRelationship(ToOneRelationship $relationship)
    {
        $this->relation->save($this->getRelatedModelFromRelationship($relationship));
    }
}
