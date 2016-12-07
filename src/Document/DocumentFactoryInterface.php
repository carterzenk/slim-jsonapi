<?php

namespace CarterZenk\JsonApi\Document;

use Illuminate\Database\Eloquent\Model;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

interface DocumentFactoryInterface
{
    /**
     * @param Model $model
     * @param RequestInterface $request
     * @return SingleResourceDocument
     */
    public function createResourceDocument(Model $model, RequestInterface $request);

    /**
     * @param Model $model
     * @param RequestInterface $request
     * @return CollectionResourceDocument
     */
    public function createCollectionDocument(Model $model, RequestInterface $request);
}
