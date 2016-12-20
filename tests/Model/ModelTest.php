<?php
namespace CarterZenk\Tests\JsonApi\Model;

use CarterZenk\Tests\JsonApi\BaseTestCase;

class ModelTest extends BaseTestCase
{
    public function testSetFillableRelations()
    {
        $contact = new Contact();
        $contact->setFillableRelations(['owner']);
        $this->assertEquals(['owner'], $contact->getFillableRelations());
    }

    public function testSetGuardedRelations()
    {
        $contact = new Contact();
        $contact->setGuardedRelations(['owner']);
        $this->assertEquals(['owner'], $contact->getGuardedRelations());
    }

    public function testGuardedRelationsAllowFillable()
    {
        $contact = new Contact();
        $contact->setGuardedRelations(['owner']);
        $contact->setFillableRelations([]);
        $this->assertTrue($contact->isRelationFillable('assignee'));
    }
}
