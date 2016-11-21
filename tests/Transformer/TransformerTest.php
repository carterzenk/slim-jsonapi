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

class TransformerTest extends BaseTestCase
{
    private function getTransformer($baseUri = 'http://localhost')
    {
        return new Transformer($baseUri);
    }

    public function testTransformerExists()
    {
        $exists = class_exists(Transformer::class);
        $this->assertEquals(true, $exists);

        $exists = class_exists(ModelTransformer::class);
        $this->assertEquals(true, $exists);
    }

    public function testTransformerConstruction()
    {
        $transformer = $this->getTransformer();
        $this->assertInstanceOf(ResourceTransformerInterface::class, $transformer);

        $modelTransformer = new ModelTransformer();
        $this->assertInstanceOf(ModelTransformer::class, $modelTransformer);
    }

    public function testGetResourceType()
    {
        $transformer = $this->getTransformer();

        $contact = new Contact();
        $this->assertEquals('lead', $transformer->getType($contact));

        $organizationUser = new OrganizationUser();
        $this->assertEquals('organization-user', $transformer->getType($organizationUser));
    }

    public function testGetId()
    {
        $transformer = $this->getTransformer();
        $contact = Contact::find(1);
        $id = $transformer->getId($contact);
        $this->assertEquals('1', $id);
    }

    public function testGetLinks()
    {
        $contact = Contact::find(1);

        $transformer = $this->getTransformer();
        $links = $transformer->getLinks($contact);
        $this->assertInstanceOf(Links::class, $links);
        $this->assertEquals('http://localhost', $links->getBaseUri());

        $transformer = $this->getTransformer(null);
        $links = $transformer->getLinks($contact);
        $this->assertInstanceOf(Links::class, $links);
        $this->assertEquals('', $links->getBaseUri());

        $selfLink = $links->getSelf();
        $this->assertEquals('/leads/1', $selfLink->getHref());
    }

    public function testGetAttributes()
    {
        $transformer = $this->getTransformer();
        $contact = Contact::find(1);

        $attributes = $transformer->getAttributes($contact);

        foreach ($contact->getVisible() as $visibleAttribute) {
            $this->assertArrayHasKey($visibleAttribute, $attributes);
        }

        $this->assertArrayNotHasKey('owner_id', $attributes);
        $this->assertArrayNotHasKey('assigned_id', $attributes);
    }

    public function testGetDefaultIncludedRelationships()
    {
        $transformer = $this->getTransformer();
        $contact = Contact::find(1);

        $defaultIncludedRelationships = $transformer->getDefaultIncludedRelationships($contact);
        $this->assertEquals(['assignee'], $defaultIncludedRelationships);
    }

    public function testToOneRelationships()
    {
        $transformer = $this->getTransformer();
        $contact = Contact::find(1);

        $relationships = $transformer->getRelationships($contact);

        $this->assertEquals(2, count($relationships));
        $this->assertArrayHasKey('owner', $relationships);
        $this->assertArrayHasKey('assignee', $relationships);

        $ownerRelationship = $relationships['owner'];
        $this->assertInstanceOf(\Closure::class, $ownerRelationship);
        $this->assertInstanceOf(ToOneRelationship::class, $ownerRelationship($contact));

        $assigneeRelationship = $relationships['assignee'];
        $this->assertInstanceOf(\Closure::class, $assigneeRelationship);
        $this->assertInstanceOf(ToOneRelationship::class, $assigneeRelationship($contact));
    }

    public function testToManyRelationships()
    {
        $transformer = $this->getTransformer();
        $organization = Organization::find(1);

        $relationships = $transformer->getRelationships($organization);

        $this->assertEquals(1, count($relationships));
        $this->assertArrayHasKey('users', $relationships);

        $usersRelationship = $relationships['users'];
        $this->assertInstanceOf(\Closure::class, $usersRelationship);
        $this->assertInstanceOf(ToManyRelationship::class, $usersRelationship($organization));
    }

    public function testExceptionWithInvalidType()
    {
        $transformer = $this->getTransformer();
        $this->expectException(\TypeError::class);
        $transformer->getRelationships([]);
    }
}
