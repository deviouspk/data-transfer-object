<?php

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\DataTransferObjectCollection;

class NestedChildCollection extends DataTransferObjectCollection
{
    public function current(): NestedParent
    {
        return parent::current();
    }
}
