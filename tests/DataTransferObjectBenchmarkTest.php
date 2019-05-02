<?php

namespace Larapie\DataTransferObject\Tests;

use Larapie\DataTransferObject\Tests\TestClasses\NestedParent;

class DataTransferObjectBenchmarkTest extends TestCase
{
    /** @test */
    public function benchmark_nested_validation()
    {
        $start = microtime(true);

        for ($i = 0; $i < 10000; $i++) {
            $dto = new NestedParent([
                'name' => 'foo',
                'child' => [
                    'name' => 'bar',
                ],
            ]);
            $dto->validate();
        }

        echo 'Benchmark took: '.round($time_elapsed_secs = microtime(true) - $start, 2).' sec';

        $this->assertTrue(true);
    }
}
