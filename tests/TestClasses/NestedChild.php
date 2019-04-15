<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\DataTransferObject;

class NestedChild extends DataTransferObject
{
    /** @var string */
    public $name;
}
