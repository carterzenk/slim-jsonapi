<?php

namespace CarterZenk\JsonApi\Encoder;

use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

interface EncoderInterface
{
    /**
     * Encodes a domain object.
     *
     * @param mixed $domainObject
     * @param Model $model
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $additionalMeta
     * @return ResponseInterface
     */
    public function encodeResource(
        $domainObject,
        Model $model,
        RequestInterface $request,
        ResponseInterface $response,
        array $additionalMeta = []
    );

    /**
     * Encodes a relationship on an object.
     *
     * @param mixed $domainObject
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param $relationshipName
     * @param array $additionalMeta
     * @return ResponseInterface
     */
    public function encodeRelationship(
        $domainObject,
        RequestInterface $request,
        ResponseInterface $response,
        $relationshipName,
        array $additionalMeta = []
    );
}
