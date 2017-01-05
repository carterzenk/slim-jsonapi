<?php

namespace CarterZenk\JsonApi\Transformer;

use Illuminate\Database\Eloquent\Relations\Relation;

interface BuilderInterface
{
    /**
     * This function should return the type to use with a model.
     *
     * @return string
     */
    public function getType();

    /**
     * This function should return the model's plural type.
     *
     * @return string
     */
    public function getPluralType();

    /**
     * @return string[]
     */
    public function getForeignKeys();

    /**
     * @return Relation[]
     */
    public function getRelations();
}
