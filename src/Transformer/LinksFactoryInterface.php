<?php

namespace CarterZenk\JsonApi\Transformer;

use WoohooLabs\Yin\JsonApi\Schema\Links;
use WoohooLabs\Yin\JsonApi\Transformer\ResourceTransformerInterface;

interface LinksFactoryInterface
{
    /**
     * @return Links
     */
    public function createDocumentLinks();

    /**
     * @param mixed $domainObject
     * @return Links
     */
    public function createDocumentLinksWithPagination($domainObject);

    /**
     * @param string $pluralType
     * @param string $id
     * @return Links
     */
    public function createResourceLinks($pluralType, $id);

    /**
     * @param string $pluralType
     * @param string $id
     * @param string $name
     * @return Links
     */
    public function createRelationshipLinks($pluralType, $id, $name);
}
