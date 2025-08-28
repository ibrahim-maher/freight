<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Core\Services\FirestoreService;
use App\Modules\Auth\Services\FirebaseAuthService;
use App\Modules\Auth\Services\AuthService;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Only register Firebase services if environment is configured
        if (env('FIREBASE_PROJECT_ID') && env('FIREBASE_PRIVATE_KEY')) {
            $this->app->singleton(FirestoreService::class, function ($app) {
                return new FirestoreService();
            });

            $this->app->singleton(FirebaseAuthService::class, function ($app) {
                return new FirebaseAuthService();
            });

            $this->app->singleton(AuthService::class, function ($app) {
                return new AuthService(
                    $app->make(FirestoreService::class),
                    $app->make(FirebaseAuthService::class)
                );
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register middleware only if Firebase is configured
        if (env('FIREBASE_PROJECT_ID') && env('FIREBASE_PRIVATE_KEY')) {
            $this->app['router']->aliasMiddleware('firebase.auth', \App\Http\Middleware\FirebaseAuth::class);
        }
    }
}