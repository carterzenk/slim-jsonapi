<?php

namespace CarterZenk\Tests\JsonApi\Model;

use CarterZenk\JsonApi\Model\RelationshipMassAssignment;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use RelationshipMassAssignment;

    public $timestamps = false;
}
