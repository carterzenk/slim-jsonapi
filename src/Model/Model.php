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
     * @var array
     */
    protected $defaultIncludedRelationships = [];

    /**
     * @var string|null
     */
    protected $resourceType = null;

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
    public function getDefaultIncludedRelationships()
    {
        return $this->defaultIncludedRelationships;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }
}
