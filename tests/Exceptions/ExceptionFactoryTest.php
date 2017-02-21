<?php

namespace CarterZenk\Tests\JsonApi\Exceptions;

use CarterZenk\JsonApi\Exceptions\BadRequest;
use CarterZenk\JsonApi\Exceptions\ExceptionFactory;
use CarterZenk\JsonApi\Exceptions\Forbidden;
use CarterZenk\JsonApi\Exceptions\InvalidDomainObjectException;
use CarterZenk\JsonApi\Exceptions\MethodNotAllowed;
use CarterZenk\JsonApi\Exceptions\ResourceNotExists;
use CarterZenk\JsonApi\Exceptions\ResourceNotFound;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use Slim\Http\Request;
use WoohooLabs\Yin\JsonApi\Document\AbstractDocument;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;

class ExceptionFactoryTest extends BaseTestCase
{
    public function testForbiddenException()
    {
        $factory = new ExceptionFactory();
        $forbidden = $factory->createForbiddenException();
        $this->assertInstanceOf(Forbidden::class, $forbidden);
    }

    public function testInvalidDomainObjectException()
    {
        $factory = new ExceptionFactory();
        $invalidDomainObject = $factory->createInvalidDomainObjectException([]);
        $this->assertInstanceOf(InvalidDomainObjectException::class, $invalidDomainObject);
        $this->assertInstanceOf(AbstractDocument::class, $invalidDomainObject->getErrorDocument());
    }

    public function testMethodNotAllowed()
    {
        $factory = new ExceptionFactory();
        $methodNotAllowed = $factory->createMethodNotAllowedException(['GET']);
        $this->assertInstanceOf(MethodNotAllowed::class, $methodNotAllowed);
    }

    public function testResourceNotFound()
    {
        $factory = new ExceptionFactory();
        $resourceNotFound = $factory->createResourceNotFoundException($this->getMockRequest());
        $this->assertInstanceOf(ResourceNotFound::class, $resourceNotFound);
    }

    public function testResourceNotExists()
    {
        $factory = new ExceptionFactory();
        $notExists = $factory->createResourceNotExistsException('type', '21');
        $this->assertInstanceOf(ResourceNotExists::class, $notExists);
    }

    public function testBadRequest()
    {
        $factory = new ExceptionFactory();

        $badRequest = $factory->createBadRequestException();
        $this->assertInstanceOf(BadRequest::class, $badRequest);

        $source = new ErrorSource('some_pointer', 'some_parameter');
        $badRequest = $factory->createBadRequestException('Bad request.', $source);
        $this->assertInstanceOf(BadRequest::class, $badRequest);
    }
}
