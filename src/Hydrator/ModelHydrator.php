<?php

namespace CarterZenk\JsonApi\Hydrator;

use CarterZenk\JsonApi\Model\RelationshipHelperTrait;
use CarterZenk\JsonApi\Transformer\TypeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\HydratorInterface;
use WoohooLabs\Yin\JsonApi\Hydrator\UpdateRelationshipHydratorInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

class ModelHydrator implements HydratorInterface, UpdateRelationshipHydratorInterface
{
    use TypeTrait;
    use RelationshipHelperTrait;

    protected $fillableRelationships;

    public function __construct(array $fillableRelationships = [])
    {
        $this->fillableRelationships = $fillableRelationships;
    }

    /**
     * @param RequestInterface $request
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param mixed $domainObject
     * @return Model|mixed
     * @throws \Exception
     */
    public function hydrate(RequestInterface $request, ExceptionFactoryInterface $exceptionFactory, $domainObject)
    {
        $data = $request->getResource();
        if ($data === null) {
            throw $exceptionFactory->createDataMemberMissingException($request);
        }

        $this->validateType($data, $exceptionFactory, $domainObject);
        $domainObject = $this->hydrateAttributes($data, $domainObject);
        $domainObject = $this->hydrateRelationships($data, $exceptionFactory, $domainObject);

        return $domainObject;
    }

    /**
     * @param array $data
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param Model $domainObject
     * @throws \Exception
     */
    protected function validateType(
        array $data,
        ExceptionFactoryInterface $exceptionFactory,
        Model $domainObject
    ) {
        if (empty($data["type"])) {
            throw $exceptionFactory->createResourceTypeMissingException();
        }

        $expectedType = $this->getModelType($domainObject);

        if ($data["type"] !== $expectedType) {
            throw $exceptionFactory->createResourceTypeUnacceptableException($data["type"], [$expectedType]);
        }
    }

    /**
     * @param array $data
     * @param Model $domainObject
     * @return Model
     */
    protected function hydrateAttributes(
        array $data,
        Model $domainObject
    ) {
        if (empty($data["attributes"])) {
            return $domainObject;
        }

        if ($domainObject->totallyGuarded()) {
            // TODO: Handle totally guarded error.
        }

        foreach ($data["attributes"] as $attributeKey => $attributeValue) {
            // Ignore attributes that are not fillable.
            if (!$domainObject->isFillable($attributeKey)) {
                continue;
            }

            // Ignore attributes that should be hydrated by the relationship hydrator.
            if (method_exists($domainObject, Str::camel($attributeKey))) {
                continue;
            }

            $domainObject->setAttribute($attributeKey, $attributeValue);
        }

        return $domainObject;
    }

    /**
     * @param array $data
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param Model $domainObject
     * @return Model
     */
    protected function hydrateRelationships(
        array $data,
        ExceptionFactoryInterface $exceptionFactory,
        Model $domainObject
    ) {
        if (empty($data["relationships"])) {
            return $domainObject;
        }

        $modelRelationships = $this->getRelations($domainObject);

        foreach ($data['relationships'] as $relationshipKey => $relationship) {

        }

        return $domainObject;
    }

    private function isHydratable($relationship)
    {
        return in_array($relationship, $this->hydratableRelationships);
    }

    /**
     * @param string $relationship
     * @param RequestInterface $request
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param mixed $domainObject
     * @return Model|mixed
     */
    public function hydrateRelationship(
        $relationship,
        RequestInterface $request,
        ExceptionFactoryInterface $exceptionFactory,
        $domainObject
    ) {
        // TODO: Implement hydrateRelationship() method.


        return $domainObject;
    }
}
