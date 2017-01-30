<?php

namespace CarterZenk\JsonApi\Hydrator;

use CarterZenk\JsonApi\Exceptions\AttributeUpdateNotAllowed;
use CarterZenk\JsonApi\Exceptions\RelationshipUpdateNotAllowed;
use CarterZenk\JsonApi\Hydrator\Relationship\Factory\RelationshipHydratorFactory;
use CarterZenk\JsonApi\Hydrator\Relationship\Factory\RelationshipHydratorFactoryInterface;
use CarterZenk\JsonApi\Model\RelationshipHelperTrait;
use CarterZenk\JsonApi\Model\StringHelper;
use CarterZenk\JsonApi\Transformer\TypeTrait;
use CarterZenk\JsonApi\Model\Model;
use Illuminate\Support\Arr;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\HydratorInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Hydrator\UpdateRelationshipHydratorInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\Yin\JsonApi\Schema\ResourceIdentifier;

class ModelHydrator implements HydratorInterface, UpdateRelationshipHydratorInterface
{
    //use RelationshipHelperTrait;
    use TypeTrait;

    /**
     * @var ExceptionFactoryInterface
     */
    protected $exceptionFactory;

    /**
     * @var RelationshipHydratorFactoryInterface
     */
    protected $relationshipHydratorFactory;

    /**
     * ModelHydrator constructor.
     * @param RelationshipHydratorFactoryInterface $relationshipHydratorFactory
     */
    public function __construct(RelationshipHydratorFactoryInterface $relationshipHydratorFactory = null)
    {
        $this->relationshipHydratorFactory = $relationshipHydratorFactory ?: new RelationshipHydratorFactory();
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function hydrate(RequestInterface $request, ExceptionFactoryInterface $exceptionFactory, $domainObject)
    {
        $this->setExceptionFactory($exceptionFactory);

        $this->validateData($request);
        $this->validateType($request, $domainObject);

        $domainObject = $this->hydrateAttributes($request, $domainObject);

        if ($request->getMethod() === 'POST') {
            $domainObject->save();
        }

        return $this->hydrateRelationships($request, $domainObject);
    }

    /**
     * @param string $relationship
     * @param RequestInterface $request
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param mixed $domainObject
     * @return Model
     */
    public function hydrateRelationship(
        $relationship,
        RequestInterface $request,
        ExceptionFactoryInterface $exceptionFactory,
        $domainObject
    ) {
        $this->setExceptionFactory($exceptionFactory);

        $this->validateData($request);
        $data = $request->getParsedBody();

        return $this->doHydrateRelationship($relationship, $data, $request, $domainObject);
    }

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     */
    protected function setExceptionFactory(ExceptionFactoryInterface $exceptionFactory)
    {
        $this->exceptionFactory = $exceptionFactory;
    }

    /**
     * @param RequestInterface $request
     * @throws \Exception
     */
    protected function validateData(RequestInterface $request)
    {
        $data = $request->getResource();

        if ($data === null) {
            throw $this->exceptionFactory->createDataMemberMissingException($request);
        }
    }

    /**
     * @param RequestInterface $request
     * @param Model $domainObject
     * @throws \Exception
     */
    protected function validateType(RequestInterface $request, Model $domainObject)
    {
        $type = $request->getResourceType();

        if (is_null($type)) {
            throw $this->exceptionFactory->createResourceTypeMissingException();
        }

        $expectedType = $this->getModelType($domainObject);

        if ($type !== $expectedType) {
            throw $this->exceptionFactory->createResourceTypeUnacceptableException(
                $type,
                [$expectedType]
            );
        }
    }

    /**
     * @param RequestInterface $request
     * @param Model $domainObject
     * @return Model
     * @throws AttributeUpdateNotAllowed
     */
    protected function hydrateAttributes(RequestInterface $request, Model $domainObject)
    {
        $attributes = $request->getResourceAttributes();

        if (empty($attributes)) {
            return $domainObject;
        }

        foreach ($attributes as $attributeKey => $attributeValue) {
            // Throw exception if the client is trying to set a relationship as an attribute.
            if (method_exists($domainObject, $attributeKey)) {
                throw new AttributeUpdateNotAllowed($attributeKey);
            }

            // Skip over null attributes.
            if (is_null($attributeValue)) {
                continue;
            }

            // Throw exception if the client is trying to set an attribute which is not fillable.
            if ($domainObject->isFillable($attributeKey) === false) {
                throw new AttributeUpdateNotAllowed($attributeKey);
            }

            $domainObject = $domainObject->setAttribute($attributeKey, $attributeValue);
        }

        return $domainObject->fill($attributes);
    }

    /**
     * @param RequestInterface $request
     * @param Model $domainObject
     * @return Model
     */
    protected function hydrateRelationships(RequestInterface $request, Model $domainObject)
    {
        $data = $request->getResource();

        if (empty($data["relationships"])) {
            return $domainObject;
        }

        foreach ($data['relationships'] as $relationshipName => $relationshipData) {
            $domainObject = $this->doHydrateRelationship(
                $relationshipName,
                $relationshipData,
                $request,
                $domainObject
            );
        }

        return $domainObject;
    }

    /**
     * @param $relationshipName
     * @param $relationshipData
     * @param RequestInterface $request
     * @param Model $domainObject
     * @return Model
     * @throws \Exception
     */
    protected function doHydrateRelationship(
        $relationshipName,
        $relationshipData,
        RequestInterface $request,
        Model $domainObject
    ) {
        $relationMethodName = StringHelper::camelCase($relationshipName);

        $this->validateRelationship($relationshipName, $relationMethodName, $domainObject);

        $relationshipHydrator = $this->relationshipHydratorFactory->createRelationshipHydrator(
            $domainObject->$relationMethodName(),
            $relationshipName
        );

        $relationship = $this->createRelationship($relationshipData);

        if (is_null($relationship)) {
            throw $this->exceptionFactory->createDataMemberMissingException($request);
        }

        $relationshipHydrator->hydrate($request, $this->exceptionFactory, $relationship);

        return $domainObject;
    }

    /**
     * @param $relationshipName
     * @param $relationMethodName
     * @param Model $domainObject
     * @throws RelationshipUpdateNotAllowed
     * @throws \Exception
     */
    protected function validateRelationship($relationshipName, $relationMethodName, Model $domainObject)
    {
        if (method_exists($domainObject, $relationMethodName) === false) {
            throw $this->exceptionFactory->createRelationshipNotExists($relationshipName);
        }

        if ($domainObject->isRelationshipFillable($relationMethodName) === false) {
            throw new RelationshipUpdateNotAllowed($relationshipName);
        }
    }

    /**
     * @param array|null $relationship
     * @return ToOneRelationship|ToManyRelationship|null
     */
    private function createRelationship($relationship)
    {
        if (array_key_exists("data", $relationship) === false) {
            return null;
        }

        //If this is a request to clear the relationship, we create an empty relationship
        if (is_null($relationship["data"])) {
            $result = new ToOneRelationship();
        } elseif (Arr::isAssoc($relationship["data"]) === true) {
            $result = new ToOneRelationship(
                ResourceIdentifier::fromArray($relationship["data"], $this->exceptionFactory)
            );
        } else {
            $result = new ToManyRelationship();
            foreach ($relationship["data"] as $relationship) {
                $result->addResourceIdentifier(
                    ResourceIdentifier::fromArray($relationship, $this->exceptionFactory)
                );
            }
        }

        return $result;
    }
}
