<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests\TestClasses;

use Larapie\DataTransferObject\DataTransferObject;

class DefaultValueDto extends DataTransferObject
{
    /** @var string */
    public $foo = 'abc';

    /** @var bool */
    public $bar;
}
