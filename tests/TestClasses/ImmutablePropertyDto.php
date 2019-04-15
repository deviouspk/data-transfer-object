<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\DataTransferObject;
use Larapie\DataTransferObject\Contracts\immutable;

class ImmutablePropertyDto extends DataTransferObject
{
    /** @var string|immutable */
    public $immutableProperty;

    /** @var string */
    public $mutableProperty;
}
