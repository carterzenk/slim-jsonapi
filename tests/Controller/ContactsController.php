<?php

namespace CarterZenk\Tests\JsonApi\Controller;

use CarterZenk\JsonApi\Controller\JsonApiController;
use CarterZenk\Tests\JsonApi\Model\Contact;

class ContactsController extends JsonApiController
{
    public function getBuilder()
    {
        return Contact::query();
    }
}
