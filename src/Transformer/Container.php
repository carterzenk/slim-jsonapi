<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Exceptions\InvalidDomainObjectException;
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

        if ($this->offsetExists($modelClass) === false) {
            $this->offsetSet($modelClass, function ($container) use ($modelClass) {
                $model = new $modelClass();
                $builder = new Builder($model);

                return new ResourceTransformer(
                    $container,
                    $this->linksFactory,
                    $builder
                );
            });
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
        if (is_string($domainObject)) {
            return $domainObject;
        } else if ($domainObject instanceof Model) {
            return get_class($domainObject);
        } else {
            throw new \InvalidArgumentException(
                'Must use either model class name or model instance to retrieve transformer.'
            );
        }
    }
}
