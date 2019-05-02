<?php

namespace Larapie\DataTransferObject\Exceptions;

use RuntimeException;

class PropertyAlreadyExistsException extends RuntimeException
{
    public function __construct(string $property)
    {
        $message = "Cannot add $property it's already present on the data value object";
        parent::__construct($message);
    }
}
