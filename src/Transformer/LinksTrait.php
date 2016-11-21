<?php

namespace CarterZenk\JsonApi\Transformer;

use WoohooLabs\Yin\JsonApi\Schema\Links;

trait LinksTrait
{
    /*
     * @var string|null
     */
    protected $baseUri;

    /**
     * @return Links
     */
    protected function createLinks()
    {
        if (isset($this->baseUri)) {
            return Links::createWithBaseUri($this->baseUri);
        } else {
            return Links::createWithoutBaseUri();
        }
    }
}
