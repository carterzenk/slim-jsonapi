<?php

namespace CarterZenk\Tests\JsonApi\Model;

class Contact extends BaseModel
{
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = 'leads';
    protected $resourceType = 'lead';

    protected $guarded = [
        'id',
        'owner_id'
    ];

    protected $visible = [
        'f_name',
        'l_name',
        'email',
        'title',
        'phone',
        'phone_cell',
        'phone_office',
        'address',
        'city',
        'state',
        'zip',
        'birthday',
        'created_at',
        'updated_at'
    ];

    protected $fillable = [
        'f_name',
        'l_name',
        'email',
        'title',
        'phone',
        'phone_cell',
        'phone_office',
        'address',
        'city',
        'state',
        'zip',
        'birthday',
    ];

    protected $with = [
        'assignee'
    ];

    protected $visibleRelationships = [
        'owner',
        'assignee'
    ];

    protected $fillableRelationships = [
        'assignee'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_id', 'id');
    }
}
