<?php

namespace Larapie\DataTransferObject\Traits;


trait Immutable
{
    public function &__get($name)
    {
        $value = $this->properties[$name]->getValue();
        return $value;
    }

    protected function determineImmutability()
    {
        $this->setImmutable();
    }
}