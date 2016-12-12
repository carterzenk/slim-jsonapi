<?php

namespace CarterZenk\JsonApi\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;

trait RelationshipHelperTrait
{
    /**
     * This function should return an array of relation methods with each
     * key as the method name, and the returned relation as a value.
     *
     * @param Model $model
     * @return Relation[]
     */
    protected function getRelations(Model $model)
    {
        if (method_exists($model, 'getRelationMethods')) {
            $relationMethods = $model->getRelationMethods();
        } else {
            $relationMethods = $this->getRelationMethodsFromChildMethods($model);
        }

        return $this->getRelationsFromMethods($model, $relationMethods);
    }

    /**
     * @param Model $model
     * @return string[]
     */
    private function getRelationMethodsFromChildMethods(Model $model)
    {
        $relationMethods = [];

        foreach ($this->getChildMethods($model) as $methodName) {
            // Filter out attribute getters/setters
            if (substr($methodName, -9) == 'Attribute') {
                continue;
            }

            // Filter out scope methods
            if (substr($methodName, 0, 5) == 'scope') {
                continue;
            }

            $reflection = new \ReflectionMethod($model, $methodName);

            // Filter out methods that use parameters
            if ($reflection->getNumberOfParameters() != 0) {
                continue;
            }

            $relationMethods[] = $methodName;
        }

        return $relationMethods;
    }

    /**
     * @param Model $model
     * @param array $methodNames
     * @return Relation[]
     */
    private function getRelationsFromMethods(Model $model, array $methodNames)
    {
        $relations = [];

        foreach ($methodNames as $methodName)
        {
            $relation = $model->$methodName();

            if ($relation instanceof Relation) {
                $relations[$methodName] = $relation;
            }
        }

        return $relations;
    }

    /**
     * This function should return methods defined in the child model class.
     *
     * @param Model $model
     * @return string[]
     */
    private function getChildMethods(Model $model)
    {
        $modelClass = get_class($model);

        // Filter out methods defined in Model class.
        $childMethods = array_diff(
            get_class_methods($modelClass),
            get_class_methods(Model::class)
        );

        // Filter out trait methods (scopes, soft deletes, etc).
        foreach (class_uses($modelClass) as $modelTrait) {
            $childMethods = array_diff($childMethods, get_class_methods($modelTrait));
        }

        return $childMethods;
    }

    /**
     * @param Relation $relation
     * @return bool
     */
    protected function isToOne(Relation $relation)
    {
        return $relation instanceof HasOne || $relation instanceof BelongsTo;
    }

    /**
     * @param Relation $relation
     * @return bool
     */
    protected function isToMany(Relation $relation)
    {
        return $relation instanceof HasMany || $relation instanceof BelongsToMany;
    }
}
