<?php

namespace CarterZenk\JsonApi\Document;

use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\UriInterface;

interface DocumentFactoryInterface
{
    /**
     * @param UriInterface $uri
     * @param Model $model
     * @return SingleResourceDocument
     */
    public function createResourceDocument(UriInterface $uri, Model $model);

    /**
     * @param UriInterface $uri
     * @param Model $model
     * @return CollectionResourceDocument
     */
    public function createCollectionDocument(UriInterface $uri, Model $model);
}
