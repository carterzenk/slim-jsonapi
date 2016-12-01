<?php

namespace CarterZenk\JsonApi\Exceptions;

use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;
use WoohooLabs\Yin\JsonApi\Schema\ResourceIdentifier;

class RelatedResourceNotFound extends ResourceNotExists
{
    /**
     * RelatedResourceNotFound constructor.
     * @param ResourceIdentifier $identifier
     * @param ErrorSource $source
     */
    public function __construct(ResourceIdentifier $identifier, ErrorSource $source) {
        $this->source = $source;
        $this->statusCode = 400;

        parent::__construct($identifier);
    }
}
