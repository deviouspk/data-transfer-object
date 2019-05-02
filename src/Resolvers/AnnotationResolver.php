<?php

namespace Larapie\DataTransferObject\Resolvers;

use ReflectionProperty;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

class AnnotationResolver
{
    /**
     * @var ReflectionProperty
     */
    protected $reflection;

    /** @var ?Reader */
    protected static $reader;

    /**
     * TypeResolver constructor.
     * @param ReflectionProperty $reflection
     */
    public function __construct(ReflectionProperty $reflection)
    {
        $this->reflection = $reflection;
    }

    public function resolve(): array
    {
        $annotations = [];
        foreach (self::getReader()->getPropertyAnnotations($this->reflection) as $annotation) {
            $annotations[get_class($annotation)] = $annotation;
        }

        return $annotations;
    }

    public static function setReader()
    {
        //IMPLEMENTED LIKE THIS TO SUPPORT DOCTRINE\ANNOTATIONS v1.* & v2.*

        if (class_exists(AnnotationRegistry::class)) {
            AnnotationRegistry::registerUniqueLoader('class_exists');
            self::$reader = new AnnotationReader();
        } else {
            self::$reader = new \Doctrine\Annotations\AnnotationReader();
        }
    }

    protected static function getReader()
    {
        if (self::$reader === null) {
            self::setReader();
        }

        return self::$reader;
    }
}
