<?php

namespace CarterZenk\JsonApi\Document;

use CarterZenk\JsonApi\Transformer\Container;
use CarterZenk\JsonApi\Transformer\LinksFactory;
use CarterZenk\JsonApi\Transformer\LinksFactoryInterface;
use Illuminate\Database\Eloquent\Model;
use WoohooLabs\Yin\JsonApi\Document\AbstractDocument;
use WoohooLabs\Yin\JsonApi\Exception\ExceptionFactoryInterface;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;
use WoohooLabs\Yin\JsonApi\Schema\Data\DataInterface;
use WoohooLabs\Yin\JsonApi\Schema\JsonApi;
use WoohooLabs\Yin\JsonApi\Transformer\Transformation;

abstract class AbstractSuccessfulDocument extends AbstractDocument
{
    /**
     * @var mixed
     */
    protected $domainObject;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var LinksFactoryInterface
     */
    protected $linksFactory;

    /**
     * @var string|null
     */
    protected $jsonApiVersion;

    /**
     * AbstractSuccessfulDocument constructor.
     * @param Model $model
     * @param RequestInterface $request
     * @param $jsonApiVersion
     */
    public function __construct(
        Model $model,
        RequestInterface $request,
        $jsonApiVersion
    ) {
        $this->model = $model;
        $this->request = $request;
        $this->jsonApiVersion = $jsonApiVersion;

        $this->linksFactory = new LinksFactory($request->getUri());
        $this->container = new Container($this->linksFactory);
    }

    /**
     * @return DataInterface
     */
    abstract protected function createData();

    /**
     * @param Transformation $transformation
     */
    abstract protected function fillData(Transformation $transformation);

    /**
     * @param $relationshipName
     * @param Transformation $transformation
     * @param array $additionalMeta
     * @return mixed
     */
    abstract protected function getRelationshipContentInternal(
        $relationshipName,
        Transformation $transformation,
        array $additionalMeta = []
    );

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param mixed $domainObject
     * @param string $relationshipName
     * @param array $additionalMeta
     * @return array
     */
    public function getRelationshipContent(
        ExceptionFactoryInterface $exceptionFactory,
        $domainObject,
        $relationshipName,
        array $additionalMeta = []
    ) {
        $transformation = new Transformation($this->request, $this->createData(), $exceptionFactory, "");
        $this->initializeDocument($domainObject);

        return $this->transformRelationshipContent($relationshipName, $transformation, $additionalMeta);
    }

    /**
     * @param ExceptionFactoryInterface $exceptionFactory
     * @param mixed $domainObject
     * @param array $additionalMeta
     * @return array
     */
    public function getResourceContent(
        ExceptionFactoryInterface $exceptionFactory,
        $domainObject,
        array $additionalMeta = []
    ) {
        $transformation = new Transformation($this->request, $this->createData(), $exceptionFactory, "");
        $this->initializeDocument($domainObject);

        return $this->transformContent($transformation, $additionalMeta);
    }

    /**
     * @param mixed $domainObject
     * @param array $additionalMeta
     * @return array
     */
    public function getMetaContent($domainObject, array $additionalMeta = [])
    {
        $this->initializeDocument($domainObject);

        return $this->transformBaseContent($additionalMeta);
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

    /**
     * @param $domainObject
     */
    private function initializeDocument($domainObject)
    {
        $this->domainObject = $domainObject;
    }

    /**
     * @param array $additionalMeta
     * @param \WoohooLabs\Yin\JsonApi\Transformer\Transformation $transformation
     * @return array
     */
    protected function transformContent(Transformation $transformation, array $additionalMeta = [])
    {
        $content = $this->transformBaseContent($additionalMeta);

        // Data
        $this->fillData($transformation);
        $content["data"] = $transformation->data->transformPrimaryResources();

        // Included
        if ($transformation->data->hasIncludedResources()) {
            $content["included"] = $transformation->data->transformIncludedResources();
        }

        return $content;
    }

    /**
     * @param string $relationshipName
     * @param \WoohooLabs\Yin\JsonApi\Transformer\Transformation $transformation
     * @param array $additionalMeta
     * @return array
     */
    protected function transformRelationshipContent(
        $relationshipName,
        Transformation $transformation,
        array $additionalMeta = []
    ) {
        $response = $this->getRelationshipContentInternal($relationshipName, $transformation, $additionalMeta);

        // Included
        if ($transformation->data->hasIncludedResources()) {
            $response["included"] = $transformation->data->transformIncludedResources();
        }

        return $response;
    }
}
