<?php

namespace CarterZenk\JsonApi\Handlers;

use WoohooLabs\Yin\JsonApi\Serializer\DefaultSerializer;
use WoohooLabs\Yin\JsonApi\Serializer\SerializerInterface;

abstract class AbstractErrorHandler
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var bool
     */
    protected $displayErrorDetails;

    /**
     * AbstractErrorHandler constructor.
     * @param SerializerInterface $serializer
     * @param bool $displayErrorDetails
     */
    public function __construct(SerializerInterface $serializer = null, $displayErrorDetails = false)
    {
        $this->serializer = isset($serializer) ? $serializer : new DefaultSerializer();
        $this->displayErrorDetails = $displayErrorDetails;
    }
}
