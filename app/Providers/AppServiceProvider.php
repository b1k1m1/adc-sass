<?php

namespace App\Providers;

use App\Models\CompanyInfo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;

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
    /**
    */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Store successful login information
        |--------------------------------------------------------------------------
        */
        Event::listen(Login::class, function (Login $event) {

            $user = $event->user;

            if (! $user instanceof User) {
                return;
            }

            // Move the previous current login into last login
            $user->last_login_time = $user->current_login_time;

            // Store the new current login date/time and IP address
            $user->current_login_time = now();
            $user->login_ip_address = request()->ip();

            // Save without triggering additional model events
            //$user->saveQuietly();
            $user->save();
        });

        /*
        |--------------------------------------------------------------------------
        | Avoid company table queries during migrations / Artisan commands
        |--------------------------------------------------------------------------
        */
        if (app()->runningInConsole()) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Share company information with all views
        |--------------------------------------------------------------------------
        */
        try {
            $company = CompanyInfo::first();

            if ($company) {
                // Put the whole row into config('company.*')
                config([
                    'company' => $company->toArray(),
                ]);

                // Share $company with all views
                View::share('company', $company);
            }
        } catch (\Exception $e) {
            // Ignore when the company table does not exist yet
        }

        /*
        |--------------------------------------------------------------------------
        | Share authenticated user with all views
        |--------------------------------------------------------------------------
        */
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $view->with('authUser', Auth::user());
            }
        });
    }
}

