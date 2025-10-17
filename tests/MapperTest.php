<?php

use Sinmiloluwa\LaravelDtoMapper\Attributes\Cast;
use Sinmiloluwa\LaravelDtoMapper\Attributes\MapFrom;
use Sinmiloluwa\LaravelDtoMapper\Traits\MapsAttributes;

class MapperTest extends \Orchestra\Testbench\TestCase
{
    /** @test */
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

    /** @test */
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