<?php

namespace CarterZenk\Tests\JsonApi\Handlers;

use CarterZenk\JsonApi\Exceptions\Forbidden;
use CarterZenk\JsonApi\Handlers\ErrorHandler;
use CarterZenk\JsonApi\Handlers\NotAllowedHandler;
use CarterZenk\JsonApi\Handlers\NotFoundHandler;
use CarterZenk\JsonApi\Handlers\PhpErrorHandler;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Serializer\DefaultSerializer;

class HandlerTest extends BaseTestCase
{
    public function testClassesExist()
    {
        $this->assertEquals(true, class_exists(ErrorHandler::class));
    }

    public function testErrorHandler()
    {
        $serializer = new DefaultSerializer();
        $errorHandler = new ErrorHandler($serializer, true);
        $exceptionFactory = new DefaultExceptionFactory();
        $request = Request::createFromEnvironment($this->app->getContainer()->get('environment'));
        $request = new \WoohooLabs\Yin\JsonApi\Request\Request($request, $exceptionFactory);
        $response = new Response();

        $exception = $exceptionFactory->createResourceNotFoundException($request);
        $exceptionResponse = $errorHandler($request, $response, $exception);
        $this->assertInstanceOf(ResponseInterface::class, $exceptionResponse);

        $exception = new \Exception('Some default exception.');
        $exceptionResponse = $errorHandler($request, $response, $exception);
        $this->assertInstanceOf(ResponseInterface::class, $exceptionResponse);
    }

    public function testPhpErrorHandler()
    {
        $serializer = new DefaultSerializer();
        $errorHandler = new PhpErrorHandler($serializer, true);
        $exceptionFactory = new DefaultExceptionFactory();
        $request = Request::createFromEnvironment($this->app->getContainer()->get('environment'));
        $request = new \WoohooLabs\Yin\JsonApi\Request\Request($request, $exceptionFactory);
        $response = new Response();

        $error = new \TypeError('This is a type error');
        $exceptionResponse = $errorHandler($request, $response, $error);
        $this->assertInstanceOf(ResponseInterface::class, $exceptionResponse);
    }

    public function testNotAllowedHandler()
    {
        $serializer = new DefaultSerializer();
        $errorHandler = new NotAllowedHandler($serializer, true);
        $exceptionFactory = new DefaultExceptionFactory();
        $request = Request::createFromEnvironment($this->app->getContainer()->get('environment'));
        $request = new \WoohooLabs\Yin\JsonApi\Request\Request($request, $exceptionFactory);
        $response = new Response();

        $exceptionResponse = $errorHandler($request, $response, ['GET', 'PUT', 'PATCH']);
        $this->assertInstanceOf(ResponseInterface::class, $exceptionResponse);
    }

    public function testNotFoundHandler()
    {
        $serializer = new DefaultSerializer();
        $errorHandler = new NotFoundHandler($serializer, true);
        $exceptionFactory = new DefaultExceptionFactory();
        $request = Request::createFromEnvironment($this->app->getContainer()->get('environment'));
        $request = new \WoohooLabs\Yin\JsonApi\Request\Request($request, $exceptionFactory);
        $response = new Response();

        $exceptionResponse = $errorHandler($request, $response);
        $this->assertInstanceOf(ResponseInterface::class, $exceptionResponse);
    }

    public function testForbiddenError()
    {
        $error = new Forbidden();
        $this->assertInstanceOf(Forbidden::class, $error);
    }
}
