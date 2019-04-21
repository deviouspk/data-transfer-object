<?php


namespace Larapie\DataTransferObject\Violations;

use Symfony\Component\Validator\ConstraintViolation;

class PropertyRequiredViolation extends ConstraintViolation
{

    /**
     * RequiredPropertyViolation constructor.
     */
    public function __construct()
    {
        parent::__construct(
            "is required",
            "is required",
            [],
            null,
            "",
            null
        );
    }
}