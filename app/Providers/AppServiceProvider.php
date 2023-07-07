<?php

namespace App\Providers;

use App\Services\MerchantService;
use App\Interfaces\MerchantServiceInterface;
use App\Services\AffiliateService;
use App\Services\ApiService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton(ApiService::class, function($app){
            return new ApiService();
        });
        
        $this->app->bind(AffiliateService::class, function($app){
            // return new AffiliateService($app->make(ApiService::class));
            return new AffiliateService(new ApiService());

        });
    }

    // public $singletons = [
    //    AffiliateService::class => new AffiliateService(new ApiService())
    // ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
