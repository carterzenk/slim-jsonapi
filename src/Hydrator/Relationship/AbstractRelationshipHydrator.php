<?php

namespace CarterZenk\JsonApi\Hydrator\Relationship;

use CarterZenk\JsonApi\Exceptions\RelatedResourceNotFound;
use Illuminate\Database\Eloquent\Relations\Relation;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;
use WoohooLabs\Yin\JsonApi\Schema\ResourceIdentifier;

abstract class AbstractRelationshipHydrator implements RelationshipHydratorInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Relation
     */
    protected $relation;

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param $relationship
     */
    protected abstract function hydrateForCreate(ExceptionFactoryInterface $exceptionFactory, $relationship);

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param $relationship
     */
    protected abstract function hydrateForUpdate(ExceptionFactoryInterface $exceptionFactory, $relationship);

    /**
     * @param RequestInterface $request
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param $relationship
     */
    public function hydrate(RequestInterface $request, ExceptionFactoryInterface $exceptionFactory, $relationship)
    {
        $this->validateRelationshipType($request, $exceptionFactory, $relationship);

        if ($request->getMethod() === 'POST') {
            $this->hydrateForCreate($exceptionFactory, $relationship);
        } elseif ($request->getMethod() === 'PATCH') {
            $this->hydrateForUpdate($exceptionFactory, $relationship);
        }
    }

    /**
     * @param RequestInterface $request
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param $relationship
     */
    protected abstract function validateRelationshipType(RequestInterface $request, ExceptionFactoryInterface $exceptionFactory, $relationship);

    /**
     * @param ResourceIdentifier $identifier
     * @return RelatedResourceNotFound
     */
    protected function createRelatedResourceNotExists(ResourceIdentifier $identifier)
    {
        $pointer = '/data/relationships/'.$this->name;
        $source = ErrorSource::fromPointer($pointer);

        return new RelatedResourceNotFound($identifier, $source);
    }
}
