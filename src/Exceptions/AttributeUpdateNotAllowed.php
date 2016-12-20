<?php

namespace CarterZenk\JsonApi\Exceptions;

use WoohooLabs\Yin\JsonApi\Exception\JsonApiException;
use WoohooLabs\Yin\JsonApi\Schema\Error;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;

class AttributeUpdateNotAllowed extends JsonApiException
{
    /**
     * @var string
     */
    protected $attributeName;

    /**
     * AttributeUpdateNotAllowed constructor.
     * @param string $attributeName
     */
    public function __construct($attributeName)
    {
        parent::__construct('Updating the '.$attributeName.' attribute is not allowed!');
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
                ->setTitle("Attribute Update Not Allowed")
                ->setDetail($this->getMessage())
                ->setSource(ErrorSource::fromPointer('/data/attributes'.$this->attributeName))
        ];
    }
}
