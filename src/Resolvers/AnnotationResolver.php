<?php

namespace Larapie\DataTransferObject\Resolvers;

use ReflectionProperty;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\AnnotationReader;

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
        \Doctrine\Common\Annotations\AnnotationRegistry::registerUniqueLoader('class_exists');
        self::$reader = $reader;
    }

    protected static function getReader(): Reader
    {
        if (self::$reader === null) {
            self::setReader(new CachedReader(new AnnotationReader(), new ArrayCache()));
        }

        return self::$reader;
    }
}
