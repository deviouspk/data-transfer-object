<?php

namespace Larapie\DataTransferObject\Resolvers;

use Throwable;
use ReflectionProperty;
use Symfony\Component\Validator\Constraint;
use Larapie\DataTransferObject\Annotations\Inherit;
use Larapie\DataTransferObject\Exceptions\ConstraintInheritanceException;

class ConstraintsResolver
{
    /**
     * @var ReflectionProperty
     */
    protected $reflection;

    /**
     * @var array
     */
    protected $annotations;

    /**
     * TypeResolver constructor.
     * @param ReflectionProperty $reflection
     * @param array $annotations
     */
    final public function __construct(ReflectionProperty $reflection, array $annotations)
    {
        $this->reflection = $reflection;
        $this->annotations = $annotations;
    }

    public function resolve(): array
    {
        $constraints = [];
        foreach ($this->annotations as $annotation) {
            if ($annotation instanceof Inherit) {
                $constraints = array_merge($constraints, $this->getParentConstraints());
            } elseif ($annotation instanceof Constraint) {
                $constraints[] = $annotation;
            }
        }

        return $constraints;
    }

    protected function getParentConstraints()
    {
        try {
            if ($parentClass = $this->reflection->getDeclaringClass()->getParentClass()) {
                $parentProperty = $parentClass->getProperty($this->reflection->getName());
            }
        } catch (Throwable $exception) {
            throw new ConstraintInheritanceException('There is no parent property to inherit from');
        }

        return (new static($parentProperty, (new AnnotationResolver($parentProperty))->resolve()))->resolve();
    }
}
