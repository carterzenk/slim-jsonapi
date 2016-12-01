<?php

namespace CarterZenk\JsonApi\Exceptions;

use WoohooLabs\Yin\JsonApi\Schema\ResourceIdentifier;

class ResourceNotExists extends ResourceNotFound
{
    /**
     * ResourceNotExists constructor.
     * @param ResourceIdentifier $identifier
     */
    public function __construct(ResourceIdentifier $identifier)
    {
        $type = $identifier->getType();
        $id = $identifier->getId();

        parent::__construct(
            'Resource '.$type.' with id '.$id.' was not found.'
        );
    }
}
