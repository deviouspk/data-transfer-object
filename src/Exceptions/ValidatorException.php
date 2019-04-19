<?php

namespace Larapie\DataTransferObject\Exceptions;

class ValidatorException extends \Symfony\Component\Validator\Exception\ValidatorException
{
    public function __construct($propertyName, $violations)
    {
        $message = "Property '$propertyName'. ";
        foreach ($violations as $violation) {
            $message = $message  . $violation->getMessage(). "\n";
        }
        parent::__construct($message);
    }

}