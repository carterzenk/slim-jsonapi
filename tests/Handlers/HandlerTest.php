<?php

namespace CarterZenk\Tests\JsonApi\Handlers;

use CarterZenk\JsonApi\Exceptions\ExceptionFactoryInterface;
use CarterZenk\JsonApi\Exceptions\Forbidden;
use CarterZenk\JsonApi\Handlers\ErrorHandler;
use CarterZenk\JsonApi\Handlers\NotAllowedHandler;
use CarterZenk\JsonApi\Handlers\NotFoundHandler;
use CarterZenk\JsonApi\Handlers\PhpErrorHandler;
use CarterZenk\JsonApi\Serializer\SerializerInterface;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Serializer\DefaultSerializer;

class HandlerTest extends BaseTestCase
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var ExceptionFactoryInterface
     */
    protected $exceptionFactory;

    /**
     * @var ResponseInterface
     */
    protected $response;

    public function setUp()
    {
        parent::setUp();

        $this->serializer = new DefaultSerializer();
        $this->exceptionFactory = new DefaultExceptionFactory();
        $this->response = new Response();
    }

    public function testClassesExist()
    {
        $this->assertEquals(true, class_exists(ErrorHandler::class));
    }

    public function testErrorHandler()
    {
        $errorHandler = new ErrorHandler($this->serializer, true);
        $request = $this->getMockRequest();

        $exception = $this->exceptionFactory->createResourceNotFoundException($request);
        $exceptionResponse = $errorHandler($request, new Response(), $exception);
        $this->assertInstanceOf(ResponseInterface::class, $exceptionResponse);

        $exception = new \Exception('Some default exception.');
        $exceptionResponse = $errorHandler($request, new Response(), $exception);
        $this->assertInstanceOf(ResponseInterface::class, $exceptionResponse);
    }

    public function testPhpErrorHandler()
    {
        $errorHandler = new PhpErrorHandler($this->serializer, true);
        $request = $this->getMockRequest();

        $error = new \TypeError('This is a type error');
        $exceptionResponse = $errorHandler($request, $this->response, $error);
        $this->assertInstanceOf(ResponseInterface::class, $exceptionResponse);
    }

    public function testNotAllowedHandler()
    {
        $errorHandler = new NotAllowedHandler($this->serializer, true);
        $request = $this->getMockRequest();

        $exceptionResponse = $errorHandler($request, $this->response, ['GET', 'PUT', 'PATCH']);
        $this->assertInstanceOf(ResponseInterface::class, $exceptionResponse);
    }

    public function testNotFoundHandler()
    {
        $errorHandler = new NotFoundHandler($this->serializer, true);
        $request = $this->getMockRequest();

        $exceptionResponse = $errorHandler($request, $this->response);
        $this->assertInstanceOf(ResponseInterface::class, $exceptionResponse);
    }

    public function testForbiddenError()
    {
        $error = new Forbidden();
        $this->assertInstanceOf(Forbidden::class, $error);
    }
}
