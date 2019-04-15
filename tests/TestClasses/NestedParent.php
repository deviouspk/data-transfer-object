<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\DataTransferObject;

class NestedParent extends DataTransferObject
{
    /** @var \Larapie\DataTransferObject\Tests\TestClasses\NestedChild */
    public $child;

    /** @var string */
    public $name;
}
