<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use jeremykenedy\laravelusers\App\Http\Controllers\SolaWiUsersManagementController;

/**
 * <p>This provider is necessary since laravel-users is having a bug which disables using other table names than 'users'.
 * @see https://github.com/jeremykenedy/laravel-users/issues/76 <br>
 * As soon as this bug get fixed, this provider is not necessary anymore.
 * </p>
 */
class LaravelUsersServiceProvider extends ServiceProvider
{
    private $packageTag = 'laravelusers';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $basePath = __DIR__ . '/../../vendor/jeremykenedy/laravel-users/src';
        $this->loadRoutesFrom($basePath . '/routes/web.php');
        $this->loadViewsFrom($basePath . '/resources/views/', $this->packageTag);
        $this->mergeConfigFrom($basePath . '/config/' . $this->packageTag . '.php', $this->packageTag);
        $this->publishFiles();
        $this->app->make('jeremykenedy\laravelusers\App\Http\Controllers\UsersManagementController');
        $controllerClass = jeremykenedy\laravelusers\App\Http\Controllers\UsersManagementController\UsersManagementController::class;
        $this->app->singleton(
            $controllerClass,
            fn () => new SolaWiUsersManagementController()
        );
        $this->app->alias($controllerClass, 'laravelusers');
    }

    /**
     * Publish files for the package.
     *
     * @return void
     */
    private function publishFiles()
    {
        $publishTag = $this->packageTag;

        $this->publishes([
            __DIR__ . '/config/' . $this->packageTag . '.php' => base_path('config/' . $this->packageTag . '.php'),
        ], $publishTag);

        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/' . $this->packageTag),
        ], $publishTag);

        $this->publishes([
            __DIR__ . '/resources/lang' => resource_path('lang/vendor/' . $this->packageTag),
        ], $publishTag);
    }
}
