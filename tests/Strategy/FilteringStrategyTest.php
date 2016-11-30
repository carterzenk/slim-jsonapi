<?php

namespace CarterZenk\Tests\JsonApi\Strategy;

use CarterZenk\JsonApi\Strategy\Filtering\ColumnEqualsValue;
use CarterZenk\JsonApi\Strategy\Filtering\ColumnOperatorValue;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use CarterZenk\Tests\JsonApi\Model\Contact;

class FilteringStrategyTest extends BaseTestCase
{
    public function testColumnEqualsValue()
    {
        $filters = [
            'f_name' => 'john',
            'l_name' => 'doe'
        ];

        $strategy = new ColumnEqualsValue();

        $builder = Contact::query();
        $builder = $strategy->applyFilters($builder, $filters);

        $wheres = $builder->getQuery()->wheres;

        $this->assertEquals(2, count($wheres));
        $this->assertEquals('f_name', $wheres[0]['column']);
        $this->assertEquals('=', $wheres[0]['operator']);
        $this->assertEquals('john', $wheres[0]['value']);
    }

    public function testColumnOperatorValue()
    {
        $filters = [
            'f_name' => [
                'contains' => 'john',
                'ne' => 'jerry'
            ],
            'created_at' => [
                'lt' => '2016-08-21',
                'gt' => '2015-08-21'
            ]
        ];

        $strategy = new ColumnOperatorValue();

        $builder = Contact::query();
        $builder = $strategy->applyFilters($builder, $filters);

        $wheres = $builder->getQuery()->wheres;

        $this->assertEquals(4, count($wheres));

        $this->assertEquals('f_name', $wheres[0]['column']);
        $this->assertEquals('LIKE', $wheres[0]['operator']);
        $this->assertEquals('john', $wheres[0]['value']);

        $this->assertEquals('f_name', $wheres[1]['column']);
        $this->assertEquals('!=', $wheres[1]['operator']);
        $this->assertEquals('jerry', $wheres[1]['value']);

        $this->assertEquals('created_at', $wheres[2]['column']);
        $this->assertEquals('<', $wheres[2]['operator']);
        $this->assertEquals('2016-08-21', $wheres[2]['value']);

        $this->assertEquals('created_at', $wheres[3]['column']);
        $this->assertEquals('>', $wheres[3]['operator']);
        $this->assertEquals('2015-08-21', $wheres[3]['value']);
    }
}
