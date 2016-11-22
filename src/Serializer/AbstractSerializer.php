<?php

namespace CarterZenk\JsonApi\Serializer;

use Psr\Http\Message\ResponseInterface;

abstract class AbstractSerializer implements SerializerInterface
{
    /**
     * This function should return the content array as a string.
     *
     * @param array $content
     * @return string
     */
    abstract protected function serializeContent(array $content);

    /**
     * This function should return the content type to use.
     *
     * @return string
     */
    abstract protected function getContentType();

    /**
     * @param ResponseInterface $response
     * @param array $content
     * @return ResponseInterface
     */
    public function serialize(ResponseInterface $response, array $content)
    {
        $contentType = $this->getContentType();
        $response = $response->withHeader("Content-Type", $contentType);

        if ($response->getBody()->isSeekable()) {
            $response->getBody()->rewind();
        }

        $response->getBody()->write($this->serializeContent($content));

        return $response;
    }

    /**
     * @param ResponseInterface $response
     * @return string
     */
    public function getBodyAsString(ResponseInterface $response)
    {
        return $response->getBody()->__toString();
    }
}
