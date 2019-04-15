<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\DataTransferObject;

class NestedParentOfMany extends DataTransferObject
{
    /** @var \Larapie\DataTransferObject\Tests\TestClasses\NestedChild[] */
    public $children;

    /** @var string */
    public $name;
}
