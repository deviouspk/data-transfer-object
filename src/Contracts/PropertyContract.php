<?php

namespace Larapie\DataTransferObject\Contracts;

interface PropertyContract
{
    public function getDefault();

    public function isVisible(): bool;

    public function setVisible(bool $bool);

    public function getValue();

    public function getName(): string;

    public function getTypes(): array;

    public function getFqn(): string;

    public function nullable(): bool;

    public function setNullable(bool $bool): void;

    public function immutable(): bool;

    public function setImmutable(bool $immutable): void;

    public function isOptional(): bool;

    public function getConstraints(): array;
}
