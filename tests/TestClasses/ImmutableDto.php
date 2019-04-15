<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\DataTransferObject;
use Larapie\DataTransferObject\Contracts\Immutable;

class ImmutableDto extends DataTransferObject implements Immutable
{
    /** @var string */
    public $name;
}
