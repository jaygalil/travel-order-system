<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\TravelOrderApproval;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Share pending approval count with all views
        View::composer('layouts.app', function ($view) {
            $pendingApprovalCount = 0;
            
            if (Auth::check()) {
                $user = Auth::user();
                $pendingApprovalCount = TravelOrderApproval::where('approver_user_id', $user->id)
                    ->where('status', 'pending')
                    ->count();
            }
            
            $view->with('pendingApprovalCount', $pendingApprovalCount);
        });
    }
}
