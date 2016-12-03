<?php

namespace CarterZenk\Tests\JsonApi\Model;

class User extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $hidden = [
        'password'
    ];

    protected $fillable = [
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
        'timezone',
        'created_at',
        'updated_at'
    ];

    protected $fillableRelationships = [
        'ownedContacts'
    ];

    protected $visibleRelationships = [
        'ownedContacts',
        'assignedContacts',
        'organizations'
    ];

    public function ownedContacts()
    {
        return $this->hasMany(Contact::class, 'owner_id', 'id');
    }

    public function assignedContacts()
    {
        return $this->hasMany(Contact::class, 'assigned_id', 'id');
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_users', 'user_id', 'org_id');
    }
}
