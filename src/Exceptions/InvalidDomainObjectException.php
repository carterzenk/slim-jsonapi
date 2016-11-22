<?php

namespace CarterZenk\JsonApi\Exceptions;

use WoohooLabs\Yin\JsonApi\Exception\JsonApiException;
use WoohooLabs\Yin\JsonApi\Schema\Error;

class InvalidDomainObjectException extends JsonApiException
{
    public function __construct($domainObject)
    {
        parent::__construct('Domain object '.get_class($domainObject).' is not a Model or Collection.');
    }

    /**
     * @inheritdoc
     */
    protected function getErrors()
    {
        return [
            Error::create()
                ->setStatus(500)
                ->setCode("DOMAIN_OBJECT_TYPE_ERROR")
                ->setTitle("Invalid domain object error")
                ->setDetail($this->getMessage())
        ];
    }
}
