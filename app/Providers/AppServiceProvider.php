<?php

namespace App\Providers;

use App\Models\Offer;
use App\Models\Course;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\ContactMessage;
use App\Observers\SectionObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use App\Policies\ContactMessagePolicy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        //just for testing 
        \DB::listen(function ($query) {
            \Log::info($query->sql, $query->bindings);
        });
        
        Section::observe(SectionObserver::class);
    }
}
