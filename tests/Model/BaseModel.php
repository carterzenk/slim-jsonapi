<?php

namespace CarterZenk\Tests\JsonApi\Model;

use CarterZenk\JsonApi\Model\Relationable;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model
{
    use Relationable;
}
