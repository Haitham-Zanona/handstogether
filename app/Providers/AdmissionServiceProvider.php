<?php
namespace App\Providers;

use App\Services\AdmissionService;
use Illuminate\Support\ServiceProvider;

class AdmissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AdmissionService::class, function ($app) {
            return new AdmissionService();
        });

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
