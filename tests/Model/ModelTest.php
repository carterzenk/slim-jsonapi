<?php

namespace CarterZenk\Tests\Model;

use CarterZenk\JsonApi\Model\ModelInterface;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use CarterZenk\Tests\JsonApi\Model\Contact;
use CarterZenk\Tests\JsonApi\Model\Organization;
use CarterZenk\Tests\JsonApi\Model\OrganizationUser;
use CarterZenk\Tests\JsonApi\Model\User;

class ModelTest extends BaseTestCase
{
    public function testClassesExist()
    {
        $this->assertEquals(true, class_exists(Contact::class));
        $this->assertEquals(true, class_exists(OrganizationUser::class));
    }

    public function testModelsImplementInterface()
    {
        $this->assertInstanceOf(ModelInterface::class, new Contact());
        $this->assertInstanceOf(ModelInterface::class, new OrganizationUser());
    }

    public function testResourceType()
    {
        $contact = new Contact();
        $this->assertEquals('lead', $contact->getResourceType());

        $organizationUser = new OrganizationUser();
        $this->assertEquals(null, $organizationUser->getResourceType());
    }

    public function testFillableRelationships()
    {
        $contact = new Contact();
        $this->assertEquals(['assignee'], $contact->getFillableRelationships());

        $user = new User();
        $this->assertEquals(['ownedContacts'], $user->getFillableRelationships());

        $organization = new Organization();
        $this->assertEquals([], $organization->getFillableRelationships());
    }

    public function testAddFillableRelationship()
    {
        $contact = new Contact();
        $contact->addFillableRelationship('someRelationship');
        $fillable = $contact->getFillableRelationships();
        $this->assertTrue(in_array('someRelationship', $fillable));
    }

    public function testRemoveFillableRelationship()
    {
        $contact = new Contact();
        $contact->removeFillableRelationship('assignee');
        $fillable = $contact->getFillableRelationships();
        $this->assertFalse(in_array('assignee', $fillable));
    }
}
