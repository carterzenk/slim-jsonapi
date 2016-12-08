<?php

namespace CarterZenk\JsonApi\Transformer;

use Illuminate\Support\Str;
use Psr\Http\Message\UriInterface;
use WoohooLabs\Yin\JsonApi\Schema\Link;
use WoohooLabs\Yin\JsonApi\Schema\Links;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

class LinksFactory implements LinksFactoryInterface
{
    /**
     * @var string
     */
    protected $baseUri;

    /**
     * @var string
     */
    protected $path;

    /**
     * LinkFactory constructor.
     * @param UriInterface $uri
     */
    public function __construct(UriInterface $uri)
    {
        $this->path = $uri->getPath();
        $this->baseUri = $this->getBaseUri($uri);
    }

    /**
     * @param UriInterface $uri
     * @return string
     */
    protected function getBaseUri(UriInterface $uri)
    {
        $baseUri = $uri->getScheme() . '://';
        $baseUri .= $uri->getHost();

        if (!empty($uri->getPort())) {
            $baseUri .= ':' . $uri->getPort();
        }

        return $baseUri;
    }

    /**
     * @return Links
     */
    private function createLinks()
    {
        return Links::createWithBaseUri($this->baseUri);
    }

    /**
     * @inheritdoc
     */
    public function createDocumentLinks()
    {
        $links = $this->createLinks();
        $links->setSelf(new Link($this->path));

        return $links;
    }

    /**
     * @inheritdoc
     */
    public function createDocumentLinksWithPagination($domainObject)
    {
        $links = $this->createDocumentLinks();
        $links->setPagination($this->path, $domainObject);

        return $links;
    }

    /**
     * @inheritdoc
     */
    public function createResourceLinks($pluralType, $id)
    {
        $links = $this->createLinks();
        $links->setSelf(new Link('/'.$pluralType.'/'.$id));

        return $links;
    }

    /**
     * @inheritdoc
     */
    public function createRelationshipLinks($name, $domainObject, ResourceTransformerInterface $transformer)
    {
        $pluralType = Str::plural($transformer->getType($domainObject));
        $modelId = $transformer->getId($domainObject);

        $links = $this->createLinks();

        $selfLink = new Link('/'.$pluralType.'/'.$modelId.'/relationships/'.$name);
        $links->setSelf($selfLink);

        $relatedLink = new Link('/'.$pluralType.'/'.$modelId.'/'.$name);
        $links->setRelated($relatedLink);

        return $links;
    }
}
