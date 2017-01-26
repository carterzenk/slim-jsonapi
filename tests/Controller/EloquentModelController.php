<?php

namespace CarterZenk\Tests\JsonApi\Controller;

use CarterZenk\JsonApi\Controller\AbstractJsonApiController;
use CarterZenk\Tests\JsonApi\Model\EloquentModel;

class EloquentModelController extends AbstractJsonApiController
{
    public function getBuilder()
    {
        return EloquentModel::query();
    }
}
