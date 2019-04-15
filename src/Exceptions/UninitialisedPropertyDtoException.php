<?php

namespace Larapie\DataTransferObject\Exceptions;

use TypeError;
use Larapie\DataTransferObject\Contracts\PropertyContract;

class UninitialisedPropertyDtoException extends TypeError
{
    public function __construct(PropertyContract $property)
    {
        parent::__construct("Non-nullable property {$property->getFqn()} has not been initialized.");
    }
}
