<?php

namespace CarterZenk\Tests\JsonApi\Model;

class Comment extends BaseModel
{
    protected $visibleRelationships = [
        'someInsaneMethod',
    ];

    public function someInsaneMethod()
    {
        return [];
    }
}
