<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\StringHelper;

trait TypeTrait
{
    /**
     * Returns the type of a model.
     *
     * @param Model $model
     * @return null|string
     */
    public function getModelType(Model $model)
    {
        $resourceType = $model->getResourceType();

        if (isset($resourceType) && is_string($resourceType)) {
            return $resourceType;
        } else {
            // By default, use a slug-cased string representing the model's class.
            $reflection = new \ReflectionClass($this->model);
            return StringHelper::slugCase($reflection->getShortName());
        }
    }
}