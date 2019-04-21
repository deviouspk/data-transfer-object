<?php

declare(strict_types=1);

namespace Larapie\DataTransferObject\Tests;

use Jchook\AssertThrows\AssertThrows;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use AssertThrows;

    protected function markTestSucceeded()
    {
        $this->assertTrue(true);
    }
}
