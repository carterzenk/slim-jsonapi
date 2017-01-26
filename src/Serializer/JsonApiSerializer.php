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
    public function __construct($serializerOptions = null)
    {
        $this->serializerOptions = $serializerOptions ?: 0;
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
