<?php

namespace CarterZenk\Tests\JsonApi\Controller;

use CarterZenk\JsonApi\Controller\JsonApiController;
use CarterZenk\Tests\JsonApi\Model\EloquentModel;

class EloquentModelController extends JsonApiController
{
    public function getBuilder()
    {
        return EloquentModel::query();
    }
}
