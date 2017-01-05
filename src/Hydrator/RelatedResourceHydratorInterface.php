<?php

namespace CarterZenk\JsonApi\Hydrator;

interface RelatedResourceHydratorInterface
{
    public function hydrateRelatedResource($request, $model, $relationshipName)
}