<?php

namespace App\Providers;

use App\Models\Task;
use App\Models\User;
use App\Models\Gate as GateModel;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use App\Policies\GatePolicy;
use Illuminate\Support\Facades\Gate;
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
        // Register policies
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(GateModel::class, GatePolicy::class);

        // Define additional gates for role-based access
        Gate::define('manage-tasks', function (User $user) {
            return $user->isDcfm();
        });

        Gate::define('view-all-tasks', function (User $user) {
            return $user->canViewAllTasks();
        });

        Gate::define('manage-users', function (User $user) {
            return $user->isDcfm();
        });

        Gate::define('manage-gates', function (User $user) {
            return $user->isDcfm();
        });
    }
}
