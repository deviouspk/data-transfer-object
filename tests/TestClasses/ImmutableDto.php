<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\Traits\Immutable;
use Larapie\DataTransferObject\DataTransferObject;

class ImmutableDto extends DataTransferObject
{
    use Immutable;

    /** @var string */
    public $name;
}
