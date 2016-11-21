<?php

namespace CarterZenk\Tests\JsonApi\Controller;

use CarterZenk\JsonApi\Controller\JsonApiController;
use CarterZenk\Tests\JsonApi\Model\User;

class UsersController extends JsonApiController
{
    public function getBuilder()
    {
        return User::query();
    }
}
