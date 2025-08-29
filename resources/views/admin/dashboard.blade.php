@extends('layouts.dashboard')

@section('sidebar-menu')
{{-- <li>
    <a href="{{ route('admin.dashboard') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('admin.dashboard') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        لوحة التحكم الرئيسية
    </a>
</li>
<li>
    <a href="{{ route('admin.admissions') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('admin.admissions') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>
        طلبات الانتساب
        @php $pendingCount = \App\Models\Admission::pending()->count(); @endphp
        @if($pendingCount > 0)
        <span class="px-2 py-1 mr-auto text-xs text-white rounded-full bg-secondary">{{ $pendingCount }}</span>
        @endif
    </a>
</li>
<li>
    <a href="{{ route('admin.groups') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('admin.groups') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        المجموعات
    </a>
</li>
<li>
    <a href="{{ route('admin.attendance') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('admin.attendance') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
        </svg>
        الحضور والغياب
    </a>
</li>
<li>
    <a href="{{ route('admin.payments') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('admin.payments') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        الدفعات الشهرية
    </a>
</li> --}}

@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle = 'البوابة الإدارية';
$pageTitle = 'لوحة التحكم الإدارية';
$pageDescription = 'نظرة عامة على أداء الأكاديمية والإحصائيات الرئيسية';
@endphp

@section('content')
<!-- Statistics Cards -->
<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
    <!-- Students Count -->
    <div class="p-6 text-white rounded-lg shadow-lg bg-gradient-to-r from-blue-500 to-blue-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-blue-100">إجمالي الطلاب</p>
                <p class="text-3xl font-bold">{{ $studentsCount }}</p>
                <p class="mt-1 text-xs text-blue-100">+{{ $monthlyStats['new_students'] }} هذا الشهر</p>
            </div>
            <div class="p-3 bg-blue-600 rounded-full">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m3 5.197H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Teachers Count -->
    <div class="p-6 text-white rounded-lg shadow-lg bg-gradient-to-r from-green-500 to-green-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-green-100">عدد المدرسين</p>
                <p class="text-3xl font-bold">{{ $teachersCount }}</p>
                <p class="mt-1 text-xs text-green-100">نشط</p>
            </div>
            <div class="p-3 bg-green-600 rounded-full">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Groups Count -->
    <div class="p-6 text-white rounded-lg shadow-lg bg-gradient-to-r from-orange-500 to-orange-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-orange-100">عدد المجموعات</p>
                <p class="text-3xl font-bold">{{ $groupsCount }}</p>
                <p class="mt-1 text-xs text-orange-100">مجموعة دراسية</p>
            </div>
            <div class="p-3 bg-orange-600 rounded-full">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Pending Admissions -->
    <div class="p-6 text-white rounded-lg shadow-lg bg-gradient-to-r from-purple-500 to-purple-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-purple-100">طلبات الانتساب</p>
                <p class="text-3xl font-bold">{{ $pendingAdmissions }}</p>
                <p class="mt-1 text-xs text-purple-100">في الانتظار</p>
            </div>
            <div class="p-3 bg-purple-600 rounded-full">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Analytics and Calendar Row -->
