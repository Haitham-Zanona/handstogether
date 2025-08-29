@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle = 'البوابة الإدارية';
$pageTitle = 'التقارير الإدارية';
$pageDescription = 'تقارير شاملة عن أداء الأكاديمية وإحصائياتها';
@endphp

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">التقارير الإدارية</h1>
            <p class="mt-2 text-gray-600">نظرة شاملة على أداء الأكاديمية للشهر الحالي</p>
        </div>
        <div class="flex space-x-3 space-x-reverse">
            <button class="px-4 py-2 text-white transition duration-300 rounded-lg bg-primary hover:bg-blue-700">
                <svg class="inline-block w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                تصدير PDF
            </button>
            <button class="px-4 py-2 text-white transition duration-300 bg-green-600 rounded-lg hover:bg-green-700">
                <svg class="inline-block w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                تصدير Excel
            </button>
        </div>
    </div>
</div>

<!-- Overview Statistics -->
<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
    <!-- Total Students -->
    <div class="p-6 bg-white border-r-4 border-blue-500 rounded-lg shadow">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="p-3 bg-blue-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m3 5.197H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mr-4">
                <p class="text-sm font-medium text-gray-600">إجمالي الطلاب</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_students'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Total Teachers -->
    <div class="p-6 bg-white border-r-4 border-green-500 rounded-lg shadow">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="p-3 bg-green-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>
            <div class="mr-4">
                <p class="text-sm font-medium text-gray-600">عدد المدرسين</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_teachers'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Total Groups -->
    <div class="p-6 bg-white border-r-4 rounded-lg shadow border-secondary">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="p-3 rounded-full bg-secondary">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mr-4">
                <p class="text-sm font-medium text-gray-600">عدد المجموعات</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_groups'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Monthly Lectures -->
    <div class="p-6 bg-white border-r-4 border-purple-500 rounded-lg shadow">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="p-3 bg-purple-500 rounded-full">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="mr-4">
                <p class="text-sm font-medium text-gray-600">محاضرات هذا الشهر</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_lectures_this_month'] ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 gap-8 mb-8 xl:grid-cols-2">
    <!-- Attendance Chart -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">إحصائيات الحضور</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $attendanceStats['present_count'] ?? 0 }}</p>
                    <p class="text-sm text-gray-600">حاضر</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-yellow-600">{{ ($attendanceStats['total_attendance'] ?? 0) -
                        ($attendanceStats['present_count'] ?? 0) }}</p>
                    <p class="text-sm text-gray-600">غائب/متأخر</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-600">
                        @php
                        $total = $attendanceStats['total_attendance'] ?? 0;
                        $present = $attendanceStats['present_count'] ?? 0;
                        $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;
                        @endphp
                        {{ $percentage }}%
                    </p>
                    <p class="text-sm text-gray-600">نسبة الحضور</p>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="w-full h-3 bg-gray-200 rounded-full">
                <div class="h-3 transition-all duration-300 bg-green-600 rounded-full"
                    style="width: {{ $percentage }}%"></div>
            </div>
        </div>
    </div>

    <!-- Payments Chart -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">إحصائيات المدفوعات</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 rounded-lg bg-green-50">
                    <div class="flex items-center">
                        <div class="w-3 h-3 ml-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm font-medium text-gray-700">المدفوعات المحصلة</span>
                    </div>
                    <span class="font-bold text-green-600">{{ number_format($paymentStats['total_paid'] ?? 0) }}
                        ش.ج</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-lg bg-yellow-50">
                    <div class="flex items-center">
                        <div class="w-3 h-3 ml-3 bg-yellow-500 rounded-full"></div>
                        <span class="text-sm font-medium text-gray-700">المدفوعات المعلقة</span>
                    </div>
                    <span class="font-bold text-yellow-600">{{ $paymentStats['pending_payments'] ?? 0 }}</span>
                </div>

                <div class="flex items-center justify-between p-3 rounded-lg bg-red-50">
                    <div class="flex items-center">
                        <div class="w-3 h-3 ml-3 bg-red-500 rounded-full"></div>
                        <span class="text-sm font-medium text-gray-700">المدفوعات المتأخرة</span>
                    </div>
                    <span class="font-bold text-red-600">{{ $paymentStats['overdue_payments'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Reports Section -->
<div class="grid grid-cols-1 gap-8 mb-8 xl:grid-cols-3">
    <!-- Top Performing Groups -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">أفضل المجموعات أداءً</h3>
        </div>
        <div class="p-6">
            @php
            $topGroups = [
            ['name' => 'الرياضيات المتقدمة', 'attendance' => 95, 'students' => 25],
            ['name' => 'العلوم التطبيقية', 'attendance' => 92, 'students' => 28],
            ['name' => 'اللغة العربية', 'attendance' => 88, 'students' => 30],
            ];
            @endphp
            <div class="space-y-4">
                @foreach($topGroups as $index => $group)
                <div class="flex items-center p-3 border border-gray-200 rounded-lg">
                    <div
                        class="flex items-center justify-center w-8 h-8 ml-3 text-sm font-bold text-white rounded-full bg-primary">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ $group['name'] }}</p>
                        <p class="text-sm text-gray-600">{{ $group['students'] }} طالب</p>
                    </div>
                    <div class="text-left">
                        <p class="font-bold text-green-600">{{ $group['attendance'] }}%</p>
                        <p class="text-xs text-gray-500">نسبة الحضور</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">الأنشطة الأخيرة</h3>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @php
                $activities = [
                ['action' => 'طلب انتساب جديد', 'user' => 'أحمد محمد', 'time' => '10 دقائق', 'type' => 'info'],
                ['action' => 'تم تحديث دفعة', 'user' => 'فاطمة علي', 'time' => '30 دقيقة', 'type' => 'success'],
                ['action' => 'تسجيل حضور', 'user' => 'مجموعة الرياضيات', 'time' => '1 ساعة', 'type' => 'primary'],
                ];
                @endphp
                @foreach($activities as $activity)
                <div class="flex items-start">
                    <div class="flex-shrink-0 ml-3">
                        @php
                        $iconColor = match($activity['type']) {
                        'success' => 'text-green-500 bg-green-100',
                        'info' => 'text-blue-500 bg-blue-100',
                        'primary' => 'text-primary bg-blue-100',
                        default => 'text-gray-500 bg-gray-100'
                        };
                        @endphp
                        <div class="w-8 h-8 rounded-full {{ $iconColor }} flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $activity['action'] }}</p>
                        <p class="text-sm text-gray-600">{{ $activity['user'] }}</p>
                        <p class="mt-1 text-xs text-gray-500">منذ {{ $activity['time'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">إجراءات سريعة</h3>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                <a href="{{ route('admin.admissions') }}"
                    class="block w-full px-4 py-2 text-center text-white transition duration-300 rounded-lg bg-primary hover:bg-blue-700">
                    مراجعة طلبات الانتساب
                </a>

                <button
                    class="w-full px-4 py-2 text-white transition duration-300 rounded-lg bg-secondary hover:bg-orange-600">
                    إرسال تذكيرات الدفع
                </button>

                <button
                    class="w-full px-4 py-2 text-white transition duration-300 bg-green-600 rounded-lg hover:bg-green-700">
                    تنبيهات الحضور المنخفض
                </button>

                <a href="{{ route('admin.groups') }}"
                    class="block w-full px-4 py-2 text-center text-white transition duration-300 bg-purple-600 rounded-lg hover:bg-purple-700">
                    إدارة المجموعات
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Trends -->
<div class="mb-8 bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">الاتجاهات الشهرية</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Enrollment Trend -->
            <div class="text-center">
                <div class="p-4 mb-3 bg-blue-100 rounded-lg">
                    <svg class="w-8 h-8 mx-auto text-blue-600" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <h4 class="font-semibold text-gray-900">نمو الانتساب</h4>
                <p class="text-2xl font-bold text-blue-600">+15%</p>
                <p class="text-sm text-gray-500">مقارنة بالشهر الماضي</p>
            </div>

            <!-- Attendance Trend -->
            <div class="text-center">
                <div class="p-4 mb-3 bg-green-100 rounded-lg">
                    <svg class="w-8 h-8 mx-auto text-green-600" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h4 class="font-semibold text-gray-900">تحسن الحضور</h4>
                <p class="text-2xl font-bold text-green-600">+8%</p>
                <p class="text-sm text-gray-500">نسبة الحضور ارتفعت</p>
            </div>

            <!-- Payment Collection -->
            <div class="text-center">
                <div class="p-4 mb-3 rounded-lg bg-secondary bg-opacity-10">
                    <svg class="w-8 h-8 mx-auto text-secondary" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
                <h4 class="font-semibold text-gray-900">تحصيل المدفوعات</h4>
                <p class="text-2xl font-bold text-secondary">95%</p>
                <p class="text-sm text-gray-500">من المدفوعات المستحقة</p>
            </div>
        </div>
    </div>
</div>

<!-- Alerts and Warnings -->
@if($paymentStats['overdue_payments'] > 0 || $attendanceStats['present_count'] < ($attendanceStats['total_attendance'] *
    0.8)) <div class="p-6 border border-yellow-200 rounded-lg bg-yellow-50">
    <div class="flex items-center mb-4">
        <svg class="w-6 h-6 ml-2 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
        <h3 class="text-lg font-semibold text-yellow-800">تحتاج انتباه</h3>
    </div>
    <div class="space-y-2">
        @if($paymentStats['overdue_payments'] > 0)
        <p class="text-yellow-700">• {{ $paymentStats['overdue_payments'] }} مدفوعات متأخرة تحتاج متابعة</p>
        @endif
        @if($attendanceStats['present_count'] < ($attendanceStats['total_attendance'] * 0.8)) <p
            class="text-yellow-700">• نسبة الحضور منخفضة هذا الشهر ({{ $percentage }}%)</p>
            @endif
    </div>
    </div>
    @endif
    @endsection
