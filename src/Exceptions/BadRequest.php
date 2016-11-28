<?php

namespace CarterZenk\JsonApi\Exceptions;

use WoohooLabs\Yin\JsonApi\Exception\JsonApiException;
use WoohooLabs\Yin\JsonApi\Schema\Error;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;

class BadRequest extends JsonApiException
{
    /**
     * @var ErrorSource
     */
    protected $source;

    /**
     * BadRequest constructor.
     * @param string $message
     * @param ErrorSource|null $source
     */
    public function __construct($message, ErrorSource $source = null)
    {
        $this->source = $source;

        parent::__construct($message);
    }

    /**
     * @inheritdoc
     */
    protected function getErrors()
    {
        $error = Error::create()
            ->setStatus(400)
            ->setCode("BAD_REQUEST")
            ->setTitle("Bad Request")
            ->setDetail($this->getMessage());

        if (isset($this->source)) {
            $error->setSource($this->source);
        }

        return [$error];
    }
}
