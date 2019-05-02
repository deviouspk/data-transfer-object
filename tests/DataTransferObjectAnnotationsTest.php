<?php

namespace Larapie\DataTransferObject\Tests;

use Larapie\DataTransferObject\Annotations\Inherit;
use Larapie\DataTransferObject\Exceptions\ValidatorException;
use Larapie\DataTransferObject\Tests\TestClasses\ImmutableDtoWithOptionalProperty;
use Larapie\DataTransferObject\Tests\TestClasses\OptionalPropertyDto;
use Larapie\DataTransferObject\Tests\TestClasses\ValidateablePropertyDto;
use Larapie\DataTransferObject\Violations\InvalidPropertyTypeViolation;

class DataTransferObjectAnnotationsTest extends TestCase
{
    /** @test */
    public function optional_property_input_is_not_required()
    {
        $dto = new OptionalPropertyDto([
        ]);
        $dto->validate();

        $this->assertEmpty($dto->toArray());
    }

    /** @test */
    public function optional_values_are_not_required()
    {
        $dto = new ImmutableDtoWithOptionalProperty([
            'name' => 'test',
        ]);
        $data = $dto->toArray();
        $this->assertArrayNotHasKey('address', $data);
    }

    /** @test */
    public function optional_values_are_outputted()
    {
        $dto = new ImmutableDtoWithOptionalProperty([
            'name' => 'test',
            'address' => '1st street',
        ]);
        $data = $dto->toArray();
        $this->assertArrayHasKey('address', $data);
    }

    /** @test */
    public function optional_annotation_property_can_be_inputted()
    {
        $dto = new OptionalPropertyDto([
            'name' => 'test',
        ]);
        $this->assertEquals([
            'name' => 'test',
        ], $dto->toArray());
    }

    /** @test */
    public function dto_inherits_parent_property_constraints()
    {
        $dto = new class(['name' => 54545645]) extends ValidateablePropertyDto
        {
            /** @Inherit() */
            public $name;
        };

        $this->assertThrows(ValidatorException::class,
            function () use ($dto) {
                $dto->validate();
            },
            function (ValidatorException $exception) {
                $this->assertTrue($exception->propertyViolationExists('name', InvalidPropertyTypeViolation::class));
            });
    }
}
