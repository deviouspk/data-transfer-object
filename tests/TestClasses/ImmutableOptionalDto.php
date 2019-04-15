<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\Traits\Immutable;
use Larapie\DataTransferObject\Contracts\optional;
use Larapie\DataTransferObject\DataTransferObject;

class ImmutableOptionalDto extends DataTransferObject
{
    use Immutable;

    /** @var string */
    public $name;

    /** @var string|optional */
    public $address;
}
