<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Exceptions\InvalidDomainObjectException;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Pimple\Container as PimpleContainer;

class Container extends PimpleContainer implements ContainerInterface
{
    /**
     * @var LinksFactoryInterface
     */
    protected $linksFactory;

    /**
     * Container constructor.
     * @param LinksFactoryInterface $linksFactory
     */
    public function __construct(LinksFactoryInterface $linksFactory)
    {
        parent::__construct();

        $this->linksFactory = $linksFactory;
    }

    /*
     * @inheritdoc
     */
    public function get($domainObject)
    {
        $modelClass = $this->getModelClass($domainObject);

        if (!$this->offsetExists($modelClass)) {
            $this->offsetSet($modelClass, $this->getBuilderCallable($modelClass));
        }

        return $this->offsetGet($modelClass);
    }

    /**
     * @param mixed $domainObject
     * @return string
     * @throws InvalidDomainObjectException
     */
    protected function getModelClass($domainObject)
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
     * @param string $modelClass
     * @return callable
     */
    protected function getBuilderCallable($modelClass)
    {
        return function ($container) use ($modelClass) {
            $model = new $modelClass();

            $builder = new Builder($model, $this->linksFactory);

            return new ResourceTransformer(
                $builder->getType(),
                $builder->getIdKey(),
                $builder->getAttributesToHide(),
                $builder->getDefaultIncludedRelationships(),
                $builder->getRelationshipsTransformer($container),
                $this->linksFactory
            );
        };
    }
}
