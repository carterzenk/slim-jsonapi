<?php

namespace CarterZenk\JsonApi\Document;

use CarterZenk\JsonApi\Transformer\Transformer;
use Psr\Http\Message\ServerRequestInterface;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class DocumentFactory implements DocumentFactoryInterface
{
    /**
     * @var ResourceTransformerInterface
     */
    private $transformer;

    /**
     * DocumentFactory constructor.
     * @param ResourceTransformerInterface $transformer
     */
    public function __construct(ResourceTransformerInterface $transformer = null)
    {
        $this->transformer = isset($transformer) ? $transformer : new Transformer();
    }

    /**
     * @inheritdoc
     */
    public function createResourceDocument(ServerRequestInterface $request)
    {
        $path = $this->getPath($request);
        $baseUri = $this->getBaseUri($request);

        $this->transformer->setBaseUri($baseUri);

        return new SingleResourceDocument($this->transformer, $path, $baseUri);
    }

    /**
     * @inheritdoc
     */
    public function createCollectionDocument(ServerRequestInterface $request)
    {
        $path = $this->getPath($request);
        $baseUri = $this->getBaseUri($request);

        $this->transformer->setBaseUri($baseUri);

        return new CollectionResourceDocument($this->transformer, $path, $baseUri);
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    private function getBaseUri(ServerRequestInterface $request)
    {
        $uri = $request->getUri();

        $baseUrl = $uri->getScheme().'://';
        $baseUrl .= $uri->getHost();

        if (!empty($uri->getPort())) {
            $baseUrl .= ':'.$uri->getPort();
        }

        return $baseUrl;
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    private function getPath(ServerRequestInterface $request)
    {
        return $request->getUri()->getPath();
    }
}
