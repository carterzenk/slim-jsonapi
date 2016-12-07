<?php

namespace CarterZenk\JsonApi\Hydrator;

use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\AbstractHydrator;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

class ResourceHydrator extends AbstractHydrator implements HydratorInterface
{
    use ModelHydratorTrait;

    /**
     * @param string $clientGeneratedId
     * @param RequestInterface $request
     * @param ExceptionFactoryInterface $exceptionFactory
     * @throws \Exception
     */
    protected function validateClientGeneratedId(
        $clientGeneratedId,
        RequestInterface $request,
        ExceptionFactoryInterface $exceptionFactory
    ) {
        if ($clientGeneratedId !== null) {
            throw $exceptionFactory->createClientGeneratedIdNotSupportedException($request, $clientGeneratedId);
        }
    }

    /**
     * @return string
     */
    protected function generateId()
    {
        return uniqid();
    }

    /**
     * @return string
     */
    protected function getAcceptedType()
    {
        return '';
    }

    /**
     * @param array $data
     * @param ExceptionFactoryInterface $exceptionFactory
     * @throws \Exception
     */
    protected function validateType($data, ExceptionFactoryInterface $exceptionFactory)
    {
        $acceptedType = $this->getAcceptedType();

        if (empty($data["type"])) {
            throw $exceptionFactory->createResourceTypeMissingException();
        }
    }

    /**
     * @param mixed $domainObject
     * @param string $id
     * @return mixed|null
     */
    protected function setId($domainObject, $id)
    {
        return $domainObject;
    }

    /**
     * @param mixed $domainObject
     * @return \callable[]
     */
    protected function getRelationshipHydrator($domainObject)
    {
        return $this->getModelRelationshipHydrators($domainObject);
    }

    /**
     * @param mixed $domainObject
     * @return \callable[]
     */
    protected function getAttributeHydrator($domainObject)
    {
        return $this->getModelAttributeHydrator($domainObject);
    }
}
