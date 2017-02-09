<?php

namespace CarterZenk\Tests\JsonApi\Model;

use Analogue\ORM\Entity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Entity
{
    public function __construct(
        $f_name,
        $l_name,
        $email,
        $title,
        $phone,
        $phone_cell,
        $phone_office,
        $address,
        $city,
        $state,
        $zip,
        $birthday
    ){

    }

    protected $guarded = [
        'id',
        'owner_id'
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
        'invalid'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $visibleRelationships = [
        'owner',
        'assignee',
        'activeUser'
    ];

    protected $fillableRelationships = [
        'assignee',
        'activeUser'
    ];



    public function setFirstNameAttribute($firstName)
    {
        $this->f_name = $firstName;
    }

    public function scopeZip(Builder $query)
    {
        return $query->where('zip', '11111');
    }
}
