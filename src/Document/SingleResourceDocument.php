<?php

namespace CarterZenk\JsonApi\Document;

use WoohooLabs\Yin\JsonApi\Schema\Data\SingleResourceData;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Transformer\Transformation;

class SingleResourceDocument extends AbstractSuccessDocument
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
        return $this->linksFactory->createDocumentLinks();
    }

    /**
     * Returns the resource ID for the current domain object.
     * It is a shortcut of calling the resource transformer's getId() method.
     *
     * @return string
     */
    public function getResourceId()
    {
        $transformer = $this->container->get($this->modelClass);

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
        $transformer = $this->container->get($this->modelClass);

        $transformation->data->addPrimaryResource(
            $transformer->transformToResource($transformation, $this->domainObject)
        );
    }
}
