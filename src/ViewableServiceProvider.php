<?php

namespace BalajiDharma\LaravelViewable;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class ViewableServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/viewable.php', 'viewable'
        );
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'viewable');

        if (app()->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/viewable.php' => config_path('viewable.php'),
            ], ['config', 'viewable-config', 'admin-core', 'admin-core-config']);
            $this->publishes([
                __DIR__.'/../database/migrations/create_viewable_tables.php.stub' => $this->getMigrationFileName('create_comment_tables.php'),
            ], ['migrations', 'viewable-migrations', 'laravel-viewable-migrations', 'admin-core', 'admin-core-migrations']);
        }
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     */
    protected function getMigrationFileName($migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $migrationFileName) {
                return $filesystem->glob($path.'*_'.$migrationFileName);
            })
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
