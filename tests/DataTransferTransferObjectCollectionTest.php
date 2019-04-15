<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests;

use Larapie\DataTransferObject\DataTransferObjectCollection;
use Larapie\DataTransferObject\Tests\TestClasses\NestedParent;
use Larapie\DataTransferObject\Tests\TestClasses\NestedChildCollection;
use Larapie\DataTransferObject\Tests\TestClasses\NestedParentCollection;
use Larapie\DataTransferObject\Tests\TestClasses\TestDataTransferObject;

class DataTransferObjectCollectionTest extends TestCase
{
    /** @test */
    public function it_can_hold_value_objects_of_a_certain_type()
    {
        $objects = [
            new TestDataTransferObject(['testProperty' => 1]),
            new TestDataTransferObject(['testProperty' => 2]),
            new TestDataTransferObject(['testProperty' => 3]),
        ];

        $list = new class($objects) extends DataTransferObjectCollection {
        };

        $this->assertCount(3, $list);
    }

    /** @test */
    public function to_array_also_recursively_casts_dtos_to_array()
    {
        $collection = new NestedChildCollection();

        $data = [
            'name' => 'parent',
            'child' => [
                'name' => 'child',
            ],
        ];

        $parent = new NestedParent($data);

        $collection[] = $parent;

        $array = $collection->toArray();

        $this->assertEquals([
            0 => $data,
        ], $array);
    }

    /** @test */
    public function to_array_also_recursively_casts_dto_collections_to_array()
    {
        $collection = new NestedParentCollection();

        $collection[] = new NestedChildCollection();

        $array = $collection->toArray();

        $this->assertEquals([
            0 => [],
        ], $array);
    }
}
