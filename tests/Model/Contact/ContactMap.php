<?php

namespace CarterZenk\Tests\JsonApi\Model\Contact;

use Analogue\ORM\EntityMap;

class ContactMap extends EntityMap
{
    public $softDeletes = true;
    protected $table = 'leads';
    protected $with = ['assignee'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_id', 'id');
    }

    public function activeUser()
    {
        return $this->hasOne(User::class, 'active_id', 'id');
    }
}