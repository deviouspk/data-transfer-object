<?php

namespace Larapie\DataTransferObject\Exceptions;

use TypeError;

class ImmutablePropertyDtoException extends TypeError
{
    public function __construct(string $property)
    {
        parent::__construct("Cannot change the value of property '{$property}'. It is immutable!");
    }
}
