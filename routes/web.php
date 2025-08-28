<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

// في routes/web.php
Route::get('/', [HomeController::class, 'index'])->name('home');

// Academy portals
Route::prefix('portal')->group(function () {
    Route::get('/admin', [AuthController::class, 'adminLogin'])->name('portal.admin');
    Route::get('/teacher', [AuthController::class, 'teacherLogin'])->name('portal.teacher');
    Route::get('/parent', [AuthController::class, 'parentLogin'])->name('portal.parent');
    Route::get('/student', [AuthController::class, 'studentLogin'])->name('portal.student');
});

// Protected routes with role middleware
Route::middleware(['auth', 'verified'])->group(function () {
    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/admissions', [AdminController::class, 'admissions'])->name('admissions');
        Route::get('/groups', [AdminController::class, 'groups'])->name('groups');
        Route::get('/attendance', [AdminController::class, 'attendance'])->name('attendance');
        Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    });

    // Teacher routes
    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');
        Route::get('/schedule', [TeacherController::class, 'schedule'])->name('schedule');
        Route::get('/students', [TeacherController::class, 'students'])->name('students');
        Route::get('/attendance', [TeacherController::class, 'attendance'])->name('attendance');
        Route::get('/reports', [TeacherController::class, 'reports'])->name('reports');
    });

    // Parent routes
    Route::middleware('role:parent')->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [ParentController::class, 'dashboard'])->name('dashboard');
        Route::get('/attendance', [ParentController::class, 'attendance'])->name('attendance');
        Route::get('/schedule', [ParentController::class, 'schedule'])->name('schedule');
        Route::get('/payments', [ParentController::class, 'payments'])->name('payments');
    });

    // Student routes
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/schedule', [StudentController::class, 'schedule'])->name('schedule');
        Route::get('/lectures', [StudentController::class, 'lectures'])->name('lectures');
        Route::get('/reports', [StudentController::class, 'reports'])->name('reports');
    });
});

Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
    Route::get('/recent', [NotificationController::class, 'getRecent'])->name('recent');
});

require __DIR__ . '/auth.php';
