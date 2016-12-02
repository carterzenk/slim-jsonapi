<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\Model;

class TransformerBuilder implements TransformerBuilderInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * TransformerBuilder constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        // TODO: Implement getType() method.
    }

    /**
     * @inheritdoc
     */
    public function getIdKey()
    {
        // TODO: Implement getIdKey() method.
    }

    /**
     * @inheritdoc
     */
    public function getDefaultIncludedRelationships()
    {
        // TODO: Implement getDefaultIncludedRelationships() method.
    }

    /**
     * @inheritdoc
     */
    public function getAttributesTransformer()
    {
        // TODO: Implement getAttributesTransformer() method.
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipsTransformer()
    {
        // TODO: Implement getRelationshipsTransformer() method.
    }
}
