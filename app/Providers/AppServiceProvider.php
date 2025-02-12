<?php

namespace App\Providers;

//use App\Services\LogService;
//use App\Services\LogServiceInt;
//use Illuminate\Pagination\Paginator;
use App\Services\CommonService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //$this->app->singleton(LogServiceInt::class, LogService::class);
        $this->app->singleton(CommonService::class, function ($app){
            return new CommonService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        //Paginator::useBootstrapFive();
    }
}
