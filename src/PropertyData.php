<?php

namespace Larapie\DataTransferObject;

use ReflectionProperty;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Validator\Constraint;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Larapie\DataTransferObject\Annotations\Optional;
use Larapie\DataTransferObject\Annotations\Immutable;
use Larapie\DataTransferObject\Resolvers\PropertyTypeResolver;
use Larapie\DataTransferObject\Resolvers\AnnotationResolver;

class PropertyData
{
    /** @var string */
    protected $name;

    /** @var PropertyType */
    protected $type;

    /** @var string */
    protected $fqn;

    /** @var bool */
    protected $optional;

    /** @var bool */
    protected $immutable;

    /** @var array */
    protected $constraints;

    /** @var Reader */
    private static $reader;

    /**
     * PropertyData constructor.
     * @param ReflectionProperty $property
     */
    public function __construct(ReflectionProperty $property)
    {
        $this->name = $property->getName();
        $this->fqn = "{$property->getDeclaringClass()->getName()}::{$property->getName()}";
        $this->boot($property);
    }

    protected function boot(reflectionProperty $reflectionProperty)
    {
        $annotations = $this->resolveAnnotations($reflectionProperty);
        $this->type = $this->resolveType($reflectionProperty);
        $this->immutable = $this->resolveImmutable($annotations);
        $this->constraints = $this->resolveConstraints($annotations);
        $this->optional = $this->resolveOptional($annotations);
    }

    protected function resolveType(ReflectionProperty $reflection)
    {
        return (new PropertyTypeResolver($reflection))->resolve();
    }

    protected function resolveAnnotations(ReflectionProperty $reflection)
    {
        $annotations = [];
        foreach (self::getReader()->getPropertyAnnotations($reflection) as $annotation) {
            $annotations[] = $annotation;
        }

        return (new AnnotationResolver($reflection))->resolve();
    }

    protected function resolveOptional($annotations)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Optional) {
                return true;
            }
        }

        return false;
    }

    protected function resolveImmutable($annotations)
    {
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Immutable) {
                return true;
            }
        }

        return false;
    }

    protected function resolveConstraints($annotations)
    {
        $constraints = [];
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Constraint) {
                $constraints[] = $annotation;
            }
        }

        return $constraints;
    }

    protected static function getReader()
    {
        AnnotationRegistry::registerUniqueLoader('class_exists');
        if (! isset(self::$reader)) {
            self::$reader = new AnnotationReader();
        }

        return self::$reader;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return PropertyType
     */
    public function getType(): PropertyType
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getFqn(): string
    {
        return $this->fqn;
    }

    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @return bool
     */
    public function isImmutable(): bool
    {
        return $this->immutable;
    }

    /**
     * @return array
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }
}
