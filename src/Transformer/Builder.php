<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\RelationshipHelperTrait;
use CarterZenk\JsonApi\Model\StringHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;

class Builder implements BuilderInterface
{
    //use RelationshipHelperTrait;
    use TypeTrait;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $pluralType;

    /**
     * @var Relation[]
     */
    protected $relations;

    /**
     * @var string[]
     */
    protected $foreignKeys;

    /**
     * TransformerBuilder constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->type = $this->getModelType($model);

        if ($model->getRelationMethods() !== null) {
            $relationMethods = $model->getRelationMethods();
        } else {
            $relationMethods = $this->getRelationMethodsFromChildMethods($model);
        }

        $this->setRelationsFromMethods($relationMethods, $model);
    }

    /**
     * @param Model $model
     * @return \string[]
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
     * This function should return methods defined in the child model class.
     *
     * @param Model $model
     * @return \string[]
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
     * @param array $methodNames
     * @param Model $model
     */
    private function setRelationsFromMethods(array $methodNames, Model $model)
    {
        foreach ($methodNames as $methodName) {

            $relation = $model->$methodName();

            if ($relation instanceof Relation && $model->isRelationshipVisible($methodName)) {
                $this->relations[$methodName] = $relation;
            }

            if ($relation instanceof BelongsTo) {
                $this->foreignKeys[] = $relation->getForeignKey();
            }
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getPluralType()
    {
        return StringHelper::pluralize($this->type);
    }

    /**
     * @return string[]
     */
    public function getForeignKeys()
    {
        return $this->foreignKeys;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\Relation[]
     */
    public function getRelations()
    {
        return $this->relations;
    }
}
