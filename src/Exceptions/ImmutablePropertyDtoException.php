<?php

namespace Larapie\DataTransferObject\Exceptions;

use RuntimeException;

class ImmutablePropertyDtoException extends RuntimeException
{
    public function __construct(string $property)
    {
        parent::__construct("Cannot change the value of property '{$property}'. It is immutable!");
    }
}
