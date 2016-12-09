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

    public function testGetAttributeHydratorsFromFillable()
    {
        $expected = [
            'f_name',
            'l_name',
            'email',
            'title',
            'phone',
            'phone_cell',
            'phone_office',
            'address',
            'city',
            'state',
            'zip',
            'birthday',
            'invalid'
        ];

        $actual = $this->getAttributeHydratorKeys(Contact::class);

        $this->assertEquals($expected, $actual);
    }

    public function testGetAttributeHydratorsFromGuarded()
    {
        $expected = [
            'f_name',
            'l_name',
            'email',
            'password',
            'phone',
            'phone_cell',
            'phone_office',
            'address',
            'city',
            'state',
            'zip',
            'timezone'
        ];

        $actual = $this->getAttributeHydratorKeys(User::class);
        var_dump($actual);

        $this->assertEquals($expected, $actual);
    }

    private function getAttributeHydratorKeys($modelClass)
    {
        $hydrator = new Hydrator();
        $model = new $modelClass();

        $attributeHydrator = $hydrator->getModelAttributeHydrator($model);

        return array_keys($attributeHydrator);
    }
}