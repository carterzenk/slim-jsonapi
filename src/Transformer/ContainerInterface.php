<?php

namespace CarterZenk\JsonApi\Transformer;

use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

interface ContainerInterface
{
    /**
     * This function should return a ResourceTransformer object
     * built with the domainObject's model class.
     *
     * @param mixed $domainObject
     * @return ResourceTransformerInterface
     */
    public function get($domainObject);
}
