<?php

namespace CarterZenk\JsonApi\Model;

interface ModelInterface
{
    /**
     * This function should return an array containing the relationship names that
     * can be hydrated.
     *
     * @return array
     */
    public function getFillableRelationships();

    /**
     * This function should return an array containing the relationship names that
     * the model should serialize for output.
     *
     * @return array
     */
    public function getVisibleRelationships();

    /**
     * Adds a fillable relationship to the model.
     *
     * @param $name
     */
    public function addFillableRelationship($name);

    /**
     * Removes a fillable relationship from the model.
     *
     * @param $name
     */
    public function removeFillableRelationship($name);

    /**
     * This function should return a string representing the model's resource type.
     * Null assumes the model should use its class name as a resource type, and
     * a string value will override this behavior with the given string.
     *
     * @return string
     */
    public function getResourceType();

    /**
     * This function should return an array containing the relationship names that
     * the model should always retrieve full resources for.
     * @return array
     */
    public function getDefaultIncludedRelationships();
}
