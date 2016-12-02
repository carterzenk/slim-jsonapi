<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\Model;

interface TransformerContainerInterface
{
    /**
     * @param Model $model
     * @return ResourceTransformerInterface
     */
    public function getTransformer(Model $model);
}
