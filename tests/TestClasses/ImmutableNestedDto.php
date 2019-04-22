<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\DataTransferObject;
use Larapie\DataTransferObject\Traits\MakeImmutable;

class ImmutableNestedDto extends DataTransferObject
{
    use MakeImmutable;

    /** @var string */
    public $name;

    /** @var NestedChild[]|array $child */
    public $children;
}
