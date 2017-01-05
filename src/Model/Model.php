<?php

namespace CarterZenk\JsonApi\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{
    /**
     * The relation methods used in the model.
     *
     * @var array|null
     */
    protected $relationMethods;

    /**
     * The relationships that are mass assignable.
     *
     * @var array
     */
    protected $assignable = [];

    /**
     * The relationships that are fully replaceable.
     *
     * @var array
     */
    protected $replaceable = [];

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
     * Get the mass-assignable relations for the model.
     *
     * @return array
     */
    public function getAssignable()
    {
        return $this->assignable;
    }

    /**
     * Set the assignable relationships for the model.
     *
     * @param array $assignable
     * @return $this
     */
    public function setAssignable(array $assignable)
    {
        $this->assignable = $assignable;

        return $this;
    }

    /**
     * Add assignable relationships for the model.
     *
     * @param array|string|null $relations
     * @return void
     */
    public function addAssignable($relations = null)
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        $this->assignable = array_merge($this->assignable, $relations);
    }

    /**
     * Get the fully-replaceable relations for the model.
     *
     * @return array
     */
    public function getReplaceable()
    {
        return $this->replaceable;
    }

    /**
     * Set the fully-replaceable relations for the model.
     *
     * @param array $replaceable
     * @return $this
     */
    public function setReplaceable(array $replaceable)
    {
        $this->replaceable = $replaceable;

        return $this;
    }

    /**
     * Determine if the given relationship may be mass assigned.
     *
     * @param $key
     * @return bool
     */
    public function isAssignable($key)
    {
        if (static::$unguarded) {
            return true;
        }

        if (in_array($key, $this->getAssignable())) {
            return true;
        }

        if ($this->isGuarded($key)) {
            return false;
        }

        return empty($this->getAssignable());
    }

    /**
     * Determine if the given attribute/relationship is visible.
     *
     * @param $key
     * @return bool
     */
    public function isVisible($key)
    {
        if (count($this->getVisible()) > 0) {
            return array_key_exists($key, $this->getVisible());
        }

        if (count($this->getHidden()) > 0) {
            return !array_key_exists($key, $this->getHidden());
        }

        return true;
    }

    /**
     * Determine if the given relationship is fully replaceable.
     *
     * @param $key
     * @return bool
     */
    public function isReplaceable($key)
    {
        return array_key_exists($key, $this->replaceable);
    }
}
