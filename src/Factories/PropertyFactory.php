<?php

namespace Larapie\DataTransferObject\Factories;

use ReflectionClass;
use ReflectionProperty;
use Larapie\DataTransferObject\Property;
use Larapie\DataTransferObject\Contracts\DtoContract;
use Larapie\DataTransferObject\Contracts\AdditionalProperties;
use Larapie\DataTransferObject\Contracts\WithAdditionalProperties;
use Larapie\DataTransferObject\Exceptions\UnknownPropertiesDtoException;

class PropertyFactory
{
    /**
     * @var DtoContract
     */
    protected $dto;

    /**
     * @var Property[]
     */
    private static $cache = [];

    /**
     * PropertyValueFactory constructor.
     * @param DtoContract $dto
     */
    public function __construct(DtoContract &$dto)
    {
        $this->dto = $dto;
    }

    public function build(array $parameters)
    {
        $properties = [];
        foreach ($this->buildPublicProperties() as $property) {
            if (array_key_exists($property->getName(), $parameters)) {
                $property->set($parameters[$property->getName()]);
            }

            /* add the property to an associative array with the name as key */
            $properties[$property->getName()] = $property;

            /* remove the property from the value object and parameters array  */
            unset($parameters[$property->getName()], $this->dto->{$property->getName()});
        }

        $this->checkRemainingProperties($parameters);

        return $properties;
    }

    protected function getDtoClass(): string
    {
        return get_class($this->dto);
    }

    protected function buildPublicProperties(): array
    {
        if (! isset(self::$cache[$this->getDtoClass()])) {
            $class = new ReflectionClass($this->dto);

            $properties = [];
            foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
                $property = new Property($reflectionProperty);

                //Set default value
                if (($default = $reflectionProperty->getValue($this->dto)) !== null) {
                    $property->set($reflectionProperty->getValue($this->dto));
                }

                $properties[$reflectionProperty->getName()] = $property;
            }
            self::$cache[$this->getDtoClass()] = $properties;

            return $properties;
        }

        return $this->getFreshProperties();
    }

    protected function getFreshProperties()
    {
        $properties = [];
        foreach (self::$cache[$this->getDtoClass()] as $key => $property) {
            $property = clone $property;
            $property->reset();
            $properties[$key] = $property;
        }

        return $properties;
    }

    protected function checkRemainingProperties(array $parameters)
    {
        if (empty($parameters)) {
            return;
        } elseif ($this instanceof WithAdditionalProperties) {
            foreach ($parameters as $name => $parameter) {
                $this->dto->with($name, $parameter);
            }
        } elseif ($this instanceof AdditionalProperties) {
            return;
        }
        throw new UnknownPropertiesDtoException($parameters, $this->getDtoClass());
    }
}
