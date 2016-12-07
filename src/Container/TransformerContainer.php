<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Container\AbstractContainer;

class Container extends AbstractContainer
{
    /**
     * @var string
     */
    protected $baseUri;

    /**
     * Container constructor.
     * @param string $baseUri
     */
    public function __construct($baseUri)
    {
        $this->baseUri = $baseUri;

        parent::__construct();
    }

    protected function getBuilderCallable($modelClass) {
        return function ($container) use ($modelClass) {
            $model = new $modelClass();

            $builder = new TransformerBuilder($model, $container, $this->baseUri);

            return new ResourceTransformer(
                $builder->getType(),
                $builder->getIdKey(),
                $this->baseUri,
                $builder->getAttributesToHide(),
                $builder->getDefaultIncludedRelationships(),
                $builder->getRelationshipsTransformer($container)
            );
        };
    }
}
