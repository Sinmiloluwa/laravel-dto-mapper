<?php

namespace Sinmiloluwa\LaravelDtoMapper;

use Illuminate\Support\ServiceProvider;

class DtoServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/dto-mapper.php' => config_path('dto-mapper.php'),
            ], 'config');
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/dto-mapper.php', 'dto-mapper');
    }
}
