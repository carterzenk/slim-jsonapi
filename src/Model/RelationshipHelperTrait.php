<?php

namespace CarterZenk\JsonApi\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;

trait RelationshipHelperTrait
{
    /**
     * @param Model $model
     * @param string $name
     * @return Relation
     * @throws RelationshipNotExists
     */
    protected function getRelation(Model $model, $name)
    {
        if (!method_exists($model, $name)) {
            throw $this->createRelationshipNotExistsException($name);
        }

        $relation = $model->$name();

        if (!$this->isRelation($relation)) {
            throw $this->createRelationshipNotExistsException($name);
        }

        return $relation;
    }

    /**
     * @param $name
     * @return RelationshipNotExists
     */
    private function createRelationshipNotExistsException($name)
    {
        return new RelationshipNotExists($this->getSlugCase($name));
    }

    /**
     * @param $name
     * @return string
     */
    protected function getSlugCase($name)
    {
        return Str::slug(Str::snake(ucwords($name)));
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
    protected function isKeyInAttributes(Relation $relation)
    {
        return $relation instanceof BelongsTo;
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
