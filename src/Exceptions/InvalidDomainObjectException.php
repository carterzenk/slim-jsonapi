<?php

namespace CarterZenk\JsonApi\Exceptions;

class InvalidDomainObjectException extends \Exception
{
    public function __construct($domainObject, $code = 0, \Exception $previous = null)
    {
        $message = 'Domain object '.get_class($domainObject).' is not a Model or Collection.';

        parent::__construct($message, $code, $previous);
    }
}