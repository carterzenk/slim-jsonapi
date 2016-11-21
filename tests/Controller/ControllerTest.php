<?php

namespace CarterZenk\Tests\JsonApi\Controller;

use CarterZenk\JsonApi\Controller\JsonApiController;
use CarterZenk\JsonApi\Serializer\Serializer;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Request\Request;

class ControllerTest extends BaseTestCase
{
    private function getRequest()
    {
        return $this->app->request;
    }

    private function getResponse()
    {
        return $this->app->response;
    }

    private function getJsonApi()
    {
        $serializer = new Serializer(JSON_PRETTY_PRINT);
        $exceptionFactory = new DefaultExceptionFactory();

        $request = new Request($this->getRequest(), $exceptionFactory);
        $response = $this->getResponse();

        return new JsonApi($request, $response, $exceptionFactory, $serializer);
    }

    private function getContactsController()
    {
        return $this->app->getContainer()->get(
            '\CarterZenk\Tests\JsonApi\Controller\ContactsController'
        );
    }

    public function testClassesExist()
    {
        $this->assertEquals(true, class_exists(JsonApi::class));
        $this->assertEquals(true, class_exists(JsonApiController::class));
        $this->assertEquals(true, class_exists(ContactsController::class));
    }
}
