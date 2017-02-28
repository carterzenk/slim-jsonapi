<?php

namespace CarterZenk\JsonApi\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

use CarterZenk\JsonApi\Model\StringHelper;

class Model extends Eloquent
{
    /**
     * The relation methods used in the model.
     *
     * @var array|null
     */
    protected $relationMethods;

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
     * Get the relation methods defined in the model.
     *
     * @return array|null
     */
    public function getRelationMethods()
    {
        return $this->relationMethods;
    }

    /**
     * @inheritdoc
     */
    public function getFillableRelationships()
    {
        return $this->fillableRelationships;
    }

    /**
     * Returns a boolean indicating whether or not the relationship is fillable.
     *
     * @param $name
     * @return bool
     */
    public function isRelationshipFillable($name)
    {
        return in_array($name, $this->fillableRelationships);
    }

    /**
     * Returns an array of valid types for the given relationship.
     * For example, a Contact model could return ['contact', 'borrower'].
     * 
     * @param type $relationshipName
     * @return array
     */
    public function getValidTypes() {
        return [];
    }
    
    /**
     * @inheritdoc
     */
    public function getVisibleRelationships()
    {
        return $this->visibleRelationships;
    }

    /**
     * Returns a boolean indicating whether or not the relationship is visible.
     *
     * @param $name
     * @return bool
     */
    public function isRelationshipVisible($name)
    {
        return in_array($name, $this->visibleRelationships);
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
