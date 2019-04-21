<?php


namespace Larapie\DataTransferObject;


class PropertyType
{
    /** @var array */
    protected const TYPE_MAPPING = [
        'int' => 'integer',
        'bool' => 'boolean',
        'float' => 'double',
    ];

    protected $types = [];

    protected $arrayTypes = [];

    protected $nullable = false;

    protected $hasType = false;

    /**
     * @return array
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param array $types
     */
    public function setTypes(array $types): void
    {
        $this->types = $types;
    }

    /**
     * @return array
     */
    public function getArrayTypes(): array
    {
        return $this->arrayTypes;
    }

    /**
     * @param array $arrayTypes
     */
    public function setArrayTypes(array $arrayTypes): void
    {
        $this->arrayTypes = $arrayTypes;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @param bool $nullable
     */
    public function setNullable(bool $nullable): void
    {
        $this->nullable = $nullable;
    }

    /**
     * @return bool
     */
    public function isHasType(): bool
    {
        return $this->hasType;
    }

    /**
     * @param bool $hasType
     */
    public function setHasType(bool $hasType): void
    {
        $this->hasType = $hasType;
    }

    public function isValid($value): bool
    {
        if (!$this->hasType) {
            return true;
        }

        if ($this->nullable && $value === null) {
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




}