<?php

namespace App\Providers;

use App\Models\FormField;
use App\Models\PqrsfSubmission;
use App\Models\Sede;
use App\Models\SedeComplaintRecipient;
use App\Models\SedeRecipient;
use App\Models\SubmissionLog;
use App\Models\User;
use App\Policies\AdminOnlyPolicy;
use App\Policies\PqrsfSubmissionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        Gate::define('access-admin', fn (User $user): bool => $user->isAdmin());
        Gate::define('access-reports', fn (User $user): bool => $user->canAccessReadOnlyPanel());

        Gate::policy(PqrsfSubmission::class, PqrsfSubmissionPolicy::class);

        foreach ([
            FormField::class,
            Sede::class,
            SedeComplaintRecipient::class,
            SedeRecipient::class,
            SubmissionLog::class,
            User::class,
        ] as $model) {
            Gate::policy($model, AdminOnlyPolicy::class);
        }

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
