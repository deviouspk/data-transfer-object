<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject;

use Larapie\DataTransferObject\Contracts\DtoContract;
use Larapie\DataTransferObject\Factories\PropertyFactory;
use Larapie\DataTransferObject\Contracts\AutomaticValidation;
use Larapie\DataTransferObject\Exceptions\ValidatorException;
use Larapie\DataTransferObject\Exceptions\ImmutableDtoException;
use Larapie\DataTransferObject\Violations\PropertyRequiredViolation;
use Larapie\DataTransferObject\Exceptions\PropertyNotFoundDtoException;
use Larapie\DataTransferObject\Exceptions\ImmutablePropertyDtoException;
use Larapie\DataTransferObject\Exceptions\PropertyAlreadyExistsException;

/**
 * Class DataTransferObject.
 */
abstract class DataTransferObject implements DtoContract
{
    /** @var array */
    protected $onlyKeys = [];

    /** @var array */
    protected $with = [];

    /** @var Property[] */
    protected $properties = [];

    /** @var bool */
    protected $immutable = false;

    public function __construct(array $parameters)
    {
        $this->boot($parameters);
    }

    /**
     * Boot the dto and process all parameters.
     * @param array $parameters
     * @throws \ReflectionException
     */
    protected function boot(array $parameters): void
    {
        $this->properties = (new PropertyFactory($this))->build($parameters);
        $this->determineImmutability();
        if ($this instanceof AutomaticValidation) {
            $this->validate();
        }
    }

    protected function determineImmutability()
    {
        /* If the dto itself is not immutable but some properties are chain them immutable  */
        foreach ($this->properties as $property) {
            if ($property->isImmutable()) {
                $this->chainPropertyImmutable($property);
            }
        }
    }

    protected function setImmutable(): void
    {
        if (!$this->isImmutable()) {
            $this->immutable = true;
            foreach ($this->properties as $property) {
                $this->chainPropertyImmutable($property);
            }
        }
    }

    protected function chainPropertyImmutable(Property $property)
    {
        $dto = $property->getValue();
        if ($dto instanceof DataTransferObject) {
            $dto->setImmutable();
        } elseif (is_iterable($dto)) {
            foreach ($dto as $aPotentialDto) {
                if ($aPotentialDto instanceof DataTransferObject) {
                    $aPotentialDto->setImmutable();
                }
            }
        }
    }

    /**
     * Immutable behavior
     * Throw error if a user tries to set a property.
     * @param $name
     * @param $value
     * @throws ImmutableDtoException|ImmutablePropertyDtoException|PropertyNotFoundDtoException
     */
    public function __set($name, $value)
    {
        if ($this->immutable) {
            throw new ImmutableDtoException($name);
        }
        if (!isset($this->properties[$name])) {
            throw new PropertyNotFoundDtoException($name, get_class($this));
        }

        if ($this->properties[$name]->isImmutable()) {
            throw new ImmutablePropertyDtoException($name);
        }
        $this->$name = $value;
    }

    public function &__get($name)
    {
        return $this->properties[$name]->value;
    }

    public function isImmutable(): bool
    {
        return $this->immutable;
    }

    public function all(): array
    {
        $data = [];

        foreach ($this->properties as $property) {
            $value = $property->getValue();
            $data[$property->getName()] = $value instanceof DtoContract ? $value->toArray() : $value;
        }

        return array_merge($data, $this->with);
    }

    public function only(string ...$keys): DtoContract
    {
        $this->onlyKeys = array_merge($this->onlyKeys, $keys);

        return $this;
    }

    public function except(string ...$keys): DtoContract
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->with)) {
                unset($this->with[$key]);
            }
            $property = $this->properties[$key] ?? null;
            if (isset($property)) {
                $property->setVisible(false);
            }
        }

        return $this;
    }

    public function with(string $key, $value): DtoContract
    {
        if (array_key_exists($key, $this->properties)) {
            throw new PropertyAlreadyExistsException($key);
        }

        return $this->override($key, $value);
    }

    public function override(string $key, $value): DtoContract
    {
        if ($this->isImmutable()) {
            throw new ImmutableDtoException($key);
        }
        if (($propertyExists = array_key_exists($key, $this->properties) && $this->properties[$key]->isImmutable())) {
            throw new ImmutablePropertyDtoException($key);
        }

        if ($propertyExists) {
            $property = $this->properties[$key];
            $property->set($value);
            if ($this instanceof AutomaticValidation) {
                $this->validate();
            }
        } else {
            $this->with[$key] = $value;
        }

        return $this;
    }

    public function toArray(): array
    {
        $data = $this->all();
        $array = [];

        if (count($this->onlyKeys)) {
            $array = array_intersect_key($data, array_flip((array)$this->onlyKeys));
        } else {
            foreach ($data as $key => $propertyValue) {
                if (array_key_exists($key, $this->properties) && $this->properties[$key]->isVisible() && $this->properties[$key]->isInitialized()) {
                    $array[$key] = $propertyValue;
                }
            }
        }

        return $this->parseArray($array);
    }

    protected function parseArray(array $array): array
    {
        foreach ($array as $key => $value) {
            if (
                $value instanceof DataTransferObject
                || $value instanceof DataTransferObjectCollection
            ) {
                $array[$key] = $value->toArray();

                continue;
            }

            if (!is_array($value)) {
                continue;
            }

            $array[$key] = $this->parseArray($value);
        }

        return $array;
    }

    public function throwValidationException()
    {
        $violations = [];
        foreach ($this->properties as $propertyName => $property) {
            $violationList = $property->getViolations();

            if ($violationList === null || $violationList->count() <= 0) {
                continue;
            }
            foreach ($violationList as $violation) {
                if ($violation instanceof PropertyRequiredViolation) {
                    $violations[$propertyName] = [$violation];
                    break;
                }
                $violations[$propertyName][] = $violation;
            }
        }
        throw new ValidatorException($violations);
    }

    protected function getViolations()
    {
        $violations = [];
        foreach ($this->properties as $name => $property) {
            if (($value = $property->getValue()) instanceof DataTransferObject) {
                $nestedViolations = $this->recursivelySortKeys($value->getViolations(),$name);
                $violations = array_merge($violations,$nestedViolations);
            }
            $violationList = $property->getViolations();
            if ($violationList === null || $violationList->count() <= 0) {
                continue;
            }
            $violations[$name] = $violationList;
        }
        return $violations;
    }

    public function validate()
    {
        $violations = $this->getViolations();
        if (!empty($violations)) {
            throw new ValidatorException($violations);
        }
    }

    protected function recursivelySortKeys(array $array, $str = '')
    {
        $sortedArray = [];
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                if ($str == '') {
                    $this->recursivelySortKeys($val, $key);
                } else {
                    $this->recursivelySortKeys($val, $str . '.' . $key);
                }
            } else {
                if ($str == '') {
                    $sortedArray[$key] = $val;
                    echo $key . "\n";
                } else {
                    $sortedArray[$str . '.' . $key] = $val;
                }
            }
        }
        return $sortedArray;
    }
}
