<?php

namespace Larapie\DataTransferObject\Tests;

use Larapie\DataTransferObject\Property;
use Doctrine\Common\Annotations\AnnotationReader;
use Larapie\DataTransferObject\Tests\TestClasses\OptionalPropertyDto;

class AnnotationsDataTransferObjectTest extends TestCase
{

    /** @test */
    public function optional_annotation_works()
    {

        $dto = new OptionalPropertyDto([
        ]);
        $dto->validate();

        $this->assertEmpty($dto->toArray());
    }
}
