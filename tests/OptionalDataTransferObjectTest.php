<?php

namespace Larapie\DataTransferObject\Tests;

use Larapie\DataTransferObject\Tests\TestClasses\ImmutableOptionalDto;

class OptionalDataTransferObjectTest extends TestCase
{
    /** @test */
    public function optional_values_are_not_required()
    {
        $dto = new ImmutableOptionalDto([
            'name' => 'test',
        ]);
        $data = $dto->toArray();
        $this->assertArrayNotHasKey('address', $data);
    }

    /** @test */
    public function optional_values_are_outputted()
    {
        $dto = new ImmutableOptionalDto([
            'name' => 'test',
            'address' => '1st street',
        ]);
        $data = $dto->toArray();
        $this->assertArrayHasKey('address', $data);
    }
}
