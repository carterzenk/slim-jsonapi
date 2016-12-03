<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\Model;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

interface ContainerInterface
{
    /**
     * @param mixed $model
     * @return ResourceTransformerInterface
     */
    public function getTransformer($model);
}
