<?php

namespace Sinmiloluwa\LaravelDtoMapper\Support;

use ReflectionClass;
use ReflectionProperty;
use Sinmiloluwa\LaravelDtoMapper\Exceptions\DtoMappingException;
use Sinmiloluwa\LaravelDtoMapper\Attributes\Cast;
use Sinmiloluwa\LaravelDtoMapper\Attributes\MapFrom;

class Mapper
{
    public static function map(object|array $source, string $dtoClass): object
    {
        if (!class_exists($dtoClass)) {
            throw new DtoMappingException("DTO class [$dtoClass] not found.");
        }

        $reflection = new ReflectionClass($dtoClass);
        self::guardSource($reflection, $source);

        $dto = new $dtoClass();

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $name = $property->getName();

            if (!self::hasValue($source, $name)) {
                // Keep declared defaults; otherwise only nullable properties may be left empty.
                if ($property->hasDefaultValue()) {
                    continue;
                }

                $dto->$name = self::nullOrFail($property, $dtoClass, 'is missing from the source');
                continue;
            }

            $value = self::getValue($source, $name);

            $dto->$name = $value === null
                ? self::nullOrFail($property, $dtoClass, 'is null')
                : self::castValue($property, $value);
        }

        return $dto;
    }

    protected static function guardSource(ReflectionClass $reflection, object|array $source): void
    {
        $mapFrom = $reflection->getAttributes(MapFrom::class)[0] ?? null;

        if (!$mapFrom || is_array($source)) {
            return;
        }

        $expected = $mapFrom->newInstance()->source;

        if (!$source instanceof $expected) {
            throw new DtoMappingException(sprintf(
                'DTO [%s] expects a source of type [%s], [%s] given.',
                $reflection->getName(),
                $expected,
                get_class($source),
            ));
        }
    }

    protected static function hasValue(object|array $source, string $key): bool
    {
        if (is_array($source)) {
            return array_key_exists($key, $source);
        }

        // isset() sees magic attributes (e.g. Eloquent); property_exists() sees declared properties set to null.
        return isset($source->$key) || property_exists($source, $key);
    }

    protected static function getValue(object|array $source, string $key)
    {
        return is_array($source) ? $source[$key] : $source->$key;
    }

    protected static function nullOrFail(ReflectionProperty $property, string $dtoClass, string $reason)
    {
        $type = $property->getType();

        if ($type === null || $type->allowsNull()) {
            return null;
        }

        throw new DtoMappingException(
            "Property [{$property->getName()}] on [$dtoClass] $reason and is not nullable."
        );
    }

    protected static function castValue(ReflectionProperty $property, $value)
    {
        $cast = $property->getAttributes(Cast::class)[0] ?? null;
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
