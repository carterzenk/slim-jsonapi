<?php

namespace CarterZenk\Tests\JsonApi\Hydrator;

use CarterZenk\JsonApi\Hydrator\Hydrator;
use CarterZenk\JsonApi\Hydrator\ResourceHydrator;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use CarterZenk\Tests\JsonApi\Model\Contact;
use CarterZenk\Tests\JsonApi\Model\User;

class HydratorTest extends BaseTestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists(ResourceHydrator::class));
    }
}