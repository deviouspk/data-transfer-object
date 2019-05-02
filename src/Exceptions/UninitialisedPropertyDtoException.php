<?php

namespace Larapie\DataTransferObject\Exceptions;

use Larapie\DataTransferObject\Contracts\PropertyContract;
use RuntimeException;

class UninitialisedPropertyDtoException extends RuntimeException
{
    public function __construct(PropertyContract $property)
    {
        parent::__construct("Parameter {$property->getName()} is required and cannot be null.");
    }
}
