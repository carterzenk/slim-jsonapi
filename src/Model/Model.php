<?php

namespace CarterZenk\JsonApi\Model;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel implements ModelInterface
{
    /**
     * @var array
     */
    protected $fillableRelationships = [];

    /**
     * @var array
     */
    protected $visibleRelationships = [];

    /**
     * @var string|null
     */
    protected $resourceType;

    /**
     * @inheritdoc
     */
    public function getFillableRelationships()
    {
        return $this->fillableRelationships;
    }

    /**
     * @inheritdoc
     */
    public function getVisibleRelationships()
    {
        return $this->visibleRelationships;
    }

    /**
     * @inheritdoc
     */
    public function addFillableRelationship($name)
    {
        $this->fillableRelationships[] = $name;
    }

    /**
     * @inheritdoc
     */
    public function removeFillableRelationship($name)
    {
        if (($key = array_search($name, $this->fillableRelationships)) !== false) {
            unset($this->fillableRelationships[$key]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getDefaultIncludedRelationships()
    {
        return $this->with;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }
}
