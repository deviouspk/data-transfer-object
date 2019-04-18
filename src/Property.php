<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\PhpFileCache;
use Larapie\DataTransferObject\Annotations\Immutable;
use Larapie\DataTransferObject\Annotations\Optional;
use phpDocumentor\Reflection\Types\Self_;
use ReflectionProperty;
use Larapie\DataTransferObject\Contracts\DtoContract;
use Larapie\DataTransferObject\Contracts\PropertyContract;
use Larapie\DataTransferObject\Exceptions\InvalidTypeDtoException;

class Property implements PropertyContract
{
    /** @var array */
    protected const TYPE_MAPPING = [
        'int' => 'integer',
        'bool' => 'boolean',
        'float' => 'double',
    ];

    /** @var bool */
    protected $hasTypeDeclaration = false;

    /** @var bool */
    protected $nullable = false;

    /** @var bool */
    protected $optional;

    /** @var bool */
    protected $initialised = false;

    /** @var bool */
    protected $immutable;

    /** @var bool */
    protected $visible = true;

    /** @var array */
    protected $types = [];

    /** @var array */
    protected $arrayTypes = [];

    /** @var mixed */
    protected $default;

    /** @var mixed */
    public $value;

    /** @var ReflectionProperty */
    protected $reflection;

    /** @var array */
    protected $annotations;

    /** @var ?Reader */
    protected static $reader;

    public function __construct(ReflectionProperty $reflectionProperty)
    {
        $this->reflection = $reflectionProperty;
        $this->resolveTypeDefinition();
    }

    protected function resolveTypeDefinition()
    {
        $docComment = $this->reflection->getDocComment();

        if (!$docComment) {
            $this->setNullable(true);

            return;
        }

        preg_match('/\@var ((?:(?:[\w|\\\\])+(?:\[\])?)+)/', $docComment, $matches);

        if (!count($matches)) {
            $this->setNullable(true);

            return;
        }

        $varDocComment = end($matches);

        $this->types = explode('|', $varDocComment);
        $this->arrayTypes = str_replace('[]', '', $this->types);
        $this->setAnnotations();

        $this->hasTypeDeclaration = true;

        $this->setNullable(strpos($varDocComment, 'null') !== false);
    }

    protected function isValidType($value): bool
    {
        if (!$this->hasTypeDeclaration) {
            return true;
        }

        if ($this->nullable() && $value === null) {
            return true;
        }

        foreach ($this->types as $currentType) {
            $isValidType = $this->assertTypeEquals($currentType, $value);

            if ($isValidType) {
                return true;
            }
        }

        return false;
    }

    protected function cast($value)
    {
        $castTo = null;

        foreach ($this->types as $type) {
            if (!is_subclass_of($type, DtoContract::class)) {
                continue;
            }

            $castTo = $type;

            break;
        }

        if (!$castTo) {
            return $value;
        }

        return new $castTo($value);
    }

    protected function castCollection(array $values)
    {
        $castTo = null;

        foreach ($this->arrayTypes as $type) {
            if (!is_subclass_of($type, DtoContract::class)) {
                continue;
            }

            $castTo = $type;

            break;
        }

        if (!$castTo) {
            return $values;
        }

        $casts = [];

        foreach ($values as $value) {
            $casts[] = new $castTo($value);
        }

        return $casts;
    }

    protected function shouldBeCastToCollection(array $values): bool
    {
        if (empty($values)) {
            return false;
        }

        foreach ($values as $key => $value) {
            if (is_string($key)) {
                return false;
            }

            if (!is_array($value)) {
                return false;
            }
        }

        return true;
    }

    protected function assertTypeEquals(string $type, $value): bool
    {
        if (strpos($type, '[]') !== false) {
            return $this->isValidGenericCollection($type, $value);
        }

        if ($type === 'mixed' && $value !== null) {
            return true;
        }

        return $value instanceof $type
            || gettype($value) === (self::TYPE_MAPPING[$type] ?? $type);
    }

    protected function isValidGenericCollection(string $type, $collection): bool
    {
        if (!is_array($collection)) {
            return false;
        }

        $valueType = str_replace('[]', '', $type);

        foreach ($collection as $value) {
            if (!$this->assertTypeEquals($valueType, $value)) {
                return false;
            }
        }

        return true;
    }

    public function set($value): void
    {
        if (is_array($value)) {
            $value = $this->shouldBeCastToCollection($value) ? $this->castCollection($value) : $this->cast($value);
        }

        if (!$this->isValidType($value)) {
            throw new InvalidTypeDtoException($this, $value);
        }

        $this->setInitialized(true);

        $this->value = $value;
    }

    public function setInitialized(bool $bool): void
    {
        $this->initialised = $bool;
    }

    public function isInitialized(): bool
    {
        return $this->initialised;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function getFqn(): string
    {
        return "{$this->reflection->getDeclaringClass()->getName()}::{$this->reflection->getName()}";
    }

    public function nullable(): bool
    {
        return $this->nullable;
    }

    public function setNullable(bool $bool): void
    {
        $this->nullable = $bool;
    }

    public function immutable(): bool
    {
        if (!isset($this->immutable))
            $this->immutable = $this->getAnnotation(Immutable::class) !== null;
        return $this->immutable;
    }

    public function setImmutable(bool $immutable): void
    {
        $this->immutable = $immutable;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setDefault($default): void
    {
        $this->default = $default;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $bool): bool
    {
        return $this->visible = $bool;
    }

    public function getValue()
    {
        if (!$this->nullable() && $this->value == null) {
            return $this->getDefault();
        }

        return $this->value;
    }

    public function getValueFromReflection($object)
    {
        return $this->reflection->getValue($object);
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }

    public function isOptional(): bool
    {
        if (!isset($this->optional))
            $this->optional = $this->getAnnotation(Optional::class) !== null;
        return $this->optional;
    }

    public function setOptional(): bool
    {
        return $this->optional = true;
    }

    public function setRequired(): bool
    {
        return $this->optional = false;
    }

    protected function getReader() :Reader{
        if(self::$reader === null)
            self::setReader(new CachedReader(new AnnotationReader(), new ArrayCache()));
        return self::$reader;
    }

    public static function setReader(Reader $reader)
    {
        \Doctrine\Common\Annotations\AnnotationRegistry::registerUniqueLoader('class_exists');
        self::$reader = $reader;
    }

    protected function setAnnotations()
    {
        $annotations = [];
        foreach(self::getReader()->getPropertyAnnotations($this->reflection) as $annotation){
            $annotations[get_class($annotation)]=$annotation;
        }
        $this->annotations = $annotations;
    }

    protected function getAnnotation($annotation)
    {
        return $this->annotations[$annotation] ?? null;
    }
}
