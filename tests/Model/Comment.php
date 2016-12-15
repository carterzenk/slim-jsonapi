<?php

namespace CarterZenk\Tests\JsonApi\Model;

class Comment extends BaseModel
{
    protected $fillable = [
        'name',
        'id',
        'someInsaneMethod'
    ];

    public function getRelationMethods()
    {
        return [
            'someInsaneMethod'
        ];
    }

    public function someInsaneMethod()
    {
        $this->morphedByMany(Contact::class, 'contacts');
    }
}
