<?php

namespace CarterZenk\JsonApi\Model;

use CarterZenk\JsonApi\Exceptions\RelatedResourceNotFound;
use CarterZenk\JsonApi\Transformer\LinksTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Schema\ErrorSource;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToOneRelationship as ToOneHydrator;
use WoohooLabs\Yin\JsonApi\Hydrator\Relationship\ToManyRelationship as ToManyHydrator;
use WoohooLabs\Yin\JsonApi\Schema\ResourceIdentifier;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class RelationshipParser implements RelationshipParserInterface
{
    use LinksTrait;

    /**
     * @var Model
     */
    private $model;

    /**
     * RelationshipParser constructor.
     * @param Model $model
     * @param string|null $baseUri
     */
    public function __construct(Model $model, $baseUri = null)
    {
        $this->model = $model;
        $this->baseUri = $baseUri;
    }

    /**
     * @return string[]
     */
    public function getForeignKeys()
    {
        $foreignKeys = [];

        foreach ($this->model->getVisibleRelationships() as $name) {
            $relation = $this->getRelation($name);

            if (!($relation instanceof BelongsTo)) {
                continue;
            }

            $foreignKeys[] = $relation->getForeignKey();
        }

        return $foreignKeys;
    }

    /**
     * @inheritdoc
     * @throws RelationshipNotExists
     */
    public function getRelationships(ResourceTransformerInterface $transformer)
    {
        $relationships = [];

        foreach ($this->model->getVisibleRelationships() as $name) {
            $relation = $this->getRelation($name);
            $keyName = $this->getSlugCase($name);

            if ($this->isToOne($relation)) {
                $relationships[$keyName] = $this->getToOneRelationshipCallable(
                    $transformer,
                    $name,
                    $keyName
                );
            } elseif ($this->isToMany($relation)) {
                $relationships[$keyName] = $this->getToManyRelationshipCallable(
                    $transformer,
                    $name,
                    $keyName
                );
            }
        }

        return $relationships;
    }

    /**
     * @param string $name
     * @return Relation
     * @throws RelationshipNotExists
     */
    private function getRelation($name)
    {
        if (!method_exists($this->model, $name)) {
            throw $this->createRelationshipNotExistsException($name);
        }

        $relation = $this->model->$name();

        if (!$this->isRelation($relation)) {
            throw $this->createRelationshipNotExistsException($name);
        }

        return $relation;
    }

    /**
     * @param $name
     * @return RelationshipNotExists
     */
    private function createRelationshipNotExistsException($name)
    {
        return new RelationshipNotExists($this->getSlugCase($name));
    }

    /**
     * @param $name
     * @return string
     */
    private function getSlugCase($name)
    {
        return Str::slug(Str::snake(ucwords($name)));
    }

    /**
     * @param mixed $object
     * @return bool
     */
    private function isRelation($object)
    {
        return $object instanceof Relation;
    }

    /**
     * @param Relation $relation
     * @return bool
     */
    private function isToOne(Relation $relation)
    {
        return $relation instanceof HasOne ||
               $relation instanceof BelongsTo;
    }

    /**
     * @param Relation $relation
     * @return bool
     */
    private function isToMany(Relation $relation)
    {
        return $relation instanceof HasMany ||
               $relation instanceof BelongsToMany;
    }

    /**
     * @param ResourceTransformerInterface $transformer
     * @param string $name
     * @param string $keyName
     * @return callable
     */
    protected function getToOneRelationshipCallable(
        ResourceTransformerInterface $transformer,
        $name,
        $keyName
    ) {
        return function ($domainObject) use ($name, $keyName, $transformer) {
            $relationship = ToOneRelationship::create();

            // Data
            $relationship->setData($domainObject->$name, $transformer);

            // Links
            $links = $this->getRelationshipLinks($transformer, $domainObject, $keyName);
            $relationship->setLinks($links);

            return $relationship;
        };
    }

    /**
     * @param ResourceTransformerInterface $transformer
     * @param string $name
     * @param string $keyName
     * @return callable
     */
    protected function getToManyRelationshipCallable(
        ResourceTransformerInterface $transformer,
        $name,
        $keyName
    ) {
        return function ($domainObject) use ($name, $keyName, $transformer) {
            $relationship = ToManyRelationship::create();

            // Data
            $relationship = $this->setToManyRelationshipData(
                $transformer,
                $relationship,
                $name,
                $domainObject
            );

            // Links
            $links = $this->getRelationshipLinks($transformer, $domainObject, $keyName);
            $relationship->setLinks($links);

            return $relationship;
        };
    }

    /**
     * @param ResourceTransformerInterface $transformer
     * @param ToManyRelationship $relationship
     * @param string $name
     * @param mixed $domainObject
     * @return ToManyRelationship
     */
    protected function setToManyRelationshipData(
        ResourceTransformerInterface $transformer,
        ToManyRelationship $relationship,
        $name,
        $domainObject
    ) {
        if ($this->model->relationLoaded($name)) {
            // If the relation is loaded, set the data directly.
            $relationship->setData($domainObject->$name, $transformer);
        } else {
            // Otherwise, set it as a callable.
            $dataCallable = function () use ($domainObject, $name) {
                return $domainObject->$name;
            };

            $relationship->setDataAsCallable($dataCallable, $transformer);

            // Only load the relationship if the client requests its inclusion.
            $relationship->omitWhenNotIncluded();
        }

        return $relationship;
    }

    /**
     * @param ResourceTransformerInterface $transformer
     * @param mixed $domainObject
     * @param string $keyName
     * @return \WoohooLabs\Yin\JsonApi\Schema\Links
     */
    private function getRelationshipLinks(
        ResourceTransformerInterface $transformer,
        $domainObject,
        $keyName
    ) {
        $pluralType = Str::plural($transformer->getType($domainObject));
        $modelId = $transformer->getId($domainObject);

        $links = $this->createLinks();

        $selfLink = new Link('/'.$pluralType.'/'.$modelId.'/relationships/'.$keyName);
        $links->setSelf($selfLink);

        $relatedLink = new Link('/'.$pluralType.'/'.$modelId.'/'.$keyName);
        $links->setRelated($relatedLink);

        return $links;
    }

    /**
     * @inheritdoc
     * @throws RelationshipNotExists
     */
    public function getRelationshipHydrators()
    {
        $hydrators = [];

        foreach ($this->model->getFillableRelationships() as $name) {
            $relation = $this->getRelation($name);

            if ($this->isToOne($relation)) {
                $hydratorCallable = $this->getToOneHydratorCallable($name, $relation);
            } elseif ($this->isToMany($relation)) {
                $hydratorCallable = $this->getToManyHydratorCallable($name, $relation);
            }

            if (isset($hydratorCallable)) {
                $keyName = $this->getSlugCase($name);
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
    private function createRelatedResourceNotExists($name, ResourceIdentifier $identifier)
    {
        $pointer = '/data/relationships/'.$this->getSlugCase($name);
        $source = ErrorSource::fromPointer($pointer);

        return new RelatedResourceNotFound($identifier, $source);
    }
}
