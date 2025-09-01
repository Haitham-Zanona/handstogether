<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AdmissionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Auth;
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

// Custom authentication route
Route::post('/login', [AuthController::class, 'authenticate'])->name('login');

// Protected routes with role middleware
Route::middleware(['auth', 'verified'])->group(function () {
    // Profile routes (accessible to all authenticated users)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // General dashboard route that redirects based on user role
    Route::get('/dashboard', function () {
        $user = Auth::user();
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'teacher':
                return redirect()->route('teacher.dashboard');
            case 'parent':
                return redirect()->route('parent.dashboard');
            case 'student':
                return redirect()->route('student.dashboard');
            default:
                return redirect()->route('home');
        }
    })->name('dashboard');

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // طلبات الانتساب - Routes الأساسية (Resource)
        Route::resource('admissions', AdmissionController::class);

        // طلبات الانتساب - Routes إضافية
        Route::controller(AdmissionController::class)->prefix('admissions')->name('admissions.')->group(function () {
            // موافقة ورفض الطلبات
            Route::patch('{admission}/approve', 'approve')->name('approve');
            Route::patch('{admission}/reject', 'reject')->name('reject');

            // إعادة تعيين حالة الطلب (للمشرفين فقط)
            Route::patch('{admission}/reset-status', 'resetStatus')
                ->name('reset-status')
                ->middleware('admission.permissions:reset_admission_status');

            // تحويل الطلب إلى طالب
            Route::post('{admission}/convert-to-student', 'convertToStudent')->name('convert-to-student');

            // طباعة بيانات طلب معين
            Route::get('{admission}/print', 'print')->name('print');

            // إرسال رسالة SMS
            Route::post('{admission}/send-sms', 'sendSMS')->name('send-sms');

            // معالجة متعددة للطلبات
            Route::post('bulk-action', 'bulkAction')->name('bulk-action');
        });

        Route::post('/admissions/check-application-number', [AdmissionController::class, 'checkApplicationNumber'])->name('admissions.check-application-number');

        Route::post('/admissions/check-name-duplication', [AdmissionController::class, 'checkNameDuplication'])->name('admissions.check-name-duplication');

        // طلبات الانتساب - Routes للبيانات والإحصائيات
        Route::prefix('admissions-data')->name('admissions.')->group(function () {
            // إحصائيات طلبات الانتساب
            Route::get('statistics', [AdmissionController::class, 'statistics'])->name('statistics');

            // تصدير البيانات
            Route::get('export', [AdmissionController::class, 'export'])->name('export');

            // البحث السريع
            Route::get('quick-search', [AdmissionController::class, 'quickSearch'])->name('quick-search');

            // التحقق من توفر رقم الهوية
            Route::post('check-id-availability', [AdmissionController::class, 'checkIdAvailability'])->name('check-id');
        });

        Route::get('/lectures/calendar-data', [AdminController::class, 'getCalendarData'])
            ->name('lectures.calendar-data');

        // Routes الأصلية الأخرى
        Route::get('/groups', [AdminController::class, 'groups'])->name('groups');
        Route::post('/groups', [AdminController::class, 'storeGroup'])->name('groups.store');
        Route::put('/groups/{group}', [AdminController::class, 'updateGroup'])->name('groups.update');
        Route::delete('/groups/{group}', [AdminController::class, 'destroyGroup'])->name('groups.destroy');
        Route::get('/attendance', [AdminController::class, 'attendance'])->name('attendance');
        Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/clear-data', [AdminController::class, 'clearData'])->name('settings.clear-data');
        Route::post('/settings/reset-system', [AdminController::class, 'resetSystem'])->name('settings.reset-system');
    });

    // Public pages
    Route::get('contact', function () {
        return view('public.contact');
    })->name('contact');
    Route::post('contact', [HomeController::class, 'sendContact'])->name('contact.send');
    Route::get('about', function () {
        return view('public.about');
    })->name('about');

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

// Notification routes
Route::middleware(['auth'])->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
    Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
    Route::get('/recent', [NotificationController::class, 'getRecent'])->name('recent');
});

// API routes للاستخدام مع AJAX (اختياري)
Route::prefix('api/admin')->name('api.admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::controller(AdmissionController::class)->prefix('admissions')->name('admissions.')->group(function () {
        // البحث السريع
        Route::get('search', 'quickSearch')->name('search');

        // إحصائيات سريعة
        Route::get('stats', 'statistics')->name('stats');

        // تحديث حالة متعددة
        Route::patch('bulk-update', 'bulkAction')->name('bulk-update');

        // حذف متعدد
        Route::delete('bulk-delete', 'bulkAction')->name('bulk-delete');

        // التحقق من رقم الهوية
        Route::post('validate-id', 'checkIdAvailability')->name('validate-id');
    });
});

require __DIR__ . '/auth.php';