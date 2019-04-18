<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\DataTransferObject;
use Larapie\DataTransferObject\Annotations\Immutable;

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
