<?php

namespace CarterZenk\JsonApi\Exceptions;

use WoohooLabs\Yin\JsonApi\Exception\JsonApiException;
use WoohooLabs\Yin\JsonApi\Schema\Error;

class Forbidden extends JsonApiException
{
    /**
     * Forbidden constructor.
     */
    public function __construct()
    {
        parent::__construct('Forbidden');
    }

    /**
     * @inheritdoc
     */
    protected function getErrors()
    {
        return [
            Error::create()
                ->setStatus(403)
                ->setCode("FORBIDDEN")
                ->setTitle("Forbidden.")
                ->setDetail($this->getMessage())
        ];
    }
}
