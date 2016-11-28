<?php

namespace CarterZenk\JsonApi\Exceptions;

use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory as YinDefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;

class ExceptionFactory extends YinDefaultExceptionFactory implements ExceptionFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createForbiddenException()
    {
        return new Forbidden();
    }

    /**
     * @inheritdoc
     */
    public function createInvalidDomainObjectException($domainObject)
    {
        return new InvalidDomainObjectException($domainObject);
    }

    /**
     * @inheritdoc
     */
    public function createMethodNotAllowedException(array $availableMethods)
    {
        return new MethodNotAllowed($availableMethods);
    }

    /**
     * @inheritdoc
     */
    public function createResourceNotFoundException(RequestInterface $request)
    {
        return new ResourceNotFound();
    }

    /**
     * @inheritdoc
     */
    public function createBadRequestException($message = 'Bad request.', ErrorSource $source = null)
    {
        return new BadRequest($message, $source);
    }

    /**
     * @inheritdoc
     */
    public function createResourceNotExistsException($type, $id)
    {
        return new ResourceNotExists($type, $id);
    }
}
