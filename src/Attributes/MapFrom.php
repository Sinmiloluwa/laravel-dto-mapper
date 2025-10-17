<?php

namespace Sinmiloluwa\LaravelDtoMapper\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class MapFrom
{
    public function __construct(public string $source) {}
}
