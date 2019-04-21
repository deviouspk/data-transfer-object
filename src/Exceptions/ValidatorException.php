<?php

namespace Larapie\DataTransferObject\Exceptions;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidatorException extends \Symfony\Component\Validator\Exception\ValidatorException
{

    /**
     * @var ConstraintViolationListInterface[]
     */
    protected $violations;

    public function __construct($violations)
    {
        $this->violations = $violations;
        parent::__construct($this->buildMessage($violations));
    }

    protected function buildMessage($violations): string
    {
        $message = "";
        foreach ($violations as $propertyName => $propertyViolations) {
            if (!empty($propertyViolations))
                $message = $message . "Exception on property '" . $propertyName . "': ";
            foreach ($propertyViolations as $violation) {
                $message = $message . $violation->getMessage() . "";
            }
            $message = $message . "\n";
        }
        return $message;
    }

    public function propertyViolationExists(string $property, string $violationClass)
    {
        if (array_key_exists($property, $this->violations)) {
            foreach ($this->violations[$property] as $violation) {
                if ($violation instanceof $violationClass)
                    return true;
            }
        }
        return false;
    }

    public function getViolations(): array
    {
        return $this->violations;
    }

}