<?php

namespace CarterZenk\JsonApi\Document;

use CarterZenk\JsonApi\Transformer\LinksTrait;
use WoohooLabs\Yin\JsonApi\Schema\JsonApi;

trait ResourceDocumentTrait
{
    use LinksTrait;

    /*
     * @var string
     */
    protected $path;

    /**
     * @return JsonApi
     */
    public function getJsonApi()
    {
        return new JsonApi('1.0');
    }
}