<div class="grid grid-cols-1 gap-6 mb-8 xl:grid-cols-3">
    <!-- Monthly Analytics -->
    <div class="p-6 bg-white rounded-lg shadow xl:col-span-1">
        <h3 class="mb-4 text-lg font-semibold text-gray-900">إحصائيات الشهر الحالي</h3>
        <div class="space-y-4">
            <!-- Total Payments -->
            <div class="flex items-center justify-between p-4 rounded-lg bg-green-50">
                <div class="flex items-center">
                    <div class="p-2 ml-3 bg-green-500 rounded-full">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">إجمالي المدفوعات</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($monthlyStats['total_payments'])
                            }} ش.ج</p>
                    </div>
                </div>
            </div>

            <!-- Attendance Rate -->
            <div class="flex items-center justify-between p-4 rounded-lg bg-blue-50">
                <div class="flex items-center">
                    <div class="p-2 ml-3 bg-blue-500 rounded-full">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">معدل الحضور</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $monthlyStats['attendance_rate'] }}%</p>
                    </div>
                </div>
            </div>

            <!-- New Students -->
            <div class="flex items-center justify-between p-4 rounded-lg bg-purple-50">
                <div class="flex items-center">
                    <div class="p-2 ml-3 bg-purple-500 rounded-full">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">طلاب جدد</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $monthlyStats['new_students'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="p-6 bg-white rounded-lg shadow xl:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">جدول المحاضرات</h3>
            <div class="flex items-center space-x-2 space-x-reverse">
                <button class="px-3 py-1 text-sm text-white rounded-md bg-primary hover:bg-blue-700">اليوم</button>
                <button
                    class="px-3 py-1 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">الأسبوع</button>
                <button
                    class="px-3 py-1 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">الشهر</button>
            </div>
        </div>
        <div id="calendar" class="w-full h-96"></div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
    <a href="{{ route('admin.admissions') }}"
        class="block p-6 transition-shadow bg-white rounded-lg shadow hover:shadow-md">
        <div class="text-center">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 text-white rounded-full bg-primary">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
            </div>
            <h4 class="mb-1 font-semibold text-gray-900">إدارة الطلبات</h4>
            <p class="text-sm text-gray-600">راجع وادير طلبات الانتساب</p>
        </div>
    </a>

    <a href="{{ route('admin.groups') }}"
        class="block p-6 transition-shadow bg-white rounded-lg shadow hover:shadow-md">
        <div class="text-center">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 text-white bg-green-500 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h4 class="mb-1 font-semibold text-gray-900">إدارة المجموعات</h4>
            <p class="text-sm text-gray-600">أضف وعدل المجموعات الدراسية</p>
        </div>
    </a>

    <a href="{{ route('admin.attendance') }}"
        class="block p-6 transition-shadow bg-white rounded-lg shadow hover:shadow-md">
        <div class="text-center">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 text-white rounded-full bg-secondary">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <h4 class="mb-1 font-semibold text-gray-900">تقارير الحضور</h4>
            <p class="text-sm text-gray-600">راجع تقارير الحضور والغياب</p>
        </div>
    </a>

    <a href="{{ route('admin.payments') }}"
        class="block p-6 transition-shadow bg-white rounded-lg shadow hover:shadow-md">
        <div class="text-center">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 text-white bg-purple-500 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h4 class="mb-1 font-semibold text-gray-900">إدارة الدفعات</h4>
            <p class="text-sm text-gray-600">تتبع ادارة المدفوعات الشهرية</p>
        </div>
    </a>
</div>

<!-- Recent Activities -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">الأنشطة الأخيرة</h3>
    </div>
    <div class="p-6">
        <div class="flow-root">
            <ul class="-mb-8">
                @php
                $recentActivities = [
                [
                'type' => 'admission',
                'message' => 'طلب انتساب جديد من أحمد محمد',
                'time' => '10 دقائق',
                'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
                'color' => 'bg-purple-500'
                ],
                [
                'type' => 'payment',
                'message' => 'تم تحديث حالة دفعة لطالب فاطمة علي',
                'time' => '30 دقيقة',
                'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2
                0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
                'color' => 'bg-green-500'
                ],
                [
                'type' => 'attendance',
                'message' => 'تم تسجيل حضور مجموعة الرياضيات المتقدمة',
                'time' => '1 ساعة',
                'icon' => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0
                002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                'color' => 'bg-blue-500'
                ]
                ];
                @endphp

                @foreach($recentActivities as $index => $activity)
                <li>
                    <div class="relative pb-8">
                        @if($index < count($recentActivities) - 1) <span
                            class="absolute top-4 right-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                            @endif
                            <div class="relative flex space-x-3 space-x-reverse">
                                <div
                                    class="{{ $activity['color'] }} h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="{{ $activity['icon'] }}" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-reverse space-x-4">
                                    <div>
                                        <p class="text-sm text-gray-900">{{ $activity['message'] }}</p>
                                    </div>
                                    <div class="text-sm text-left text-gray-500 whitespace-nowrap">
                                        <time>منذ {{ $activity['time'] }}</time>
                                    </div>
                                </div>
                            </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
                                var calendarEl = document.getElementById('calendar');
                                var calendar = new FullCalendar.Calendar(calendarEl, {
                                    initialView: 'dayGridMonth',
                                    locale: 'ar',
                                    direction: 'rtl',
                                    headerToolbar: {
                                        left: 'prev,next today',
                                        center: 'title',
                                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                                    },
                                    events: @json($lectures),
                                    eventClick: function(info) {
                                        alert('محاضرة: ' + info.event.title + '\n' +
                                              'المدرس: ' + info.event.extendedProps.teacher + '\n' +
                                              'المجموعة: ' + info.event.extendedProps.group);
                                    },
                                    height: 'auto',
                                    eventDisplay: 'block',
                                    dayMaxEvents: 3,
                                    moreLinkText: function(num) {
                                        return 'المزيد +' + num;
                                    },
                                    buttonText: {
                                        today: 'اليوم',
                                        month: 'شهر',
                                        week: 'أسبوع',
                                        day: 'يوم'
                                    }
                                });
                                calendar.render();
                            });
</script>
@endpush