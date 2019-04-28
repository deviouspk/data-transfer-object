<?php

namespace Larapie\DataTransferObject\Tests;

use Larapie\DataTransferObject\Contracts\DisableValidation;
use Larapie\DataTransferObject\Exceptions\ValidatorException;
use Larapie\DataTransferObject\Tests\TestClasses\NestedParent;
use Larapie\DataTransferObject\Tests\TestClasses\ValidateablePropertyDto;
use Larapie\DataTransferObject\Violations\PropertyRequiredViolation;

class DataTransferObjectValidationTest extends TestCase
{
    /** @test */
    public function validate_string_property()
    {
        $this->expectException(ValidatorException::class);
        $dto = new ValidateablePropertyDto([
            'name' => 'zefqsdfqsdfqsdfqsdf',
        ]);
        $dto->validate();
    }

    /** @test */
    public function test_disable_validation()
    {
        $dto = new class([]) extends ValidateablePropertyDto
        {
        };
        $dto->setValidation(false);
        $this->assertEmpty($dto->toArray());
    }

    /** @test */
    public function valid_nested_validation_works()
    {
        $dto = new NestedParent([
            "name" => "foo",
            "child" => [
                "name" => "bar"
            ]
        ]);

        $dto->validate();

        $this->assertEquals($dto->toArray(), [
            "name" => "foo",
            "child" => [
                "name" => "bar"
            ]
        ]);
    }

    /** @test */
    public function invalid_nested_validation_throws_exception()
    {
        $dto = new NestedParent([
            "name" => "foo",
            "child" => [

            ]
        ]);

        $this->assertThrows(ValidatorException::class,
            function () use ($dto) {
                $dto->validate();
            },
            function (ValidatorException $exception) {
                $this->assertTrue($exception->propertyViolationExists('child.name', PropertyRequiredViolation::class));
            });
    }
}
