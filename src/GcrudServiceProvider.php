<?php

namespace DjFerreira\Gcrud;

use Illuminate\Support\ServiceProvider;

class GcrudServiceProvider extends ServiceProvider
{
    protected $commands = [
        'DjFerreira\Gcrud\Commands\ModelGenerator',
        'DjFerreira\Gcrud\Commands\ControllerGenerator',
        'DjFerreira\Gcrud\Commands\ResourceGenerator',
        'DjFerreira\Gcrud\Commands\BOGenerator',
        'DjFerreira\Gcrud\Commands\RepositoryGenerator',
        'DjFerreira\Gcrud\Commands\RequestGenerator',
        'DjFerreira\Gcrud\Commands\TraitGenerator',
        'DjFerreira\Gcrud\Commands\CRUDGenerator',
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__ . '/stubs/' => base_path('resources/stubs/'),
        ], 'gcrud-stubs');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands($this->commands);
    }
}
