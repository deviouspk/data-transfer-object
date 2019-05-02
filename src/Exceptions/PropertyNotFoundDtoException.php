<?php

namespace Larapie\DataTransferObject\Exceptions;

use RuntimeException;

class PropertyNotFoundDtoException extends RuntimeException
{
    public function __construct(string $property, string $className)
    {
        parent::__construct("Property `{$property}` not found on {$className}");
    }
}
