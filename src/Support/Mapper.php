<?php

namespace Sinmiloluwa\LaravelDtoMapper\Support;

use ReflectionClass;
use ReflectionProperty;
use Sinmiloluwa\LaravelDtoMapper\Exceptions\DtoMappingException;
use Sinmiloluwa\LaravelDtoMapper\Attributes\Cast;

class Mapper
{
    public static function map(object|array $source, string $dtoClass)
    {
        if (!class_exists($dtoClass)) {
            throw new DtoMappingException("DTO class [$dtoClass] not found.");
        }

        $dto = new $dtoClass();
        $reflection = new ReflectionClass($dtoClass);

        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();
            $value = self::getValue($source, $name);
            $value = self::castValue($property, $value);
            $dto->$name = $value;
        }

        return $dto;
    }

    protected static function getValue(object|array $source, string $key)
    {
        if (is_array($source)) {
            return $source[$key] ?? null;
        }

        return $source->$key ?? null;
    }

    protected static function castValue(ReflectionProperty $property, $value)
    {
        $cast = collect($property->getAttributes(Cast::class))->first();
        if (!$cast) return $value;

        $type = $cast->newInstance()->type;

        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => (bool) $value,
            'string' => (string) $value,
            default => $value,
        };
    }
}
