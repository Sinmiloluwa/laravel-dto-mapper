<?php

namespace Sinmiloluwa\LaravelDtoMapper\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Cast
{
    public function __construct(public string $type) {}
}
