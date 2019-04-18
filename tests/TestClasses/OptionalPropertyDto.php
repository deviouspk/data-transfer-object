<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\DataTransferObject;
use Larapie\DataTransferObject\Annotations\Optional;

class OptionalPropertyDto extends DataTransferObject
{
    /**
     * @Optional
     * @var string
     */
    public $name;
}
