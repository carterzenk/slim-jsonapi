<?php

namespace CarterZenk\Tests\JsonApi\Model;

class Organization extends BaseModel
{
    protected $table = 'organizations';
    protected $fillable = [
        'name', 'org_id', 'user_id'
    ];

    protected $visibleRelationships = [
        'users'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_users', 'org_id');
    }
}
