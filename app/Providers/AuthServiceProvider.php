<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //管理者
        Gate::define('SYSTEM_ADMIN', function($user) {
          return ($user->role == 1);
        });

        //管理者、見積作成者
        Gate::define('QUOTE_MEMBER', function($user) {
          return ($user->role == 5);
        });

        //一般会員（見積依頼）
        Gate::define('GENERAL_MEMBER', function($user) {
          return ($user->role == 10);
        });
    }
}
