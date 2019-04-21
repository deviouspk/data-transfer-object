<?php


namespace Larapie\DataTransferObject\Constraints;

use Larapie\DataTransferObject\Validator\TypesValidator;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class Types extends Constraint
{
    public $message = 'This value should be of type {{ type }}.';
    public $types;

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'types';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions()
    {
        return ['types'];
    }

    public function validatedBy()
    {
        return TypesValidator::class;
    }
}