<?php

namespace CarterZenk\JsonApi\Hydrator\Relationship;

use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

abstract class AbstractToManyHydrator extends AbstractRelationshipHydrator
{
    public function hydrate(RequestInterface $request, ExceptionFactoryInterface $exceptionFactory, $relationship)
    {
        parent::hydrate($request, $exceptionFactory, $relationship);

        if ($request->getMethod() === 'DELETE') {
            $this->hydrateForDelete($exceptionFactory, $relationship);
        }
    }

    protected abstract function hydrateForDelete(ExceptionFactoryInterface $exceptionFactory, $relationship);

    protected function getRelatedModelsFromRelationship(ToManyRelationship $relationship)
    {
        $relatedModels = $this->relation
            ->getRelated()
            ->newQuery()
            ->findMany($relationship->getResourceIdentifierIds());

        foreach ($relationship->getResourceIdentifiers() as $resourceIdentifier) {
            if ($relatedModels->find($resourceIdentifier->getId()) === null) {
                throw $this->createRelatedResourceNotExists($resourceIdentifier);
            }
        }

        return $relatedModels;
    }

    protected function validateRelationshipType(RequestInterface $request, ExceptionFactoryInterface $exceptionFactory, $relationship)
    {
        if ($relationship instanceof ToOneRelationship) {
            throw $exceptionFactory->createRelationshipTypeInappropriateException(
                $this->name,
                "to-one",
                "to-many"
            );
        }
    }
}
