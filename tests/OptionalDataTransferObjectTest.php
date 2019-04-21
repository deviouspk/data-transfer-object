<?php

namespace Larapie\DataTransferObject\Tests;

use Larapie\DataTransferObject\Tests\TestClasses\OptionalPropertyDto;
use Larapie\DataTransferObject\Tests\TestClasses\ImmutableDtoWithOptionalProperty;

class OptionalDataTransferObjectTest extends TestCase
{
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
    public function test_optional_annotation()
    {
        $dto = new OptionalPropertyDto([
            'name' => 'test',
        ]);
        $dto->validate();
        $this->assertTrue(true);
    }
}
