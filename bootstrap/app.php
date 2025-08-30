<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'                  => \App\Http\Middleware\CheckRole::class,
            'admission.permissions' => \App\Http\Middleware\CheckAdmissionPermissions::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        // تنفيذ تنظيف يومي
        $schedule->command('admissions:scheduled-tasks')
            ->daily()
            ->at('02:00');

        // إحصائيات أسبوعية
        $schedule->command('admissions:manage stats')
            ->weekly()
            ->mondays()
            ->at('08:00');
    })
    ->create();
