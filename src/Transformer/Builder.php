<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\Model;
use Illuminate\Support\Str;

class Builder implements BuilderInterface
{
    use RelationshipBuilderTrait;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * TransformerBuilder constructor.
     * @param Model $model
     * @param ContainerInterface $container
     * @param string $baseUri
     */
    public function __construct(Model $model, ContainerInterface $container, $baseUri)
    {
        $this->model = $model;
        $this->container = $container;
        $this->baseUri = $baseUri;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        $resourceType = $this->model->getResourceType();

        if (isset($resourceType) && is_string($resourceType)) {
            return $resourceType;
        } else {
            // By default, use a slug-cased string representing the model's class.
            $reflection = new \ReflectionClass($this->model);
            return Str::slug(Str::snake($reflection->getShortName()));
        }
    }

    /**
     * @inheritdoc
     */
    public function getIdKey()
    {
        return $this->model->getKeyName();
    }

    /**
     * @inheritdoc
     */
    public function getDefaultIncludedRelationships()
    {
        $defaultIncludedRelationships = [];

        foreach ($this->model->getDefaultIncludedRelationships() as $includedRelationship) {
            $defaultIncludedRelationships[] = Str::slug(Str::snake(ucwords($includedRelationship)));
        }

        return $defaultIncludedRelationships;
    }

    /**
     * @inheritdoc
     */
    public function getAttributesToHide()
    {
        $hiddenAttributes = $this->getForeignKeys($this->model);
        $hiddenAttributes[] = $this->getIdKey();

        return $hiddenAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipsTransformer(Container $container)
    {
        return $this->getRelationshipsFromModel($this->model, $container);
    }
}
