<?php

namespace CarterZenk\Tests\JsonApi\Model\User;

use Analogue\ORM\EntityMap;

class UserMap extends EntityMap
{
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
}