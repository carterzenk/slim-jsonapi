<?php

namespace CarterZenk\JsonApi\Document;

use CarterZenk\JsonApi\Transformer\Container;
use CarterZenk\JsonApi\Transformer\LinksFactory;
use CarterZenk\JsonApi\Transformer\LinksFactoryInterface;
use Psr\Http\Message\UriInterface;
use WoohooLabs\Yin\JsonApi\Document\AbstractSuccessfulDocument;
use WoohooLabs\Yin\JsonApi\Schema\JsonApi;
use WoohooLabs\Yin\JsonApi\Transformer\Transformation;

abstract class AbstractSuccessDocument extends AbstractSuccessfulDocument
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var LinksFactoryInterface
     */
    protected $linksFactory;

    /**
     * @var string
     */
    protected $jsonApiVersion;

    /**
     * @var string
     */
    protected $modelClass;

    /**
     * AbstractSuccessfulDocument constructor.
     * @param UriInterface $uri
     * @param string $jsonApiVersion
     * @param string $modelClass
     */
    public function __construct(
        UriInterface $uri,
        $jsonApiVersion,
        $modelClass
    ) {
        $this->modelClass = $modelClass;
        $this->jsonApiVersion = $jsonApiVersion;

        $this->linksFactory = new LinksFactory($uri);
        $this->container = new Container($this->linksFactory);
    }

    /**
     * @return JsonApi
     */
    public function getJsonApi()
    {
        if (isset($this->jsonApiVersion) && is_string($this->jsonApiVersion)) {
            return new JsonApi($this->jsonApiVersion);
        } else {
            return null;
        }
    }

    public function getRelationshipContent(
        $relationshipName,
        Transformation $transformation,
        array $additionalMeta = []
    ) {
        // TODO: Implement getRelationshipContent() method.
    }
}
