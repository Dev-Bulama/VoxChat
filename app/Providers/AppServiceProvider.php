<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use App\Models\Community;
use App\Policies\CommunityPolicy;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Community::class => CommunityPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Gate::define('admin', fn($user) => $user->is_admin);
    }
}
