<?php

namespace CarterZenk\JsonApi\Document;

use WoohooLabs\Yin\JsonApi\Schema\Data\CollectionData;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Transformer\Transformation;

class CollectionResourceDocument extends AbstractSuccessfulDocument
{
    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        return [
            'page' => [
                'number' => $this->domainObject->getPage(),
                'size' => $this->domainObject->getSize(),
                'total' => $this->domainObject->getTotalItems()
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getLinks()
    {
        $links = $this->createLinks();
        $links->setSelf(new Link($this->path));
        $links->setPagination($this->path, $this->domainObject);

        return $links;
    }

    /**
     * @inheritdoc
     */
    protected function createData()
    {
        return new CollectionData();
    }

    /**
     * @return bool
     */
    protected function hasItems()
    {
        return empty($this->getItems()) === false;
    }

    /**
     * @return mixed
     */
    protected function getItems()
    {
        return $this->domainObject;
    }

    /**
     * @inheritdoc
     */
    protected function fillData(Transformation $transformation)
    {
        $transformer = $this->container->getTransformer($this->model);
        foreach ($this->getItems() as $item) {
            $transformation->data->addPrimaryResource($transformer->transformToResource($transformation, $item));
        }
    }

    /**
     * @inheritdoc
     */
    protected function getRelationshipContentInternal(
        $relationshipName,
        Transformation $transformation,
        array $additionalMeta = []
    ) {
        if ($this->hasItems() === false) {
            return [];
        }

        $transformer = $this->container->getTransformer($this->model);
        $result = [];

        foreach ($this->getItems() as $item) {
            $result[] = $transformer->transformRelationship(
                $relationshipName,
                $transformation,
                $item,
                $additionalMeta
            );
        }

        return $result;
    }
}
