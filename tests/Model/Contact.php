<?php

namespace CarterZenk\Tests\JsonApi\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends BaseModel
{
    use SoftDeletes;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = 'leads';

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

    protected $with = [
        'assignee'
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

    public function setFirstNameAttribute($firstName)
    {
        $this->f_name = $firstName;
    }

    public function scopeZip(Builder $query)
    {
        return $query->where('zip', '11111');
    }

    public function someOtherMethod($something)
    {
        return $something;
    }

    protected function someOtherProtectedMethod()
    {
        return null;
    }
}
