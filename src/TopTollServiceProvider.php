<?php

namespace alimianesa\TopToll;

use Illuminate\Support\ServiceProvider;

class TopTollServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'alimianesa');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'alimianesa');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/toptoll.php', 'toptoll');

        // Register the service the package provides.
        $this->app->singleton('toptoll', function ($app) {
            return new TopToll;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['toptoll'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/toptoll.php' => config_path('toptoll.php'),
        ], 'toptoll.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/alimianesa'),
        ], 'toptoll.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/alimianesa'),
        ], 'toptoll.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/alimianesa'),
        ], 'toptoll.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
