<?php

namespace App\Providers;

use App\Models\ActiveSession;
use App\Models\LoginHistory;
use App\Models\Report;
use App\Models\TaskPreset;
use App\Models\User;
use App\Policies\ActiveSessionPolicy;
use App\Policies\LoginHistoryPolicy;
use App\Policies\ReportPolicy;
use App\Policies\TaskPresetPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
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
        Gate::policy(TaskPreset::class, TaskPresetPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(LoginHistory::class, LoginHistoryPolicy::class);
        Gate::policy(ActiveSession::class, ActiveSessionPolicy::class);
        Gate::define('access-admin-dashboard', fn (User $user): bool => $user->role === 'admin');

        Vite::prefetch(concurrency: 3);
    }
}
