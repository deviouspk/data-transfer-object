<?php

namespace Larapie\DataTransferObject\Exceptions;

use TypeError;
use Larapie\DataTransferObject\Contracts\PropertyContract;

class CastingFailedException extends TypeError
{
    public function __construct(PropertyContract $property)
    {
        parent::__construct("Parameter {$property->getName()} is required and cannot be null.");
    }
}
