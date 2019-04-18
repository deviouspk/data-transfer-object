<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\Traits\MakeImmutable;
use Larapie\DataTransferObject\DataTransferObject;

class ImmutableDto extends DataTransferObject
{
    use MakeImmutable;

    /** @var string */
    public $name;
}
