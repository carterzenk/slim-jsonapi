<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Pimple\Container as PimpleContainer;

class Container extends PimpleContainer implements ContainerInterface
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

    /**
     * @inheritdoc
     */
    public function getTransformer($domainObject)
    {
        if ($domainObject instanceof Model) {
            $modelClass = get_class($domainObject);
            $model = $domainObject;
        } elseif ($domainObject instanceof Collection || $domainObject instanceof Paginator) {
            $modelClass = $domainObject->getQueueableClass();
            $model = new $modelClass();
        } else {
            throw new \InvalidArgumentException(get_class($domainObject).' is not a compatible type.');
        }

        if (!$this->offsetExists($modelClass)) {
            $this->offsetSet($modelClass, function (Container $container) use ($model) {
                $builder = new Builder($model, $container, $this->baseUri);

                return new ResourceTransformer(
                    $builder->getType(),
                    $builder->getIdKey(),
                    $this->baseUri,
                    $builder->getAttributesToHide(),
                    $builder->getDefaultIncludedRelationships(),
                    $builder->getRelationshipsTransformer($container)
                );
            });
        }

        return $this->offsetGet($modelClass);
    }
}