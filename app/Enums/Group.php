<?php

namespace App\Enums;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
class Group
{
    public function __construct(public string $group) {}

    public function getName(): string
    {
        return $this->group;
    }
}
