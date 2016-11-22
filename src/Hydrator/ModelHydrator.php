<?php

namespace CarterZenk\JsonApi\Hydrator;

use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\RelationshipParser;

class ModelHydrator
{
    /**
     * @inheritdoc
     */
    public function setId(Model $model, $id)
    {
        return $model;
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipHydrator(Model $model)
    {
        $relationshipParser = new RelationshipParser($model);
        return $relationshipParser->getRelationshipHydrators();
    }

    /**
     * @inheritdoc
     */
    public function getAttributeHydrator(Model $model)
    {
        $hydrators = [];

        foreach ($model->getFillable() as $fillableAttribute) {
            $hydrators[$fillableAttribute] = function (Model $model, $attribute, $data, $attributeName) {
                $model->setAttribute($attributeName, $attribute);
                return $model;
            };
        }

        return $hydrators;
    }
}
