<?php

namespace Sinmiloluwa\LaravelDtoMapper\Traits;

use Sinmiloluwa\LaravelDtoMapper\Support\Mapper;

trait MapsAttributes
{
    public static function from(object|array $source): static
    {
        return Mapper::map($source, static::class);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
