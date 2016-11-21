<?php

namespace CarterZenk\Tests\JsonApi\Model;

/**
 * Class OrganizationUser
 */
class OrganizationUser extends BaseModel
{
    protected $table = 'organization_users';
    protected $guarded = [];

    protected $visibleRelationships = [
        'someMorph'
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'org_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function someMorph()
    {
        return $this->morphOne(User::class, 'some_user');
    }
}
