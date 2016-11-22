<?php

namespace CarterZenk\JsonApi\Serializer;

class JsonApiSerializer extends AbstractSerializer
{
    /**
     * @var int
     */
    private $serializerOptions;

    /**
     * JsonApiSerializerSerializer constructor.
     * @param $serializerOptions
     */
    public function __construct($serializerOptions)
    {
        $this->serializerOptions = $serializerOptions === null ? 0 : $serializerOptions;
    }

    /**
     * @inheritdoc
     */
    protected function serializeContent(array $content)
    {
        return json_encode($content, $this->serializerOptions);
    }

    /**
     * @inheritdoc
     */
    protected function getContentType()
    {
        return "application/vnd.api+json";
    }
}
