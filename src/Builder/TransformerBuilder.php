<?php

namespace CarterZenk\JsonApi\Transformer;

use CarterZenk\JsonApi\Builder\AbstractBuilder;
use CarterZenk\JsonApi\Model\StringHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\AbstractRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToManyRelationship;
use WoohooLabs\Yin\JsonApi\Schema\Relationship\ToOneRelationship;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class TransformerBuilder extends AbstractBuilder implements TransformerBuilderInterface
{
    use LinksTrait;
    use TypeTrait;

    /**
     * @var ContainerInterface
     */
    protected $transformerContainer;

    /**
     * TransformerBuilder constructor.
     * @param Model $model
     * @param ContainerInterface $transformerContainer
     * @param string $baseUri
     */
    public function __construct(Model $model, ContainerInterface $transformerContainer, $baseUri)
    {
        parent::__construct($model);

        $this->transformerContainer = $transformerContainer;
        $this->baseUri = $baseUri;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->getModelType($this->model);
    }

    /**
     * @inheritdoc
     */
    public function getIdKey()
    {
        return $this->model->getKeyName();
    }

    /**
     * @inheritdoc
     */
    public function getDefaultIncludedRelationships()
    {
        $defaultIncludedRelationships = [];

        $query = $this->model->newQueryWithoutScopes();

        foreach (array_keys($query->getEagerLoads()) as $includedRelationship) {
            $defaultIncludedRelationships[] = StringHelper::slugCase($includedRelationship);
        }

        return $defaultIncludedRelationships;
    }

    /**
     * @inheritdoc
     */
    public function getAttributesToHide()
    {
        $hiddenAttributes = $this->getForeignKeys();
        $hiddenAttributes[] = $this->getIdKey();

        return $hiddenAttributes;
    }

    /**
     * @return \string[]
     */
    protected function getForeignKeys()
    {
        $foreignKeys = [];

        foreach ($this->relations as $name => $relation) {
            if ($relation instanceof BelongsTo) {
                $foreignKeys[] = $relation->getForeignKey();
            }
        }

        return $foreignKeys;
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipsTransformer(ContainerInterface $container)
    {
        $relationships = [];

        foreach ($this->getVisibleRelations() as $name) {
            $keyName = StringHelper::slugCase($name);

            $relationships[$keyName] = $this->getRelationshipCallable(
                $container,
                $this->relations[$name],
                $name,
                $keyName
            );
        }

        return $relationships;
    }

    protected function getVisibleRelations()
    {
        $values = array_keys($this->relations);

        if (count($this->model->getVisible()) > 0) {
            $values = array_intersect_key($values, array_flip($this->model->getVisible()));
        }

        if (count($this->model->getHidden()) > 0) {
            $values = array_diff_key($values, array_flip($this->model->getHidden()));
        }

        return $values;
    }

    /**
     * @param ContainerInterface $container
     * @param Relation $relation
     * @param string $name
     * @param string $keyName
     * @returns callable
     * @throws RelationshipNotExists
     */
    protected function getRelationshipCallable(
        ContainerInterface $container,
        Relation $relation,
        $name,
        $keyName
    ) {
        return function ($domainObject) use ($name, $keyName, $relation, $container) {
            $primaryTransformer = $container->get($domainObject);
            $relatedModel = $relation->getRelated()->newInstance();
            $relatedTransformer = $container->get($relatedModel);

            if ($this->isToOne($relation)) {
                $relationship = ToOneRelationship::create();
            } elseif ($this->isToMany($relation)) {
                $relationship = ToManyRelationship::create();
            }

            $this->setRelationshipLinks(
                $relationship,
                $primaryTransformer,
                $domainObject,
                $keyName
            );

            $this->setRelationshipData(
                $relationship,
                $relatedTransformer,
                $domainObject,
                $name
            );

            return $relationship;
        };
    }

    /**
     * @param AbstractRelationship $relationship
     * @param ResourceTransformerInterface $transformer
     * @param mixed $domainObject
     * @param string $keyName
     */
    private function setRelationshipLinks(
        AbstractRelationship &$relationship,
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

        $relationship->setLinks($links);
    }

    /**
     * @param AbstractRelationship $relationship
     * @param ResourceTransformerInterface $transformer
     * @param mixed $domainObject
     * @param string $name
     */
    private function setRelationshipData(
        AbstractRelationship &$relationship,
        ResourceTransformerInterface $transformer,
        $domainObject,
        $name
    ) {
        if ($this->isRelationshipLoaded($domainObject, $name)) {
            $data = $domainObject->$name;

            $relationship->setData($data, $transformer);
        } else {
            $dataCallable = function () use ($domainObject, $name) {
                return $domainObject->$name;
            };

            $relationship->setDataAsCallable($dataCallable, $transformer);
            $relationship->omitWhenNotIncluded();
        }
    }
}
