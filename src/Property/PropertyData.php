<?php

namespace Larapie\DataTransferObject\Property;

use ReflectionProperty;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Larapie\DataTransferObject\Annotations\Optional;
use Larapie\DataTransferObject\Annotations\Immutable;
use Larapie\DataTransferObject\Resolvers\AnnotationResolver;
use Larapie\DataTransferObject\Resolvers\ConstraintsResolver;
use Larapie\DataTransferObject\Resolvers\PropertyTypeResolver;

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

    /** @var array */
    protected $annotations;

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
        $this->annotations = $this->resolveAnnotations($reflectionProperty);
        $this->constraints = $this->resolveConstraints($reflectionProperty);
        $this->type = $this->resolveType($reflectionProperty);
        $this->immutable = $this->resolveImmutable();
        $this->optional = $this->resolveOptional();
    }

    protected function resolveType(ReflectionProperty $reflection)
    {
        return (new PropertyTypeResolver($reflection, $this->annotations))->resolve();
    }

    protected function resolveAnnotations(ReflectionProperty $reflection)
    {
        $annotations = [];
        foreach (self::getReader()->getPropertyAnnotations($reflection) as $annotation) {
            $annotations[] = $annotation;
        }

        return (new AnnotationResolver($reflection))->resolve();
    }

    protected function resolveOptional()
    {
        foreach ($this->annotations as $annotation) {
            if ($annotation instanceof Optional) {
                return true;
            }
        }

        return false;
    }

    protected function resolveImmutable()
    {
        foreach ($this->annotations as $annotation) {
            if ($annotation instanceof Immutable) {
                return true;
            }
        }

        return false;
    }

    protected function resolveConstraints(ReflectionProperty $reflection)
    {
        return (new ConstraintsResolver($reflection, $this->annotations))->resolve();
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

    /**
     * @return array
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
    }
}
