<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\Annotations\Optional;
use Larapie\DataTransferObject\DataTransferObject;

class OptionalPropertyDto extends DataTransferObject
{
    /**
     * @Optional
     * @var string $name
     */
    public $name;
}
