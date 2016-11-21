<?php

namespace CarterZenk\Tests\JsonApi\Model;

class Thread extends BaseModel
{
    protected $visibleRelationships = [
        'authors',
    ];
}
