<?php

namespace CarterZenk\JsonApi\Model;

trait Relationable
{
    /**
     * @var array|null
     */
    protected $relationMethods;

    /**
     * @var array
     */
    protected $fillableRelations = [];

    /**
     * @var array
     */
    protected $guardedRelations = ['*'];

    /**
     * @return array|null
     */
    public function getRelationMethods()
    {
        return $this->relationMethods;
    }

    /**
     * @return array
     */
    public function getFillableRelations()
    {
        return $this->fillableRelations;
    }

    /**
     * @param array $fillableRelations
     */
    public function setFillableRelations(array $fillableRelations)
    {
        $this->fillableRelations = $fillableRelations;
    }

    /**
     * @return array
     */
    public function getGuardedRelations()
    {
        return $this->guardedRelations;
    }

    /**
     * @param array $guardedRelations
     */
    public function setGuardedRelations(array $guardedRelations)
    {
        $this->guardedRelations = $guardedRelations;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isRelationFillable($key)
    {
        if (in_array($key, $this->getFillableRelations())) {
            return true;
        }

        if ($this->isRelationGuarded($key)) {
            return false;
        }

        return empty($this->getFillableRelations());
    }

    /**
     * @param $key
     * @return bool
     */
    public function isRelationGuarded($key)
    {
        return in_array($key, $this->getGuardedRelations()) || $this->getGuardedRelations() == ['*'];
    }
}
