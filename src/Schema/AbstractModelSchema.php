<?php

namespace CarterZenk\JsonApi\Schema;

use CarterZenk\JsonApi\Model\StringHelper;
use CarterZenk\JsonApi\Transformer\ContainerInterface;
use CarterZenk\JsonApi\Transformer\LinksFactoryInterface;
use CarterZenk\JsonApi\Transformer\TypeTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use WoohooLabs\Yin\JsonApi\Schema\Links;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\AbstractRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Transformer\AbstractResourceTransformer;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

abstract class AbstractModelSchema extends AbstractResourceTransformer implements SchemaInterface
{
    use TypeTrait;

    protected $type;

    protected $validTypes;

    protected $relationshipMethods;

    protected $fillableRelationships;

    protected $fillableAttributes;

    protected $visibleRelationships;

    protected $visibleAttributes;

    protected $linksFactory;

    protected $schemaContainer;

    private $pluralType;

    private $foreignKeys;

    private $modelClass;

    private $modelReflection;

    private $attributeHydrators;

    private $attributeTransformers;

    private $relationshipHydrators;

    private $relationshipTransformers;



    public function __construct(LinksFactoryInterface $linksFactory, ContainerInterface $schemaContainer)
    {
        $this->linksFactory = $linksFactory;
        $this->schemaContainer = $schemaContainer;

        $this->modelClass = $this->getModelClass();
        $this->modelReflection = new \ReflectionClass($this->modelClass);


        $this->type = $this->type ?? StringHelper::slugCase($this->modelReflection->getShortName());
        $this->pluralType = StringHelper::pluralize($this->type);

        $modelInstance = $this->modelReflection->newInstance();

        $this->setRelationships($modelInstance);
    }

    protected abstract function getModelClass();

    protected function setRelationships(Model $model)
    {
        foreach ($this->visibleRelationships as $relationshipMethod) {
            if ($this->modelReflection->hasMethod($relationshipMethod) === false) {
                throw new \LogicException("Relationship {$relationshipMethod} does note exist.");
            }

            $reflectionMethod = $this->modelReflection->getMethod($relationshipMethod);

            $relation = $reflectionMethod->invoke($model);

            if (!$relation instanceof Relation) {
                throw new \LogicException("Relationship {$relationshipMethod} did not return a Relation instance.");
            }

            $this->setRelationshipTransformer($relation, $relationshipMethod);

            if ($relation instanceof BelongsTo) {
                $this->foreignKeys[] = $relation->getForeignKey();
            }
        }
    }

    protected function setRelationshipTransformer(Relation $relation, $relationshipMethodName)
    {
        $keyName = StringHelper::slugCase($relationshipMethodName);

        $this->relationshipTransformers[$keyName] = function (Model $domainObject) use ($relationshipMethodName, $keyName, $relation) {
            $relationship = $this->createRelationshipFromRelation($relation);

            $relationship->setLinks($this->linksFactory->createRelationshipLinks(
                $this->pluralType,
                $this->getId($domainObject),
                $keyName
            ));

            $relatedSchema = $this->schemaContainer->get($relation->getRelated());

            if ($domainObject->relationLoaded($relationshipMethodName)) {
                $relationship->setData($domainObject->$relationshipMethodName, $relatedSchema);
            } else {
                $relationship
                    ->setDataAsCallable(function () use ($domainObject, $relationshipMethodName) {
                        return $domainObject->$relationshipMethodName;
                    }, $relatedSchema)
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
        // TODO: Implement getAttributes() method.
    }

    protected function setAttributeTransformers()
    {
        foreach ($this->visibleAttributes as $visibleAttribute) {
            // TODO: Skip over id key and foreign keys.

            // TODO:
        }
    }

    public function getDefaultIncludedRelationships($domainObject)
    {
        // TODO: Implement getDefaultIncludedRelationships() method.
    }

    public function getRelationships($domainObject)
    {
        // TODO: Implement getRelationships() method.
    }

    public function isValidType(string $type)
    {
        if ($type === $this->type) {
            return true;
        }

        return in_array($type, $this->validTypes);
    }

    public function isRelationshipFillable(string $relationshipMethodName)
    {
        // TODO: Implement isRelationshipFillable() method.
    }

    public function isRelationshipVisible(string $relationshipMethodName)
    {
        return in_array($relationshipMethodName, $this->visibleRelationships);
    }

    public function isAttributeFillable(string $attributeKey)
    {
        // TODO: Implement isAttributeFillable() method.
    }
}