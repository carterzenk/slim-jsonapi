<?php

namespace CarterZenk\Tests\JsonApi\Controller;

use CarterZenk\JsonApi\Controller\JsonApiController;
use CarterZenk\JsonApi\Exceptions\InvalidDomainObjectException;
use CarterZenk\JsonApi\Serializer\Serializer;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\JsonApi;
use WoohooLabs\Yin\JsonApi\Request\Request;

class ControllerTest extends BaseTestCase
{
    public function testClassesExist()
    {
        $this->assertEquals(true, class_exists(JsonApi::class));
        $this->assertEquals(true, class_exists(JsonApiController::class));
        $this->assertEquals(true, class_exists(ContactsController::class));
    }
}
