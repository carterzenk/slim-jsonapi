<?php

namespace CarterZenk\JsonApi\Encoder;

use CarterZenk\JsonApi\Exceptions\InvalidDomainObjectException;
use CarterZenk\JsonApi\Serializer\SerializerInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

abstract class EloquentEncoder implements EncoderInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * EloquentEncoder constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @inheritdoc
     */
    public function encodeResource(
        $domainObject,
        Model $model,
        RequestInterface $request,
        ResponseInterface $response,
        array $additionalMeta = []
    ) {
        if ($this->isModel($domainObject)) {
            $content = $this->encodeModel($domainObject, $request, $additionalMeta);
        } elseif ($this->isPaginator($domainObject)) {
            $content = $this->encodeCollection($domainObject, $model, $request, $additionalMeta);
        } else {
            throw new InvalidDomainObjectException($domainObject);
        }

        return $this->serializer->serialize($response, $content);
    }

    /**
     * @inheritdoc
     */
    public function encodeRelationship(
        $domainObject,
        RequestInterface $request,
        ResponseInterface $response,
        $relationshipName,
        array $additionalMeta = []
    ) {
        if ($this->isModel($domainObject)) {
            $content = $this->encodeModelRelationship(
                $domainObject,
                $request,
                $relationshipName,
                $additionalMeta
            );
        } else {
            throw new InvalidDomainObjectException($domainObject);
        }

        return $this->serializer->serialize($response, $content);
    }

    /**
     * @param Model $model
     * @param RequestInterface $request
     * @param array $additionalMeta
     * @return array
     */
    abstract protected function encodeModel(
        Model $model,
        RequestInterface $request,
        array $additionalMeta
    );

    /**
     * @param Paginator $collection
     * @param Model $model
     * @param RequestInterface $request
     * @param array $additionalMeta
     * @return array
     */
    abstract protected function encodeCollection(
        Paginator $collection,
        Model $model,
        RequestInterface $request,
        array $additionalMeta
    );

    /**
     * @param Model $model
     * @param RequestInterface $request
     * @param $relationshipName
     * @param array $additionalMeta
     * @return array
     */
    abstract protected function encodeModelRelationship(
        Model $model,
        RequestInterface $request,
        $relationshipName,
        array $additionalMeta
    );

    /**
     * @param mixed $domainObject
     * @return bool
     */
    private function isModel($domainObject)
    {
        return $domainObject instanceof Model;
    }

    /**
     * @param mixed $domainObject
     * @return bool
     */
    private function isPaginator($domainObject)
    {
        return $domainObject instanceof Paginator;
    }
}
