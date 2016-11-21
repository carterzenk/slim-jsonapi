<?php

namespace CarterZenk\JsonApi\Exceptions;

class RelationshipExistenceException extends \Exception
{
    public function __construct($domainObject, $relationshipName, $code = 0, \Exception $previous = null)
    {
        $message = 'Relationship method '.$relationshipName.' not found on domain object '.get_class($domainObject).'.';

        parent::__construct($message, $code, $previous);
    }
}
