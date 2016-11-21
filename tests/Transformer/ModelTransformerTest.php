<?php

namespace CarterZenk\Tests\JsonApi\Transformer;

use CarterZenk\JsonApi\Transformer\ModelTransformer;
use CarterZenk\JsonApi\Transformer\Transformer;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use CarterZenk\Tests\JsonApi\Model\Contact;
use CarterZenk\Tests\JsonApi\Model\Organization;
use CarterZenk\Tests\JsonApi\Model\OrganizationUser;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Links;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class ModelTransformerTest extends BaseTestCase
{
    public function testClassExists()
    {
        $this->assertEquals(true, class_exists(ModelTransformer::class));
    }

    public function testConstruction()
    {
        $modelTransformer = new ModelTransformer();
        $this->assertInstanceOf(ModelTransformer::class, $modelTransformer);
    }
}
