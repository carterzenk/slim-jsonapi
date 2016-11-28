<?php

namespace CarterZenk\JsonApi\Exceptions;

use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface as YinExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;

interface ExceptionFactoryInterface extends YinExceptionFactoryInterface
{
    /**
     * @return Forbidden
     */
    public function createForbiddenException();

    /**
     * @param $domainObject
     * @return InvalidDomainObjectException
     */
    public function createInvalidDomainObjectException($domainObject);

    /**
     * @param array $availableMethods
     * @return MethodNotAllowed
     */
    public function createMethodNotAllowedException(array $availableMethods);

    /**
     * @param string $message
     * @param ErrorSource|null $source
     * @return BadRequest
     */
    public function createBadRequestException($message = 'Bad request.', ErrorSource $source = null);

    /**
     * @param string $type
     * @param string $id
     * @return ResourceNotExists
     */
    public function createResourceNotExistsException($type, $id);
}
