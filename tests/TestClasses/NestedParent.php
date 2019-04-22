<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\DataTransferObject;

class NestedParent extends DataTransferObject
{
    /** @var NestedChild */
    public $child;

    /** @var string */
    public $name;
}
