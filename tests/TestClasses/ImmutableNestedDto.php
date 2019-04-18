<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\Traits\MakeImmutable;
use Larapie\DataTransferObject\DataTransferObject;

class ImmutableNestedDto extends DataTransferObject
{
    use MakeImmutable;

    /** @var string */
    public $name;

    /** @var \Larapie\DataTransferObject\Tests\TestClasses\NestedChild[]|array $child */
    public $children;
}
