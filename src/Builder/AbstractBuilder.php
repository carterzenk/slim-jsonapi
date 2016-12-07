<?php

namespace CarterZenk\JsonApi\Builder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;

abstract class AbstractBuilder
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $relations;


    public function __construct(Model $model) {
        $this->model = $model;

        $this->setRelations();
    }

    protected function setRelations()
    {
        $relations = [];

        $modelClass = get_class($this->model);
        $skipMethods = get_class_methods(Model::class);
        $modelMethods = get_class_methods($modelClass);
        $childMethods = array_diff($modelMethods, $skipMethods);

        foreach (class_uses($modelClass) as $modelTrait) {
            $childMethods = array_diff($childMethods, get_class_methods($modelTrait));
        }

        foreach ($childMethods as $methodName) {
            if (substr($methodName, -9) == 'Attribute') {
                continue;
            }

            if (substr($methodName, 0, 5) == 'scope') {
                continue;
            }

            $reflection = new \ReflectionMethod($this->model, $methodName);
            if ($reflection->getNumberOfParameters() != 0) {
                continue;
            }

            $relation = $this->model->$methodName();

            if ($relation instanceof Relation) {
                $relations[$methodName] = $relation;
            }
        }

        return $relations;
    }

    protected function isRelationshipLoaded(Model $model, $name)
    {
        return $model->relationLoaded($name);
    }


    /**
     * @param mixed $object
     * @return bool
     */
    protected function isRelation($object)
    {
        return $object instanceof Relation;
    }

    /**
     * @param Relation $relation
     * @return bool
     */
    protected function isBelongsTo(Relation $relation)
    {
        return $relation instanceof BelongsTo;
    }

    /**
     * @param Relation $relation
     * @return bool
     */
    protected function isHasOne(Relation $relation)
    {
        return $relation instanceof HasOne;
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
