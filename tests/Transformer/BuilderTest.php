<?php

namespace CarterZenk\Tests\JsonApi\Transformer;

use CarterZenk\JsonApi\Transformer\Builder;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use CarterZenk\Tests\JsonApi\Model\Contact;
use CarterZenk\Tests\JsonApi\Model\OrganizationUser;
use Illuminate\Database\Eloquent\Relations\Relation;
use Laracasts\TestDummy\Factory;

class BuilderTest extends BaseTestCase
{
    private function getBuilder($modelClass)
    {
        $model = Factory::build($modelClass);

        return new Builder($model);
    }

    private function getBuilderForNew($modelClass)
    {
        $model = new $modelClass();

        return new Builder($model);
    }

    public function testGetType()
    {
        $builder = $this->getBuilder(Contact::class);
        $this->assertEquals('contact', $builder->getType());

        $builder = $this->getBuilder(OrganizationUser::class);
        $this->assertEquals('organization-user', $builder->getType());

        $builder = $this->getBuilderForNew(Contact::class);
        $this->assertEquals('contact', $builder->getType());
    }

    public function testGetPluralType()
    {
        $builder = $this->getBuilder(Contact::class);
        $this->assertEquals('contacts', $builder->getPluralType());

        $builder = $this->getBuilder(OrganizationUser::class);
        $this->assertEquals('organization-users', $builder->getPluralType());
    }

    public function testGetAttributesTransformer()
    {
        $expectedKeys = ['owner_id', 'assigned_id'];

        $builder = $this->getBuilder(Contact::class);
        $this->assertEquals($expectedKeys, $builder->getForeignKeys());

        $builder = $this->getBuilderForNew(Contact::class);
        $this->assertEquals($expectedKeys, $builder->getForeignKeys());
    }

    public function testGetRelationshipsTransformer()
    {
        $builder = $this->getBuilder(Contact::class);
        $relations = $builder->getRelations();

        $this->assertEquals(['owner', 'assignee', 'activeUser'], array_keys($relations));
        foreach ($relations as $name => $relation) {
            $this->assertInstanceOf(Relation::class, $relation);
        }
    }
}
