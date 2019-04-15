<?php

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\DataTransferObjectCollection;

class NestedParentCollection extends DataTransferObjectCollection
{
    public function current(): NestedChildCollection
    {
        return parent::current();
    }
}
