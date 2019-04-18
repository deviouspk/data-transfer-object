<?php

namespace Larapie\DataTransferObject\Tests;

use Doctrine\Common\Annotations\AnnotationReader;
use Larapie\DataTransferObject\Property;
use Larapie\DataTransferObject\Tests\TestClasses\OptionalPropertyDto;

class AnnotationsDataTransferObjectTest extends TestCase
{
    /** @test */
    public function cached_reader_is_faster()
    {
        $start_time = microtime(true);
        for ($i = 0; $i < 100; $i++) {
            $dto = new OptionalPropertyDto([
                "name" => "test"
            ]);
            $dto->toArray();
        }
        $end_time = microtime(true);

        $cachedExecutionTime = ($end_time - $start_time);

        Property::setReader(new AnnotationReader());

        $start_time = microtime(true);
        for ($i = 0; $i < 100; $i++) {
            $dto = new OptionalPropertyDto([
                "name" => "test"
            ]);
            $dto->toArray();
        }
        $end_time = microtime(true);

        $normalExecutionTime = ($end_time - $start_time);

        $this->assertGreaterThan($cachedExecutionTime, $normalExecutionTime);
    }

    /** @test */
    public function optional_annotation_works()
    {
        $dto = new OptionalPropertyDto([
        ]);
        $this->assertEmpty($dto->toArray());
    }
}
