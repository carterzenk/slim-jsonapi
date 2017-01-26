<?php

namespace CarterZenk\Tests\JsonApi\Controller;

use CarterZenk\JsonApi\Controller\AbstractJsonApiController;
use CarterZenk\Tests\JsonApi\Model\Contact;

class ContactsController extends AbstractJsonApiController
{
    public function getBuilder()
    {
        return Contact::query();
    }
}
