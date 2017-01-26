<?php
namespace CarterZenk\Tests\JsonApi\Model;

use CarterZenk\Tests\JsonApi\BaseTestCase;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    public function testResourceType()
    {
        $comment = new Comment();
        $this->assertEquals('words', $comment->getResourceType());
        $organizationUser = new OrganizationUser();
        $this->assertEquals(null, $organizationUser->getResourceType());
    }

    public function testFillableRelationships()
    {
        $contact = new Contact();
        $this->assertEquals(['assignee'], $contact->getFillableRelationships());

        $user = new User();
        $expected = [
            'organizations',
            'assignedContacts',
            'otherOrganizations',
            'activeContact'
        ];
        $this->assertEquals($expected, $user->getFillableRelationships());
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

    public function testIsFillable()
    {
        $contact = new Contact();

        $this->assertTrue($contact->isRelationshipFillable('assignee'));
        $this->assertFalse($contact->isRelationshipFillable('owner'));
    }
}
