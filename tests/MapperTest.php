<?php

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use Sinmiloluwa\LaravelDtoMapper\Attributes\Cast;
use Sinmiloluwa\LaravelDtoMapper\Attributes\MapFrom;
use Sinmiloluwa\LaravelDtoMapper\Exceptions\DtoMappingException;
use Sinmiloluwa\LaravelDtoMapper\Traits\MapsAttributes;

class MapperTest extends \Orchestra\Testbench\TestCase
{
    #[Test]
    public function it_maps_object_to_dto()
    {
        $user = new stdClass();
        $user->name = 'John';
        $user->age = '30';
        $user->active = '1';

        $dto = ExampleDTO::from($user);

        $this->assertEquals('John', $dto->name);
        $this->assertSame(30, $dto->age);
        $this->assertTrue($dto->active);
    }

    #[Test]
    public function it_maps_array_to_dto()
    {
        $dto = ExampleDTO::from([
            'name' => 'Jane',
            'age' => '25',
            'active' => false,
        ]);

        $this->assertEquals('Jane', $dto->name);
        $this->assertSame(25, $dto->age);
        $this->assertFalse($dto->active);
    }

    #[Test]
    public function it_throws_when_a_required_key_is_missing()
    {
        $this->expectException(DtoMappingException::class);
        $this->expectExceptionMessage('Property [name] on [ExampleDTO] is missing from the source and is not nullable.');

        ExampleDTO::from(['age' => 1, 'active' => true]);
    }

    #[Test]
    public function it_throws_instead_of_casting_null_to_a_non_nullable_property()
    {
        $this->expectException(DtoMappingException::class);
        $this->expectExceptionMessage('Property [age] on [ExampleDTO] is null and is not nullable.');

        ExampleDTO::from(['name' => 'Jane', 'age' => null, 'active' => true]);
    }

    #[Test]
    public function it_keeps_defaults_and_nulls_for_missing_optional_keys()
    {
        $dto = OptionalDTO::from(['name' => 'Jane']);

        $this->assertSame('Jane', $dto->name);
        $this->assertNull($dto->nickname);
        $this->assertSame('user', $dto->role);
    }

    #[Test]
    public function it_keeps_null_for_nullable_properties_instead_of_casting()
    {
        $dto = OptionalDTO::from(['name' => 'Jane', 'score' => null]);

        $this->assertNull($dto->score);
    }

    #[Test]
    public function it_maps_eloquent_model_attributes()
    {
        $model = new ExampleModel(['name' => 'Ada', 'nickname' => null, 'score' => '9']);

        $dto = OptionalDTO::from($model);

        $this->assertSame('Ada', $dto->name);
        $this->assertNull($dto->nickname);
        $this->assertSame(9, $dto->score);
    }

    #[Test]
    public function it_ignores_static_and_non_public_properties()
    {
        $dto = OptionalDTO::from(['name' => 'Jane', 'secret' => 'leak', 'registry' => 'leak']);

        $this->assertNull(OptionalDTO::$registry);
        $this->assertSame('hidden', (fn () => $this->secret)->call($dto));
    }

    #[Test]
    public function it_rejects_a_source_that_does_not_match_map_from()
    {
        $this->expectException(DtoMappingException::class);
        $this->expectExceptionMessage('DTO [ExampleDTO] expects a source of type [stdClass], [ExampleModel] given.');

        ExampleDTO::from(new ExampleModel(['name' => 'Ada', 'age' => 1, 'active' => true]));
    }
}

#[MapFrom(stdClass::class)]
class ExampleDTO
{
    use MapsAttributes;

    public string $name;

    #[Cast('int')]
    public int $age;

    #[Cast('bool')]
    public bool $active;
}

class OptionalDTO
{
    use MapsAttributes;

    public static ?string $registry = null;

    public string $name;

    public ?string $nickname;

    public string $role = 'user';

    #[Cast('int')]
    public ?int $score;

    private string $secret = 'hidden';
}

class ExampleModel extends Model
{
    protected $guarded = [];
}
