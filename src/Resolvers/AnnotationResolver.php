<?php

namespace Larapie\DataTransferObject\Resolvers;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use ReflectionProperty;

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

    public function resolve() : array
    {
        $annotations = [];
        foreach (self::getReader()->getPropertyAnnotations($this->reflection) as $annotation) {
            $annotations[get_class($annotation)] = $annotation;
        }

        return $annotations;
    }

    public static function setReader(Reader $reader)
    {
        AnnotationRegistry::registerUniqueLoader('class_exists');
        self::$reader = $reader;
    }

    protected static function getReader(): Reader
    {
        if (self::$reader === null) {
            self::setReader(new AnnotationReader());
        }

        return self::$reader;
    }
}
