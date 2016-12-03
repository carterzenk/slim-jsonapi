<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Model\RelationshipHelperTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

trait RelationshipBuilderTrait
{
    use LinksTrait;
    use RelationshipHelperTrait;

    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @param Model $model
     * @return \string[]
     */
    protected function getForeignKeys(Model $model)
    {
        $foreignKeys = [];

        foreach ($model->getVisibleRelationships() as $name) {
            $relation = $this->getRelation($model, $name);

            if ($relation instanceof BelongsTo) {
                $foreignKeys[] = $relation->getForeignKey();
            }
        }

        return $foreignKeys;
    }

    /**
     * @inheritdoc
     * @throws RelationshipNotExists
     */
    public function getRelationshipsFromModel(Model $model, Container $container)
    {
        $relationships = [];

        foreach ($model->getVisibleRelationships() as $name) {
            $relation = $this->getRelation($model, $name);
            $keyName = $this->getSlugCase($name);

            if ($this->isToOne($relation)) {
                $relationships[$keyName] = $this->getToOneRelationshipCallable(
                    $container,
                    $name,
                    $keyName
                );
            } elseif ($this->isToMany($relation)) {
                $relationships[$keyName] = $this->getToManyRelationshipCallable(
                    $container,
                    $name,
                    $keyName
                );
            }
        }

        return $relationships;
    }

    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param string $keyName
     * @return callable
     */
    protected function getToOneRelationshipCallable(
        ContainerInterface $container,
        $name,
        $keyName
    ) {
        return function ($domainObject) use ($name, $keyName, $container) {
            $primaryTransformer = $container->getTransformer($domainObject);

            $relatedModel = $this->getRelation($domainObject, $name)
                ->getRelated()
                ->newInstance();

            $relatedTransformer = $container->getTransformer($relatedModel);

            // Data
            $dataCallable = function () use ($domainObject, $name) {
                return $domainObject->$name;
            };

            // Links
            $links = $this->getRelationshipLinks($primaryTransformer, $domainObject, $keyName);

            return ToOneRelationship::create()
                ->setLinks($links)
                ->setDataAsCallable($dataCallable, $relatedTransformer);
        };
    }

    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param string $keyName
     * @return callable
     * @internal param ResourceTransformerInterface $transformer
     */
    protected function getToManyRelationshipCallable(
        ContainerInterface $container,
        $name,
        $keyName
    ) {
        return function ($domainObject) use ($name, $keyName, $container) {
            $primaryTransformer = $container->getTransformer($domainObject);

            $relatedModel = $this->getRelation($domainObject, $name)
                ->getRelated()
                ->newInstance();

            $relatedTransformer = $container->getTransformer($relatedModel);

            // Data
            $dataCallable = function () use ($domainObject, $name) {
                return $domainObject->$name;
            };

            // Links
            $links = $this->getRelationshipLinks($primaryTransformer, $domainObject, $keyName);

            return ToManyRelationship::create()
                ->setDataAsCallable($dataCallable, $relatedTransformer)
                ->omitWhenNotIncluded()
                ->setLinks($links);
        };
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
}
