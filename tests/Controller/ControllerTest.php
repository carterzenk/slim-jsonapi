<?php

namespace CarterZenk\Tests\JsonApi\Controller;

use CarterZenk\JsonApi\Controller\AbstractJsonApiController;
use CarterZenk\JsonApi\Controller\JsonApiControllerInterface;
use CarterZenk\Tests\JsonApi\BaseTestCase;

class ControllerTest extends BaseTestCase
{
    public function testClassExist()
    {
        $this->assertTrue(class_exists(AbstractJsonApiController::class));
    }

    public function testItImplementsInterface()
    {
        $this->assertNotFalse(
            array_search(JsonApiControllerInterface::class,
                class_implements(AbstractJsonApiController::class),
                true
            )
        );
    }


}
