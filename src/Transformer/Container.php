<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Exceptions\InvalidDomainObjectException;
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
        $modelClass = $this->getModelClass($domainObject);

        if (!$this->offsetExists($modelClass)) {
            $this->offsetSet($modelClass, function (Container $container) use ($modelClass) {
                return $this->createResourceTransformer($modelClass, $container);
            });
        }

        return $this->offsetGet($modelClass);
    }

    /**
     * @param $domainObject
     * @return string
     * @throws InvalidDomainObjectException
     */
    private function getModelClass($domainObject)
    {
        if ($domainObject instanceof Model) {
            return get_class($domainObject);
        } elseif ($domainObject instanceof Collection || $domainObject instanceof Paginator) {
            return $domainObject->getQueueableClass();
        } else {
            throw new InvalidDomainObjectException($domainObject);
        }
    }

    /**
     * @param $modelClass
     * @param ContainerInterface $container
     * @return ResourceTransformer
     */
    private function createResourceTransformer($modelClass, ContainerInterface $container)
    {
        $model = new $modelClass();

        $builder = new Builder($model, $container, $this->baseUri);

        return new ResourceTransformer(
            $builder->getType(),
            $builder->getIdKey(),
            $this->baseUri,
            $builder->getAttributesToHide(),
            $builder->getDefaultIncludedRelationships(),
            $builder->getRelationshipsTransformer($container)
        );
    }
}
