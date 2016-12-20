<?php

namespace CarterZenk\JsonApi\Hydrator\Relationship;

use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

interface RelationshipHydratorInterface
{
    /**
     * @param RequestInterface $request
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param $relationship
     */
    function hydrate(RequestInterface $request, ExceptionFactoryInterface $exceptionFactory, $relationship);
}
