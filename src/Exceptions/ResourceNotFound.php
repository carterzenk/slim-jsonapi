<?php

namespace CarterZenk\JsonApi\Exceptions;

use WoohooLabs\Yin\JsonApi\Exception\JsonApiException;
use WoohooLabs\Yin\JsonApi\Schema\Error;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;

class ResourceNotFound extends JsonApiException
{
    /**
     * @var int
     */
    protected $statusCode = 404;

    /**
     * @var ErrorSource
     */
    protected $source;

    /**
     * ResourceNotFound constructor.
     * @param string|null $message
     */
    public function __construct($message = null)
    {
        parent::__construct(
            isset($message) ? $message : "The requested resource is not found!"
        );
    }

    /**
     * @inheritDoc
     */
    protected function getErrors()
    {
        $error = Error::create()
            ->setStatus($this->statusCode)
            ->setCode("RESOURCE_NOT_FOUND")
            ->setTitle("Resource Not Found")
            ->setDetail($this->getMessage());

        if (isset($this->source)) {
            $error->setSource($this->source);
        }

        return [$error];
    }
}
