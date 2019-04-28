<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Property;

use ReflectionProperty;
use Symfony\Component\Validator\ValidatorBuilder;
use Larapie\DataTransferObject\Casters\TypeCaster;
use Larapie\DataTransferObject\DataTransferObject;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Larapie\DataTransferObject\Violations\PropertyRequiredViolation;
use Larapie\DataTransferObject\Violations\InvalidPropertyTypeViolation;

class Property
{
    /** @var PropertyData */
    protected $data;

    /** @var mixed */
    public $value;

    /** @var bool */
    protected $initialized = false;

    /** @var bool */
    protected $visible = true;

    /** @var ConstraintViolationListInterface|null */
    protected $violations;

    /**
     * PropertyValue constructor.
     * @param ReflectionProperty $reflection
     */
    public function __construct(ReflectionProperty $reflection)
    {
        $this->boot($reflection);
    }

    public function boot(ReflectionProperty $property)
    {
        $this->data = new PropertyData($property);
        $this->initViolations();
    }

    protected function initViolations()
    {
        $this->setViolations(new ConstraintViolationList());
        if (! $this->data->isOptional()) {
            $this->violations->add(new PropertyRequiredViolation());
        }
    }

    public function set($value): void
    {
        $value = (new TypeCaster($this->data->getType()))->cast($value);
        $this->value = $this->disableAutoValidation($value);
        $this->initialized = true;
        $this->setViolations($this->validate($value));
    }

    protected function disableAutoValidation($value)
    {
        if (is_iterable($value)) {
            $values = [];
            foreach ($value as $potentialDto) {
                if ($potentialDto instanceof DataTransferObject) {
                    $potentialDto->setValidation(false);
                    $values[] = $potentialDto;
                }
            }

            return $values;
        } elseif ($value instanceof DataTransferObject) {
            $value->setValidation(false);
        }

        return $value;
    }

    public function reset()
    {
        $this->value = null;
        $this->initialized = false;
        $this->initViolations();
    }

    public function isInitialized()
    {
        return $this->initialized;
    }

    public function validate($value): ?ConstraintViolationListInterface
    {
        $constraints = $this->data->getConstraints();

        $violations = (new ValidatorBuilder())->getValidator()->validate($value, $constraints);

        if (! $this->isInitialized() && ! $this->data->isOptional()) {
            $violations->add(new PropertyRequiredViolation());
        }
        if (! $this->data->getType()->isValid($value)) {
            $violations->add(new InvalidPropertyTypeViolation($this->data->getType()->getTypes()));
        }

        return $violations;
    }

    public function isValid()
    {
        return $this->violations === null || $this->violations->count() <= 0;
    }

    /**
     * @return ConstraintViolationListInterface|null
     */
    public function getViolations(): ?ConstraintViolationListInterface
    {
        return $this->violations;
    }

    public function setViolations(?ConstraintViolationListInterface $violationList): ?ConstraintViolationListInterface
    {
        return $this->violations = $violationList;
    }

    public function isImmutable()
    {
        return $this->data->isImmutable();
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getName()
    {
        return $this->data->getName();
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     */
    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }
}
