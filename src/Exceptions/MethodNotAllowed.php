<?php

namespace CarterZenk\JsonApi\Exceptions;

use WoohooLabs\Yin\JsonApi\Exception\JsonApiException;
use WoohooLabs\Yin\JsonApi\Schema\Error;

class MethodNotAllowed extends JsonApiException
{
    /**
     * MethodNotAllowed constructor.
     * @param array $availableMethods
     */
    public function __construct(array $availableMethods)
    {
        $allowed = implode(', ', $availableMethods);
        parent::__construct('Available methods: '.$allowed);
    }

    /**
     * @inheritdoc
     */
    protected function getErrors()
    {
        return [
            Error::create()
                ->setStatus(405)
                ->setCode("METHOD_NOT_ALLOWED")
                ->setTitle("Method not allowed.")
                ->setDetail($this->getMessage())
        ];
    }
}
