<?php

namespace CarterZenk\JsonApi\Schema;

use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

interface SchemaInterface extends ResourceTransformerInterface
{
    /**
     * This function should return a boolean indicating if the given type is valid.
     *
     * @param string $type
     * @return bool
     */
    public function isValidType(string $type);

    /**
     * This function should return a boolean indicating whether or not the relationship
     * can be hydrated.
     *
     * @param string $relationshipMethodName
     * @return bool
     */
    public function isRelationshipFillable(string $relationshipMethodName);

    /**
     * This function should return a boolean indicating whether or not a given attribute
     * can be hydrated.
     *
     * @param string $attributeKey
     * @return bool
     */
    public function isAttributeFillable(string $attributeKey);
}