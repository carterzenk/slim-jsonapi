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
     * @param string $name
     * @param mixed $domainObject
     * @param ResourceTransformerInterface $transformer
     * @return Links
     */
    public function createRelationshipLinks($name, $domainObject, ResourceTransformerInterface $transformer);
}
