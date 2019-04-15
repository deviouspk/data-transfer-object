<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\DataTransferObject;
use Larapie\DataTransferObject\Contracts\Immutable;

class ImmutablePropertyDto extends DataTransferObject
{
    /** @var string|Immutable */
    public $immutableProperty;

    /** @var string */
    public $mutableProperty;
}
