<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\StringHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use WoohooLabs\Yin\JsonApi\Schema\Links;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\AbstractRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Transformer\AbstractResourceTransformer;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class ResourceTransformer extends AbstractResourceTransformer implements ResourceTransformerInterface
{
    use TypeTrait;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $pluralType;

    /**
     * @var callable[]|null
     */
    protected $attributes;

    /**
     * @var callable[]
     */
    protected $relationships = [];

    /**
     * @var LinksFactoryInterface
     */
    protected $linksFactory;

    protected $container;

    protected $foreignKeys;

    /**
     * ResourceTransformer constructor.
     * @param ContainerInterface $container
     * @param LinksFactoryInterface $linksFactory
     * @param Builder $builder
     */
    public function __construct(
        ContainerInterface $container,
        LinksFactoryInterface $linksFactory,
        Builder $builder
    ) {
        $this->type = $builder->getType();
        $this->pluralType = $builder->getPluralType();
        $this->foreignKeys = $builder->getForeignKeys();
        $this->relationships = $builder->getRelations();

        $this->linksFactory = $linksFactory;
        $this->container = $container;

        $this->setRelationshipsTransformer($builder->getRelations());
    }

    private function setRelationshipsTransformer(array $relationMethods)
    {
        foreach ($relationMethods as $name => $relation) {
            $keyName = StringHelper::slugCase($name);
            $this->relationships[$keyName] = $this->getRelationshipCallable($relation, $name, $keyName);
        }
    }

    private function getRelationshipCallable(Relation $relation, $name, $keyName)
    {
        return function (Model $domainObject) use ($name, $keyName, $relation) {
            $relationship = $this->createRelationshipFromRelation($relation);

            $relationship->setLinks($this->linksFactory->createRelationshipLinks(
                $this->pluralType,
                $this->getId($domainObject),
                $keyName
            ));

            $transformer = $this->container->get($relation->getRelated());

            if ($domainObject->relationLoaded($name)) {
                $relationship->setData($domainObject->$name, $transformer);
            } else {
                $relationship
                    ->setDataAsCallable(function () use ($domainObject, $name) {
                        return $domainObject->$name;
                    }, $transformer)
                    ->omitWhenNotIncluded();
            }

            return $relationship;
        };
    }

    /**
     * @param Relation $relation
     * @return AbstractRelationship
     */
    private function createRelationshipFromRelation(Relation $relation)
    {
        if (($relation instanceof HasOne) || ($relation instanceof BelongsTo)) {
            return ToOneRelationship::create();
        } else {
            return ToManyRelationship::create();
        }
    }

    /**
     * @inheritdoc
     */
    public function getType($domainObject)
    {
        return $this->type;
    }

    /**
     * @param Model $domainObject
     * @return string
     */
    public function getId($domainObject)
    {
        return $domainObject->getKey();
    }

    /**
     * @inheritdoc
     */
    public function getMeta($domainObject)
    {
        return [];
    }

    /**
     * @param Model $domainObject
     * @return Links
     */
    public function getLinks($domainObject)
    {
        return $this->linksFactory->createResourceLinks(
            $this->pluralType,
            $domainObject->getKey()
        );
    }

    /**
     * @param Model $domainObject
     * @return callable[]
     */
    public function getAttributes($domainObject)
    {
        if (is_null($this->attributes)) {
            $this->setAttributes($domainObject);
        }

        return $this->attributes;
    }

    private function setAttributes(Model $domainObject)
    {
        $this->attributes = [];

        $domainObject->makeHidden($domainObject->getKeyName());
        $domainObject->makeHidden($this->foreignKeys);

        $attributeKeys = array_keys($domainObject->attributesToArray());

        foreach ($attributeKeys as $attributeKey) {
            $this->attributes[$attributeKey] = function ($domainObject) use ($attributeKey) {
                return $domainObject->$attributeKey;
            };
        }
    }

    /**
     * @inheritdoc
     */
    public function getDefaultIncludedRelationships($domainObject)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($domainObject)
    {
        return $this->relationships;
    }
}
