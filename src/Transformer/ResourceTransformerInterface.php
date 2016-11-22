<?php

namespace CarterZenk\JsonApi\Transformer;

use \WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface as YinResourceTransformerInterface;

interface ResourceTransformerInterface extends YinResourceTransformerInterface
{
    public function setBaseUri($baseUri);
}
