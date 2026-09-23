# Laravel DTO Mapper

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sinmiloluwa/laravel-dto-mapper.svg?style=flat-square)](https://packagist.org/packages/sinmiloluwa/laravel-dto-mapper)
[![Total Downloads](https://img.shields.io/packagist/dt/sinmiloluwa/laravel-dto-mapper.svg?style=flat-square)](https://packagist.org/packages/sinmiloluwa/laravel-dto-mapper)
[![Tests](https://github.com/sinmiloluwa/laravel-dto-mapper/actions/workflows/run-tests.yml/badge.svg)](https://github.com/sinmiloluwa/laravel-dto-mapper/actions)

> 🧩 Elegant, type-safe DTO mapping for Laravel models and arrays.

---

### 🚀  Features

- ✅ Map Eloquent models or arrays to typed DTOs
- 🎯 Supports PHP 8+ attributes (`#[MapFrom]`, `#[Cast]`)
- 🧠 Automatic type casting (`int`, `bool`, `float`, `string`)
- ⚡ Zero configuration – drop it in and use
- 🛡️ Clear errors for missing or null values instead of silent defaults

---

## 📦  Installation

```bash
composer require sinmiloluwa/laravel-dto-mapper
```

Requires PHP 8.1+ and Laravel 10 or 11.

---

## 🛠️  Usage

Add the `MapsAttributes` trait to a class with public typed properties, then call `from()` with an array, an Eloquent model or any object:

```php
use App\Models\User;
use Sinmiloluwa\LaravelDtoMapper\Attributes\Cast;
use Sinmiloluwa\LaravelDtoMapper\Attributes\MapFrom;
use Sinmiloluwa\LaravelDtoMapper\Traits\MapsAttributes;

#[MapFrom(User::class)]
class UserData
{
    use MapsAttributes;

    public string $name;

    public ?string $nickname;

    public string $role = 'member';

    #[Cast('int')]
    public int $age;

    #[Cast('bool')]
    public bool $active;
}

$dto = UserData::from(User::find(1));
$dto = UserData::from(['name' => 'Jane', 'age' => '25', 'active' => '1']);

$dto->toArray(); // ['name' => 'Jane', 'nickname' => null, 'role' => 'member', 'age' => 25, 'active' => true]
```

Each public, non-static property is filled from the source key with the same name.

### `#[Cast]`

Converts the value before it is assigned. The supported types are `int`, `float`, `bool` and `string`. A `null` value is never cast.

### `#[MapFrom]`

Declares which class the DTO is built from. Passing an object of any other class throws a `DtoMappingException`. Arrays are always accepted.

### Missing and null values

| Source value | Property | Result |
|---|---|---|
| key missing | has a default (`= 'member'`) | default is kept |
| key missing or `null` | nullable (`?string`) | `null` |
| key missing or `null` | not nullable | `DtoMappingException` |

```php
use Sinmiloluwa\LaravelDtoMapper\Exceptions\DtoMappingException;

try {
    UserData::from(['age' => 30]);
} catch (DtoMappingException $e) {
    // Property [name] on [UserData] is missing from the source and is not nullable.
}
```

---

## 🧪  Testing

```bash
composer install
vendor/bin/phpunit
```

---

## 📄  License

MIT. See [LICENSE](LICENSE).
