<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\DataTransferObject;
use Larapie\DataTransferObject\Traits\Immutable;

class ImmutableDto extends DataTransferObject
{
    use Immutable;

    /** @var string */
    public $name;
}
