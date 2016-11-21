<?php

namespace CarterZenk\Tests\JsonApi\Handlers;

use CarterZenk\JsonApi\Handlers\ErrorHandler;
use CarterZenk\JsonApi\Handlers\Strategies\InvocationStrategy;
use CarterZenk\JsonApi\Serializer\Serializer;
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
        $this->assertEquals(true, class_exists(InvocationStrategy::class));
        $this->assertEquals(true, class_exists(ErrorHandler::class));
    }

    public function testInvocationStrategyImplementsInterface()
    {
        $invocationStrategy = $this->getInvocationStrategy();
        $this->assertInstanceOf(InvocationStrategyInterface::class, $invocationStrategy);
    }

    public function testErrorHandler()
    {
        $errorHandler = new ErrorHandler(new Serializer(JSON_PRETTY_PRINT), true);
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
