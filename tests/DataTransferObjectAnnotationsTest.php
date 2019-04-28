<?php

namespace Larapie\DataTransferObject\Tests;

use Larapie\DataTransferObject\Tests\TestClasses\ImmutableDtoWithOptionalProperty;
use Larapie\DataTransferObject\Tests\TestClasses\OptionalPropertyDto;

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
}
