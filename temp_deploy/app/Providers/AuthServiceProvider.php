<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\TravelOrder' => 'App\Policies\TravelOrderPolicy',
        'App\Models\TravelOrderApproval' => 'App\Policies\TravelOrderApprovalPolicy',
        'App\Models\WorkflowTemplate' => 'App\Policies\WorkflowTemplatePolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
