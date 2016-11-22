<?php

namespace CarterZenk\JsonApi\Exceptions;

use WoohooLabs\Yin\JsonApi\Exception\JsonApiException;
use WoohooLabs\Yin\JsonApi\Schema\Error;

class InvalidDomainObjectException extends JsonApiException
{
    /**
     * InvalidDomainObjectException constructor.
     * @param mixed $domainObject
     */
    public function __construct($domainObject)
    {
        $identifier = $this->getIdentifier($domainObject);
        parent::__construct('Domain object '.$identifier.' is not a Model or Collection.');
    }

    /**
     * @param mixed $domainObject
     * @return string
     */
    private function getIdentifier($domainObject)
    {
        $type = gettype($domainObject);

        if ($type == 'object') {
            $type = get_class($domainObject);
        }

        return $type;
    }

    /**
     * @inheritdoc
     */
    protected function getErrors()
    {
        return [
            Error::create()
                ->setStatus(500)
                ->setCode("DOMAIN_OBJECT_TYPE_ERROR")
                ->setTitle("Invalid domain object error")
                ->setDetail($this->getMessage())
        ];
    }
}
