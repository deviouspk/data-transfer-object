<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\Annotations\Optional;
use Larapie\DataTransferObject\Traits\MakeImmutable;
use Larapie\DataTransferObject\DataTransferObject;

class ImmutableDtoWithOptionalProperty extends DataTransferObject
{
    use MakeImmutable;

    /** @var string */
    public $name;

    /**
     * @Optional
     * @var string
     */
    public $address;
}
