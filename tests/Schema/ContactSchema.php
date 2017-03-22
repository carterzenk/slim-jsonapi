<?php

namespace CarterZenk\Tests\JsonApi\Schema;

use CarterZenk\JsonApi\Schema\AbstractModelSchema;
use CarterZenk\Tests\JsonApi\Model\Contact;

class ContactSchema extends AbstractModelSchema
{
    protected $fillableAttributes = [
        'attribute1',
        'attribute2'
    ];

    protected $fillableRelationships = [
        'owner',
        'assignee'
    ];

    protected $visibleAttributes = [
        'name',
        'email',
        'phone'
    ];

    protected $visibleRelationships = [
        'owner',
        'assignee'
    ];

    protected $validTypes = [
        'contact',
        'borrower',
        'owner'
    ];

    protected function getModelClass()
    {
        return Contact::class;
    }
}