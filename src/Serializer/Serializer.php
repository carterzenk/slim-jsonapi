<?php

namespace CarterZenk\JsonApi\Serializer;

use Psr\Http\Message\ResponseInterface;
use WoohooLabs\Yin\JsonApi\Serializer\SerializerInterface;

class Serializer implements SerializerInterface
{
    private $serializerOptions;

    public function __construct($serializerOptions)
    {
        $this->serializerOptions = $serializerOptions === null ? 0 : $serializerOptions;
    }

    /**
     * @param ResponseInterface $response
     * @param int $responseCode
     * @param array $content
     * @return ResponseInterface
     */
    public function serialize(ResponseInterface $response, $responseCode, array $content)
    {
        $response = $response->withStatus($responseCode);
        $response = $response->withHeader("Content-Type", "application/vnd.api+json");

        if ($response->getBody()->isSeekable()) {
            $response->getBody()->rewind();
        }

        $response->getBody()->write(json_encode($content, $this->serializerOptions));

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
