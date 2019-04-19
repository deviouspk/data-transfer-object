<?php

namespace Larapie\DataTransferObject\Tests;

use Larapie\DataTransferObject\Exceptions\ValidatorException;
use Larapie\DataTransferObject\Tests\TestClasses\ValidateablePropertyDto;

class ValidateableDataTransferObjectTest extends TestCase
{
    /** @test */
    public function validate_string_property()
    {
        $this->expectException(ValidatorException::class);
        new ValidateablePropertyDto([
            'name' => 's',
        ]);
    }

    /** @test */
    public function optional_property_does_not_require_validation()
    {
        $dto = new ValidateablePropertyDto([]);
        $this->assertEmpty($dto->toArray());
    }
}
