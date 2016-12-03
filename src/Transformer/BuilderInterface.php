<?php

namespace CarterZenk\JsonApi\Transformer;

interface BuilderInterface
{
    /**
     * This function should return the type to use with a model.
     *
     * @return string
     */
    public function getType();

    /**
     * This function should return the model's id key.
     *
     * @return string
     */
    public function getIdKey();

    /**
     * This function should return the default included relationships in slug-case.
     *
     * @return string[]
     */
    public function getDefaultIncludedRelationships();

    /**
     * This function should return the additional attributes that should be hidden from
     * the data/attributes section of the document.  This enables the hiding of foreign
     * and primary keys.
     *
     * @return string[]
     */
    public function getAttributesToHide();

    /**
     * This function should return a callable array with the key set to a slug-cased
     * relationship method name, and the value set to a callable which returns a
     * relationships schema object.
     *
     * @param Container $container
     * @return \callable[]
     */
    public function getRelationshipsTransformer(Container $container);
}
