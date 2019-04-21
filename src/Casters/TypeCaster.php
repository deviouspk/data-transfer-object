<?php


namespace Larapie\DataTransferObject\Casters;


use Larapie\DataTransferObject\Contracts\DtoContract;
use Larapie\DataTransferObject\PropertyType;

class TypeCaster
{
    /**
     * @var PropertyType $type
     */
    protected $type;

    /**
     * TypeCaster constructor.
     * @param PropertyType $type
     */
    public function __construct(PropertyType $type)
    {
        $this->type = $type;
    }

    public function cast($value)
    {
        $value = $this->castDto($value);

        if (is_array($value)) {
            $value = $this->shouldBeCastToCollection($value) ? $this->castCollection($value) : $this->castDto($value);
        }
        return $value;
    }


    protected function castDto($value)
    {
        foreach ($this->type->getTypes() as $type) {
            if (is_subclass_of($type, DtoContract::class)) {
                if (is_array($value))
                    return new $type($value);
            }
        }
        return $value;
    }

    protected function castCollection(array $values)
    {
        $castTo = null;

        foreach ($this->type->getArrayTypes() as $type) {
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
}