<?php

namespace Larapie\DataTransferObject\Contracts;

interface DtoContract
{
    public function all(): array;

    public function only(string ...$keys): DtoContract;

    public function except(string ...$keys): DtoContract;

    public function with(string $key, $value): DtoContract;

    public function override(string $key, $value): DtoContract;

    public function toArray(): array;

    public function isImmutable(): bool;

    public function setImmutable(bool $immutable): void ;
}
