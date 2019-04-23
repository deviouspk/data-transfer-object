<?php

namespace Larapie\DataTransferObject\Violations;

use Symfony\Component\Validator\ConstraintViolation;

class InvalidPropertyTypeViolation extends ConstraintViolation
{
    /**
     * RequiredPropertyViolation constructor.
     */
    public function __construct(array $types)
    {
        parent::__construct(
            'invalid type should be '.implode('|', $types),
            'invalid type should be '.implode('|', $types),
            [],
            null,
            '',
            null
        );
    }
}
