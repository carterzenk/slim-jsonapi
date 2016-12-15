<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\StringHelper;
use Illuminate\Database\Eloquent\Model;

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
        $reflection = new \ReflectionClass($model);
        return StringHelper::slugCase($reflection->getShortName());
    }
}