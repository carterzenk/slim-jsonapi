<?php

namespace CarterZenk\Tests\JsonApi\Model;

use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    public $timestamps = false;
}
