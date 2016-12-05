<?php

namespace CarterZenk\JsonApi\Document;

use WoohooLabs\Yin\JsonApi\Schema\Data\SingleResourceData;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Transformer\Transformation;

class SingleResourceDocument extends AbstractSuccessfulDocument
{
    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getLinks()
    {
        $links = $this->createLinks();
        $links->setSelf(new Link($this->path));

        return $links;
    }

    /**
     * Returns the resource ID for the current domain object.
     * It is a shortcut of calling the resource transformer's getId() method.
     *
     * @return string
     */
    public function getResourceId()
    {
        $transformer = $this->container->getTransformer($this->domainObject);
        return $transformer->getId($this->domainObject);
    }

    /**
     * @inheritdoc
     */
    protected function createData()
    {
        return new SingleResourceData();
    }

    /**
     * @inheritdoc
     */
    protected function fillData(Transformation $transformation)
    {
        $transformer = $this->container->getTransformer($this->domainObject);
        $transformation->data->addPrimaryResource(
            $transformer->transformToResource($transformation, $this->domainObject)
        );
    }

    /**
     * @inheritdoc
     */
    protected function getRelationshipContentInternal(
        $relationshipName,
        Transformation $transformation,
        array $additionalMeta = []
    ) {
        $transformer = $this->container->getTransformer($this->domainObject);

        return $transformer->transformRelationship(
            $relationshipName,
            $transformation,
            $this->domainObject,
            $additionalMeta
        );
    }
}
