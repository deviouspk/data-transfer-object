<?php

namespace Larapie\DataTransferObject\Exceptions;


use RuntimeException;

class UnknownPropertiesDtoException extends RuntimeException
{
    public function __construct(array $properties, string $className)
    {
        $propertyNames = implode(', ', array_keys($properties));
        if (count($properties) > 1) {
            $message = "Parameters {$propertyNames} are not allowed as input on {$className}";
        } else {
            $message = "Parameter {$propertyNames} is not allowed as input on {$className}";
        }
        parent::__construct($message);
    }
}
