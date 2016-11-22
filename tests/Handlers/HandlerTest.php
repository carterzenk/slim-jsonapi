<?php

namespace CarterZenk\Tests\JsonApi\Handlers;

use CarterZenk\JsonApi\Handlers\ErrorHandler;
use CarterZenk\JsonApi\Serializer\JsonApiSerializer;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;
use Slim\Interfaces\InvocationStrategyInterface;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;

class HandlerTest extends BaseTestCase
{
    private function getInvocationStrategy()
    {
        return $this->app->getContainer()->get(InvocationStrategyInterface::class);
    }

    public function testClassesExist()
    {
        $this->assertEquals(true, class_exists(ErrorHandler::class));
    }

    public function testErrorHandler()
    {
        $serializer = new JsonApiSerializer(JSON_PRETTY_PRINT);
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
}
