<?php

namespace CarterZenk\Tests\JsonApi\Transformer;

use CarterZenk\JsonApi\Transformer\Builder;
use CarterZenk\JsonApi\Transformer\Container;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use CarterZenk\Tests\JsonApi\Model\Contact;
use CarterZenk\Tests\JsonApi\Model\OrganizationUser;
use CarterZenk\Tests\JsonApi\Model\User;

class BuilderTest extends BaseTestCase
{
    private function getBuilder($modelClass)
    {
        $baseUri = 'http://localhost:8000';
        $container = new Container($baseUri);
        $model = $modelClass::find(1);

        return new Builder($model, $container, $baseUri);
    }

    private function getBuilderForNew($modelClass)
    {
        $baseUri = 'http://localhost:8000';
        $container = new Container($baseUri);
        $model = new $modelClass();

        return new Builder($model, $container, $baseUri);
    }

    public function testGetType()
    {
        $builder = $this->getBuilder(Contact::class);
        $this->assertEquals('lead', $builder->getType());

        $builder = $this->getBuilder(OrganizationUser::class);
        $this->assertEquals('organization-user', $builder->getType());

        $builder = $this->getBuilderForNew(Contact::class);
        $this->assertEquals('lead', $builder->getType());
    }

    public function testGetIdKey()
    {
        $builder = $this->getBuilder(Contact::class);
        $this->assertEquals('id', $builder->getIdKey());

        $builder = $this->getBuilderForNew(Contact::class);
        $this->assertEquals('id', $builder->getIdKey());
    }

    public function testGetDefaultIncludedRelationships()
    {
        $builder = $this->getBuilder(Contact::class);
        $this->assertEquals(['assignee'], $builder->getDefaultIncludedRelationships());

        $builder = $this->getBuilder(User::class);
        $this->assertEquals([], $builder->getDefaultIncludedRelationships());

        $builder = $this->getBuilderForNew(Contact::class);
        $this->assertEquals(['assignee'], $builder->getDefaultIncludedRelationships());
    }

    public function testGetAttributesTransformer()
    {
        $expectedKeys = ['owner_id', 'assigned_id', 'id'];

        $builder = $this->getBuilder(Contact::class);
        $this->assertEquals($expectedKeys, $builder->getAttributesToHide());

        $builder = $this->getBuilderForNew(Contact::class);
        $this->assertEquals($expectedKeys, $builder->getAttributesToHide());
    }

    public function testGetRelationshipsTransformer()
    {
        $expectedKeys = ['owner', 'assignee'];
        $baseUri = 'http://localhost:8000';

        $container = new Container($baseUri);

        $model = Contact::find(1);
        $builder = new Builder($model, $container, $baseUri);
        $relationshipsTransformer = $builder->getRelationshipsTransformer($container);
        $this->checkTransformer($expectedKeys, $relationshipsTransformer);

        $model = new Contact();
        $builder = new Builder($model, $container, $baseUri);

        $relationshipsTransformer = $builder->getRelationshipsTransformer($container);
        $this->checkTransformer($expectedKeys, $relationshipsTransformer);
    }

    private function checkTransformer(array $expectedKeys, array $actual)
    {
        $this->assertEquals(count($expectedKeys), count($actual));

        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $actual);
            $this->assertInstanceOf(\Closure::class, $actual[$expectedKey]);
        }
    }
}
