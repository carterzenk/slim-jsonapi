<?php

namespace CarterZenk\JsonApi\Transformer;

interface TransformerBuilderInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getIdKey();

    /**
     * @return string[]
     */
    public function getDefaultIncludedRelationships();

    /**
     * @return callable[]
     */
    public function getAttributesTransformer();

    /**
     * @return callable[]
     */
    public function getRelationshipsTransformer();
}
