<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\Annotations\Immutable;
use Larapie\DataTransferObject\DataTransferObject;

class ImmutablePropertyDto extends DataTransferObject
{
    /**
     * @Immutable
     * @var string
     */
    public $immutableProperty;

    /** @var string */
    public $mutableProperty;
}
