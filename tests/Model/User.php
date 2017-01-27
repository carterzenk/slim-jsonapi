<?php

namespace CarterZenk\Tests\JsonApi\Model;

class User extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $hidden = [
        'password'
    ];

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $fillableRelationships = [
        'organizations',
        'assignedContacts',
        'otherOrganizations',
        'activeContact'
    ];

    protected $visibleRelationships = [
        'assignedContacts',
        'ownedContacts'
    ];

    protected $relationMethods = [
        'ownedContacts',
        'assignedContacts',
        'activeContact',
        'organizations',
        'otherOrganizations'
    ];

    public function ownedContacts()
    {
        return $this->hasMany(Contact::class, 'owner_id', 'id');
    }

    public function assignedContacts()
    {
        return $this->hasMany(Contact::class, 'assigned_id', 'id');
    }

    public function activeContact()
    {
        return $this->belongsTo(Contact::class, 'active_id', 'id');
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_users', 'user_id', 'org_id');
    }

    public function otherOrganizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_users', 'user_id', 'org_id');
    }
}
