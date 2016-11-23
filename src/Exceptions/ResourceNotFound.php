<?php

namespace CarterZenk\JsonApi\Exceptions;

use WoohooLabs\Yin\JsonApi\Exception\JsonApiException;
use WoohooLabs\Yin\JsonApi\Schema\Error;

class ResourceNotFound extends JsonApiException
{
    public function __construct()
    {
        parent::__construct("The requested resource is not found!");
    }

    /**
     * @inheritDoc
     */
    protected function getErrors()
    {
        return [
            Error::create()
                ->setStatus(404)
                ->setCode("RESOURCE_NOT_FOUND")
                ->setTitle("Resource not found")
                ->setDetail($this->getMessage())
        ];
    }
}
