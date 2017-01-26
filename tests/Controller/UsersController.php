<?php

namespace CarterZenk\Tests\JsonApi\Controller;

use CarterZenk\JsonApi\Controller\AbstractJsonApiController;
use CarterZenk\Tests\JsonApi\Model\User;

class UsersController extends AbstractJsonApiController
{
    public function getBuilder()
    {
        return User::query();
    }
}
