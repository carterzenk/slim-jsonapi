<?php

namespace CarterZenk\Tests\JsonApi\Model;

use CarterZenk\Tests\JsonApi\BaseTestCase;
use Illuminate\Database\Eloquent\Model;

class ModelTest extends BaseTestCase
{
    public function testFillMethodWithoutGuardedRelationships()
    {
        $contact = new Contact();
        $contact->guard([
            'id',
            'owner_id'
        ]);

        $contact = $this->getFilledModel($contact);

        $this->assertEquals('John', $contact->f_name);
        $this->assertEquals('Doe', $contact->l_name);
        $this->assertNull($contact->owner);
        $this->assertNull($contact->assignee);
    }

    public function testFillMethodWithGuardedRelationships()
    {
        $contact = new Contact();
        $contact->guard([
            'id',
            'owner_id',
            'assignee'
        ]);

        $contact = $this->getFilledModel($contact);

        $this->assertEquals('John', $contact->f_name);
        $this->assertEquals('Doe', $contact->l_name);
        $this->assertNull($contact->owner);
        $this->assertNull($contact->assignee);
    }

    public function testFillMethodWithoutFillableRelationships()
    {
        $contact = new Contact();
        $contact->fillable([
            'f_name',
            'l_name'
        ]);

        $contact = $this->getFilledModel($contact);

        $this->assertEquals('John', $contact->f_name);
        $this->assertEquals('Doe', $contact->l_name);
        $this->assertNull($contact->owner);
        $this->assertNull($contact->assignee);
    }

    public function testFillMethodWithFillableRelationships()
    {
        $contact = new Contact();
        $contact->fillable([
            'f_name',
            'l_name',
            'assignee'
        ]);

        $contact = $this->getFilledModel($contact);

        $this->assertEquals('John', $contact->f_name);
        $this->assertEquals('Doe', $contact->l_name);
        $this->assertNull($contact->owner);
        $this->assertNull($contact->assignee);
    }

    private function getFilledModel(Model $contact)
    {
        $contact->fill([
            'f_name' => 'John',
            'l_name' => 'Doe',
            'owner_id' => 1,
            'assignee' => [
                'id' => 2
            ]
        ]);

        $contact->save();
        $contact->fresh();

        return $contact;
    }
}
