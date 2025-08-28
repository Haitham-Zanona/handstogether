<?php
namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

// Define gates for different roles
        Gate::define('admin-access', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('teacher-access', function ($user) {
            return $user->role === 'teacher';
        });

        Gate::define('parent-access', function ($user) {
            return $user->role === 'parent';
        });

        Gate::define('student-access', function ($user) {
            return $user->role === 'student';
        });

// Define specific permissions
        Gate::define('manage-admissions', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('mark-attendance', function ($user, $lecture = null) {
            if ($user->role === 'admin') {
                return true;
            }

            if ($user->role === 'teacher' && $lecture) {
                return $user->teacher->id === $lecture->teacher_id;
            }
            return false;
        });

        Gate::define('view-payments', function ($user, $student = null) {
            if ($user->role === 'admin') {
                return true;
            }

            if ($user->role === 'parent' && $student) {
                return $user->id === $student->parent_id;
            }
            if ($user->role === 'student' && $student) {
                return $user->id === $student->user_id;
            }
            return false;
        });

    }
}
