<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadModuleRoutes();
    }

    /**
     * Load routes from modules
     */
    protected function loadModuleRoutes(): void
    {
        $modules = [
            'Auth' => '', // No prefix for auth routes
            'User' => 'user',
            'Product' => 'product'
        ];

        foreach ($modules as $module => $prefix) {
            $routePath = app_path("Modules/{$module}/routes.php");
            if (file_exists($routePath)) {
                Route::group([
                    'prefix' => $prefix,
                    'namespace' => "App\\Modules\\{$module}\\Controllers"
                ], function () use ($routePath) {
                    require $routePath;
                });
            }
        }
    }
}