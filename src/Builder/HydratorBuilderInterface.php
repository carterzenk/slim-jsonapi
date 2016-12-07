<?php

namespace CarterZenk\JsonApi\Builder;

interface HydratorBuilderInterface
{
    /**
     * @return callable[]
     */
    public function getAttributeHydrator();

    /**
     * @return callable[]
     */
    public function getRelationshipHydrator();
}
