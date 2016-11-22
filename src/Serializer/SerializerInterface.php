<?php

namespace CarterZenk\JsonApi\Serializer;

use Psr\Http\Message\ResponseInterface;

interface SerializerInterface
{
    /**
     * @param ResponseInterface $response
     * @param array $content
     * @return mixed
     */
    public function serialize(ResponseInterface $response, array $content);

    /**
     * @param ResponseInterface $response
     * @return mixed
     */
    public function getBodyAsString(ResponseInterface $response);
}
