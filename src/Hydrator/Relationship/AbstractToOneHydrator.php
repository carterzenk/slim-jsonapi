<?php

namespace CarterZenk\JsonApi\Hydrator\Relationship;

use CarterZenk\JsonApi\Exceptions\MethodNotAllowed;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

abstract class AbstractToOneHydrator extends AbstractRelationshipHydrator
{
    public function hydrate(RequestInterface $request, ExceptionFactoryInterface $exceptionFactory, $relationship)
    {
        parent::hydrate($request, $exceptionFactory, $relationship);

        if ($request->getMethod() === 'DELETE') {
            throw new MethodNotAllowed(['PATCH']);
        }
    }

    protected function getRelatedModelFromRelationship(ToOneRelationship $relationship)
    {
        $resourceIdentifier = $relationship->getResourceIdentifier();

        try {
            return $this->relation->getRelated()->newQuery()->findOrFail($resourceIdentifier->getId());
        } catch (ModelNotFoundException $exception) {
            throw $this->createRelatedResourceNotExists($resourceIdentifier);
        }
    }

    protected function validateRelationshipType(RequestInterface $request, ExceptionFactoryInterface $exceptionFactory, $relationship)
    {
        if ($relationship instanceof ToManyRelationship) {
            throw $exceptionFactory->createRelationshipTypeInappropriateException(
                $this->name,
                "to-many",
                "to-one"
            );
        }
    }
}