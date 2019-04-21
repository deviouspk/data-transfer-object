<?php

namespace Larapie\DataTransferObject\Validator;

use Symfony\Component\Validator\Constraint;
use Larapie\DataTransferObject\Constraints\Types;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TypesValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (! $constraint instanceof Types) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Type');
        }

        if (null === $value) {
            return;
        }

        //TODO IMPLEMENT TYPE VALIDATION;
    }
}
