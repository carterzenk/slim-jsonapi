<?php

namespace CarterZenk\JsonApi\Hydrator;

use CarterZenk\JsonApi\Exceptions\RelatedResourceNotFound;
use CarterZenk\JsonApi\Model\RelationshipHelperTrait;
use CarterZenk\JsonApi\Model\StringHelper;
use CarterZenk\JsonApi\Transformer\TypeTrait;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\AbstractHydrator;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship as ToManyHydrator;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship as ToOneHydrator;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;
use WoohooLabs\Yin\JsonApi\Schema\ResourceIdentifier;

class ResourceHydrator extends AbstractHydrator implements HydratorInterface
{
    use RelationshipHelperTrait;
    use TypeTrait;

    /*
     * @var string
     */
    protected $modelType;

    /**
     * @param RequestInterface $request
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param mixed $domainObject
     * @return mixed
     */
    public function hydrate(
        RequestInterface $request,
        ExceptionFactoryInterface $exceptionFactory,
        $domainObject
    ) {
        $this->modelType = $this->getModelType($domainObject);

        return parent::hydrate($request, $exceptionFactory, $domainObject);
    }

    /**
     * @param string $clientGeneratedId
     * @param RequestInterface $request
     * @param ExceptionFactoryInterface $exceptionFactory
     * @throws \Exception
     */
    protected function validateClientGeneratedId(
        $clientGeneratedId,
        RequestInterface $request,
        ExceptionFactoryInterface $exceptionFactory
    ) {
        if ($clientGeneratedId !== null) {
            throw $exceptionFactory->createClientGeneratedIdNotSupportedException($request, $clientGeneratedId);
        }
    }

    /**
     * @return string
     */
    protected function generateId()
    {
        return uniqid();
    }

    /**
     * @return string
     */
    protected function getAcceptedType()
    {
        return $this->modelType;
    }

    /**
     * @param mixed $domainObject
     * @param string $id
     * @return mixed|null
     */
    protected function setId($domainObject, $id)
    {
        return $domainObject;
    }

    /**
     * @param mixed $domainObject
     * @return \callable[]
     */
    protected function getAttributeHydrator($domainObject)
    {
        $fillable = $domainObject->getFillable();

        if (!empty($fillable)) {
            return $this->getAttributeHydratorFromFillable($domainObject, $fillable);
        } else {
            return $this->getAttributeHydratorFromGuarded($domainObject);
        }
    }

    /**
     * @param Model $model
     * @param array $fillable
     * @return callable[]
     */
    protected function getAttributeHydratorFromFillable(Model $model, array $fillable)
    {
        $hydrators = [];

        foreach ($fillable as $fillableAttribute) {
            // If the attribute is a relation method, do not add it to
            // the attribute hydrators.
            if (method_exists($model, $fillableAttribute)) {
                continue;
            }

            $hydrators[$fillableAttribute] = $this->getAttributeHydratorCallable();
        }

        return $hydrators;
    }

    /**
     * @param Model $model
     * @return callable[]
     */
    protected function getAttributeHydratorFromGuarded(Model $model)
    {
        $hydrators = [];

        $table = $model->getTable();
        $columns = Manager::schema()->getColumnListing($table);

        foreach ($columns as $column) {
            if ($model->isFillable($column)) {
                $hydrators[$column] = $this->getAttributeHydratorCallable();
            }
        }

        return $hydrators;
    }

    /**
     * @return callable
     */
    protected function getAttributeHydratorCallable()
    {
        return function (Model $model, $attribute, $data, $attributeName) {
            $model->setAttribute($attributeName, $attribute);
            return $model;
        };
    }

    /**
     * @param mixed $domainObject
     * @return \callable[]
     */
    protected function getRelationshipHydrator($domainObject)
    {
        $hydrators = [];

        foreach ($this->getRelations($domainObject) as $name => $relation) {
            if (!$domainObject->isFillable($name)) {
                continue;
            }

            if ($this->isToOne($relation)) {
                $hydratorCallable = $this->getToOneHydratorCallable($name, $relation);
            } elseif ($this->isToMany($relation)) {
                $hydratorCallable = $this->getToManyHydratorCallable($name, $relation);
            }

            if (isset($hydratorCallable)) {
                $keyName = StringHelper::slugCase($name);
                $hydrators[$keyName] = $hydratorCallable;
            }
        }

        return $hydrators;
    }

    /**
     * @param $name
     * @param Relation $relation
     * @return callable
     */
    protected function getToOneHydratorCallable($name, Relation $relation)
    {
        return function (
            Model $model,
            ToOneHydrator $relationship
        ) use (
            $name,
            $relation
        ) {
            $resourceIdentifier = $relationship->getResourceIdentifier();

            try {
                $relatedModel = $relation
                    ->getRelated()
                    ->newQuery()
                    ->findOrFail($resourceIdentifier->getId());

                if ($relation instanceof HasOne) {
                    $model->$name()->save($relatedModel);
                } else {
                    $model->$name()->associate($relatedModel);
                }

                return $model;
            } catch (ModelNotFoundException $modelNotFoundException) {
                throw $this->createRelatedResourceNotExists($name, $resourceIdentifier);
            }
        };
    }

    /**
     * @param string $name
     * @param Relation $relation
     * @return callable
     */
    protected function getToManyHydratorCallable($name, Relation $relation)
    {
        return function (
            Model $model,
            ToManyHydrator $relationship
        ) use (
            $name,
            $relation
        ) {
            $relatedModels = $relation
                ->getRelated()
                ->newQuery()
                ->findMany($relationship->getResourceIdentifierIds());

            foreach ($relationship->getResourceIdentifiers() as $resourceIdentifier) {
                if (!$relatedModels->contains($resourceIdentifier->getId())) {
                    throw $this->createRelatedResourceNotExists($name, $resourceIdentifier);
                }
            }

            $model->$name()->saveMany($relatedModels);

            return $model;
        };
    }

    /**
     * @param string $name
     * @param ResourceIdentifier $identifier
     * @return RelatedResourceNotFound
     */
    protected function createRelatedResourceNotExists($name, ResourceIdentifier $identifier)
    {
        $pointer = '/data/relationships/'.StringHelper::slugCase($name);
        $source = ErrorSource::fromPointer($pointer);

        return new RelatedResourceNotFound($identifier, $source);
    }
}
