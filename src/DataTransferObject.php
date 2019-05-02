<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject;

use ReflectionException;
use Larapie\DataTransferObject\Property\Property;
use Larapie\DataTransferObject\Contracts\DtoContract;
use Larapie\DataTransferObject\Factories\PropertyFactory;
use Larapie\DataTransferObject\Exceptions\ValidatorException;
use Larapie\DataTransferObject\Exceptions\ImmutableDtoException;
use Larapie\DataTransferObject\Contracts\WithAdditionalProperties;
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

    /** @var bool */
    protected $validate = true;

    public function __construct(array $parameters)
    {
        $this->boot($parameters);
    }

    /**
     * Boot the dto and process all parameters.
     * @param array $parameters
     * @throws ReflectionException
     */
    protected function boot(array $parameters): void
    {
        $this->properties = (new PropertyFactory($this))->build($parameters);
    }

    public function setImmutable(bool $immutable): void
    {
        if ($immutable) {
            if (! $this->isImmutable()) {
                $this->immutable = true;
                foreach ($this->properties as $property) {
                    $property->chainImmutable($immutable);
                }
            }
        } else {
            $this->immutable = true;
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
        if (! isset($this->properties[$name])) {
            throw new PropertyNotFoundDtoException($name, get_class($this));
        }

        if ($this->properties[$name]->isImmutable()) {
            throw new ImmutablePropertyDtoException($name);
        }
        $this->$name = $value;
    }

    protected function propertyExists(string $propertyName)
    {
        return array_key_exists($propertyName, $this->properties);
    }

    public function &__get($name)
    {
        if (! $this->propertyExists($name)) {
            if ($this instanceof WithAdditionalProperties) {
                if (array_key_exists($name, $this->with)) {
                    if ($this->isImmutable()) {
                        $value = $this->with[$name];

                        return clone $value;
                    }

                    return $this->with[$name];
                }
                throw new PropertyNotFoundDtoException($name, static::class);
            }
        }
        $property = $this->properties[$name];
        $violations = $property->getViolations();
        if ($violations->count() > 0) {
            throw new ValidatorException([$name => $violations]);
        }
        if ($this->isImmutable()) {
            $value = $this->properties[$name]->getValue();

            return $value;
        }

        return $this->properties[$name]->value;
    }

    public function isImmutable(): bool
    {
        return $this->immutable;
    }

    public function isValid()
    {
        return empty($this->getValidationViolations());
    }

    public function validationEnabled()
    {
        return $this->validate;
    }

    public function enableValidation()
    {
        $this->validate = true;
    }

    public function disableValidation()
    {
        $this->validate = false;
    }

    public function all(): array
    {
        $data = [];

        if ($this->validate) {
            $this->validate();
        }

        foreach ($this->properties as $property) {
            $data[$property->getName()] = ($value = $property->getValue()) instanceof DataTransferObject ? $value->toArray() : $value;
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
            if ($this->validate) {
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
            $array = array_intersect_key($data, array_flip((array) $this->onlyKeys));
        } else {
            foreach ($data as $key => $propertyValue) {
                if (array_key_exists($key, $this->with) || (array_key_exists($key, $this->properties) && $this->properties[$key]->isVisible() && $this->properties[$key]->isInitialized())) {
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

            if (! is_array($value)) {
                continue;
            }

            $array[$key] = $this->parseArray($value);
        }

        return $array;
    }

    public function getValidationViolations()
    {
        $violations = [];
        foreach ($this->properties as $name => $property) {
            $value = $property->getValue();
            if ($value instanceof DataTransferObject) {
                $nestedViolations = $this->recursivelySortKeys($value->getValidationViolations(), $name);
                $violations = array_merge($violations, $nestedViolations);
            }
            if (is_iterable($value)) {
                foreach ($value as $key => $potentialDto) {
                    if ($potentialDto instanceof DataTransferObject) {
                        $nestedViolations = $this->recursivelySortKeys($potentialDto->getValidationViolations(), "$name.$key");
                        $violations = array_merge($violations, $nestedViolations);
                    }
                }
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
        $violations = $this->getValidationViolations();
        if (! empty($violations)) {
            throw new ValidatorException($violations);
        }

        return true;
    }

    protected function recursivelySortKeys(array $array, $str = '')
    {
        $sortedArray = [];
        foreach ($array as $key => $val) {
            if (is_array($val)) {
                if ($str == '') {
                    $this->recursivelySortKeys($val, $key);
                } else {
                    $this->recursivelySortKeys($val, $str.'.'.$key);
                }
            } else {
                if ($str == '') {
                    $sortedArray[$key] = $val;
                    echo $key."\n";
                } else {
                    $sortedArray[$str.'.'.$key] = $val;
                }
            }
        }

        return $sortedArray;
    }
}
