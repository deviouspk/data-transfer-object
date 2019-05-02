<?php

namespace Larapie\DataTransferObject\Traits;

trait MakeImmutable
{
    protected function boot(array $parameters): void
    {
        parent::boot($parameters);
        $this->setImmutable(true);
    }
}
