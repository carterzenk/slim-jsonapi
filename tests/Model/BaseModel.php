<?php

namespace CarterZenk\Tests\JsonApi\Model;

use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\ModelInterface;

abstract class BaseModel extends Model implements ModelInterface
{
    public $timestamps = false;
}
