<?php

namespace CarterZenk\JsonApi\Transformer;

use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

interface ContainerInterface
{
    /**
     * @param mixed $domainObject
     * @return ResourceTransformerInterface
     */
    public function getTransformer($domainObject);
}
