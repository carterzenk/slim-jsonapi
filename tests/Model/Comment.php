<?php

namespace CarterZenk\Tests\JsonApi\Model;

class Comment extends BaseModel
{
    protected $resourceType = 'words';

    protected $fillable = [
        'name',
        'id'
    ];

    protected $fillableRelationships = [
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
