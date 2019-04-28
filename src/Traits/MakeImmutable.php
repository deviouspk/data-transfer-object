<?php

namespace Larapie\DataTransferObject\Traits;

trait MakeImmutable
{
    protected function determineImmutability()
    {
        $this->setImmutable(true);
    }
}
