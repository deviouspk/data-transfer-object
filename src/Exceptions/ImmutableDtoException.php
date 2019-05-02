<?php

namespace Larapie\DataTransferObject\Exceptions;

use RuntimeException;

class ImmutableDtoException extends RuntimeException
{
    public function __construct(string $property)
    {
        parent::__construct("Cannot change the value of property '{$property}' on an immutable data transfer object");
    }
}
