<?php

namespace Larapie\DataTransferObject\Exceptions;

use RuntimeException;
use Larapie\DataTransferObject\Contracts\PropertyContract;

class CastingFailedException extends RuntimeException
{
    public function __construct(PropertyContract $property)
    {
        parent::__construct("Parameter {$property->getName()} is required and cannot be null.");
    }
}
