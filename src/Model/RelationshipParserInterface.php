<?php

namespace CarterZenk\JsonApi\Model;

use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

interface RelationshipParserInterface
{
    /**
     * Should return the relationship callable array that should
     * be used with the transformer.
     *
     * @param ResourceTransformerInterface $transformer
     * @return callable[]
     */
    public function getRelationships(ResourceTransformerInterface $transformer);

    /**
     * Should return the relationship callable array that should
     * be used with the hydrator.
     *
     * @return callable[]
     */
    public function getRelationshipHydrators();
}
