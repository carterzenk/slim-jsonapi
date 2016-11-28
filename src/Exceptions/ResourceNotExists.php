<?php

namespace CarterZenk\JsonApi\Exceptions;

use WoohooLabs\Yin\JsonApi\Exception\JsonApiException;
use WoohooLabs\Yin\JsonApi\Schema\Error;

class ResourceNotExists extends JsonApiException
{
    protected $type;

    protected $id;

    public function __construct($type, $id)
    {
        $this->id = (string) $id;
        $this->type = ucwords($type);

        $message = $this->type.' '.$id.' is not available.';
        parent::__construct($message);
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
                ->setTitle($this->type.' Not Found')
                ->setDetail($this->getMessage())
                ->setId($this->id)
        ];
    }
}
