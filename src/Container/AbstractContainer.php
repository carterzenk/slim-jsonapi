<?php

namespace CarterZenk\JsonApi\Container;

use CarterZenk\JsonApi\Exceptions\InvalidDomainObjectException;
use CarterZenk\JsonApi\Transformer\ContainerInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Pimple\Container;

abstract class AbstractContainer extends Container implements ContainerInterface
{
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
     * @param string $modelClass
     * @return callable
     */
    protected abstract function getBuilderCallable($modelClass);
}