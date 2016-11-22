<?php

namespace CarterZenk\JsonApi\Encoder;

use CarterZenk\JsonApi\Document\DocumentFactory;
use CarterZenk\JsonApi\Document\DocumentFactoryInterface;
use CarterZenk\JsonApi\Model\Model;
use CarterZenk\JsonApi\Serializer\SerializerInterface;
use Illuminate\Contracts\Pagination\Paginator;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

class JsonApiEncoder extends EloquentEncoder
{
    /**
     * @var DocumentFactoryInterface
     */
    private $documentFactory;

    /**
     * @var ExceptionFactoryInterface
     */
    private $exceptionFactory;

    /**
     * JsonApiEncoder constructor.
     * @param SerializerInterface $serializer
     * @param DocumentFactoryInterface $documentFactory
     * @param ExceptionFactoryInterface $exceptionFactory
     */
    public function __construct(
        SerializerInterface $serializer,
        DocumentFactoryInterface $documentFactory = null,
        ExceptionFactoryInterface $exceptionFactory = null
    ) {
        $this->documentFactory = isset($documentFactory) ? $documentFactory : new DocumentFactory();
        $this->exceptionFactory = isset($exceptionFactory) ? $exceptionFactory : new DefaultExceptionFactory();

        parent::__construct($serializer);
    }

    /**
     * @inheritdoc
     */
    protected function encodeModel(Model $model, RequestInterface $request, array $additionalMeta)
    {
        $document = $this->documentFactory->createResourceDocument($request);

        return $document->getResourceContent($this->exceptionFactory, $model, $additionalMeta);
    }

    /**
     * @inheritdoc
     */
    protected function encodeCollection(Paginator $collection, RequestInterface $request, array $additionalMeta)
    {
        $document = $this->documentFactory->createCollectionDocument($request);

        return $document->getResourceContent($this->exceptionFactory, $collection, $additionalMeta);
    }

    /**
     * @inheritdoc
     */
    protected function encodeModelRelationship(
        Model $model,
        RequestInterface $request,
        $relationshipName,
        array $additionalMeta
    ) {
        $document = $this->documentFactory->createResourceDocument($request);

        return $document->getRelationshipContent(
            $this->exceptionFactory,
            $model,
            $relationshipName,
            $additionalMeta
        );
    }
}
