<?php

namespace Larapie\DataTransferObject\Resolvers;

use Throwable;
use ReflectionProperty;
use Larapie\DataTransferObject\Annotations\Inherit;
use Larapie\DataTransferObject\Property\PropertyType;
use Larapie\DataTransferObject\Exceptions\ConstraintInheritanceException;

class PropertyTypeResolver
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

    /**
     * @return PropertyType
     */
    public function resolve(): PropertyType
    {
        $type = new PropertyType();

        $docComment = $this->reflection->getDocComment();

        if (! $docComment) {
            $type->setNullable(true);

            if (($parentType = $this->resolvePossibleParentType()) !== null) {
                return $parentType;
            }

            return $type;
        }

        preg_match('/\@var ((?:(?:[\w|\\\\])+(?:\[\])?)+)/', $docComment, $matches);

        if (! count($matches)) {
            $type->setNullable(true);

            if (($parentType = $this->resolvePossibleParentType()) !== null) {
                return $parentType;
            }

            return $type;
        }

        $varDocComment = end($matches);

        $resolver = new VarTypeResolver($this->reflection);
        $types = $resolver->resolve($varDocComment);
        $type->setTypes($types);
        $type->setArrayTypes(str_replace('[]', '', $types));
        $type->setInitialized(true);
        $type->setNullable(strpos($varDocComment, 'null') !== false);

        return $type;
    }

    protected function resolvePossibleParentType()
    {
        foreach ($this->annotations as $annotation) {
            if ($annotation instanceof Inherit) {
                $type = $this->getParentType();
                if ($type->isInitialized()) {
                    return $type;
                }
            }
        }
    }

    protected function getParentType(): PropertyType
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
