<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Schema\SchemaInterface;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

interface ContainerInterface
{
    /**
     * This function should return a Schema object
     * for the domainObject's model class.
     *
     * @param mixed $domainObject
     * @return SchemaInterface
     */
    public function get($domainObject);
}
