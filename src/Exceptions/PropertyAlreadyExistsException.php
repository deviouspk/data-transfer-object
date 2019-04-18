<?php

namespace Larapie\DataTransferObject\Exceptions;

use TypeError;

class PropertyAlreadyExistsException extends TypeError
{
    public function __construct(string $property)
    {
        $message = "Cannot add $property it's already present on the data value object";
        parent::__construct($message);
    }
}
