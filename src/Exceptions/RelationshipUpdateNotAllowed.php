<?php

namespace CarterZenk\JsonApi\Exceptions;

use WoohooLabs\Yin\JsonApi\Exception\JsonApiException;
use WoohooLabs\Yin\JsonApi\Schema\Error;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;

class RelationshipUpdateNotAllowed extends JsonApiException
{
    /**
     * @var ErrorSource|null
     */
    protected $source;

    /**
     * RelationshipUpdateUnsupported constructor.
     * @param string $relationshipName
     * @param ErrorSource|null $source
     */
    public function __construct($relationshipName, ErrorSource $source = null)
    {
        parent::__construct('Updating the '.$relationshipName.' relationship is not allowed!');

        $this->source = $source;
    }

    /**
     * @inheritdoc
     */
    protected function getErrors()
    {
        $error = Error::create()
            ->setStatus(403)
            ->setCode("FORBIDDEN")
            ->setTitle("Relationship Update Not Allowed")
            ->setDetail($this->getMessage());

        if (isset($this->source)) {
            $error->setSource($this->source);
        }

        return [$error];
    }
}
