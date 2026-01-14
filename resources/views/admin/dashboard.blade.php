@extends('layouts.dashboard')

@section('sidebar-menu')


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
    <div class="p-6 bg-white shadow-md rounded-2xl xl:col-span-2">
        <!-- العنوان مع معلومات إضافية -->
        <div class="flex items-center justify-between mb-4">
            <h3 class="flex items-center text-lg font-semibold text-gray-800">
                📅 التقويم الأكاديمي
                <span id="lectureStats"
                    class="hidden px-2 py-1 mr-2 text-xs text-blue-800 bg-blue-100 rounded-full"></span>
            </h3>
            <span id="currentPeriod" class="text-sm text-gray-500"></span>
        </div>

        <!-- أزرار التنقل مع مؤشر التحميل -->
        <div class="flex items-center justify-between mb-4">
            <button id="prevBtn"
                class="flex items-center px-3 py-1 text-gray-700 transition-colors bg-gray-200 rounded hover:bg-gray-300 disabled:opacity-50">
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                السابق
            </button>

            <!-- مؤشر التحميل -->
            <div id="loadingIndicator" class="hidden">
                <svg class="w-5 h-5 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                    <path fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        class="opacity-75"></path>
                </svg>
            </div>

            <button id="nextBtn"
                class="flex items-center px-3 py-1 text-gray-700 transition-colors bg-gray-200 rounded hover:bg-gray-300 disabled:opacity-50">
                التالي
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>

        <!-- شبكة التقويم -->
        <div class="grid grid-cols-7 overflow-hidden text-sm text-center border rounded-lg shadow-sm">
            <!-- رؤوس الأيام -->
            <div class="p-3 font-semibold text-gray-700 border-b bg-gray-50">الأحد</div>
            <div class="p-3 font-semibold text-gray-700 border-b bg-gray-50">الاثنين</div>
            <div class="p-3 font-semibold text-gray-700 border-b bg-gray-50">الثلاثاء</div>
            <div class="p-3 font-semibold text-gray-700 border-b bg-gray-50">الأربعاء</div>
            <div class="p-3 font-semibold text-gray-700 border-b bg-gray-50">الخميس</div>
            <div class="p-3 font-semibold text-gray-700 border-b bg-gray-50">الجمعة</div>
            <div class="p-3 font-semibold text-gray-700 border-b bg-gray-50">السبت</div>

            <!-- شبكة الأيام - يتم ملؤها بـ JavaScript -->
            <div id="calendarGrid" class="grid grid-cols-7 col-span-7"></div>
        </div>

        <!-- محاضرات اليوم - قسم محسن -->
        <div class="mt-6">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-base font-semibold text-gray-700">📌 محاضرات اليوم</h4>
                <button id="refreshTodayBtn" class="flex items-center text-xs text-blue-600 hover:text-blue-800"
                    onclick="window.dashboardCalendar?.refresh()">
                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    تحديث
                </button>
            </div>

            <!-- محتوى محاضرات اليوم -->
            <div id="todayLectures" class="min-h-[100px]">
                <!-- يتم ملء هذا القسم بـ JavaScript -->
                <div class="py-4 text-center text-gray-500">
                    <div class="animate-pulse">جاري التحميل...</div>
                </div>
            </div>
        </div>

        <!-- معلومات إضافية -->
        <div class="pt-4 mt-4 border-t border-gray-100">
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>آخر تحديث: <span id="lastUpdate">---</span></span>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <div class="flex items-center">
                        <div class="w-2 h-2 ml-1 bg-green-500 rounded-full"></div>
                        <span>محاضرة واحدة</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 ml-1 bg-orange-500 rounded-full"></div>
                        <span>2-3 محاضرات</span>
                    </div>
                    <div class="flex items-center">
                        <div class="w-2 h-2 ml-1 bg-red-500 rounded-full"></div>
                        <span>أكثر من 3</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tooltip -->
    <div id="lectureTooltip" class="absolute z-50 hidden px-3 py-2 text-xs text-white bg-gray-800 rounded-lg shadow-lg">
    </div>

    <!-- Modal -->
    <div id="lectureModal" class="fixed inset-0 z-50 items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-lg p-6 bg-white shadow-lg rounded-xl">
            <h5 class="mb-4 text-lg font-bold">📖 تفاصيل المحاضرة</h5>
            <div id="lectureModalContent" class="space-y-2 text-sm text-gray-700"></div>
            <div class="flex justify-end mt-4">
                <button id="closeModal" class="px-4 py-2 text-white bg-red-500 rounded-lg hover:bg-red-600">
                    إغلاق
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
    <a href="{{ route('admin.admissions.index') }}"
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

    <a href="{{ route('admin.groups.index') }}"
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
    // 📅 تقويم المحاضرات المصحح للـ Dashboard
class LecturesCalendar {
    constructor() {
        this.currentDate = new Date();
        this.currentView = 'month';
        this.lectures = [];
        this.isLoading = false;
        this.tooltip = this.createTooltip();

        this.monthNames = [
            'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
            'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
        ];

        this.dayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

        this.subjectColors = {
            'رياضيات': '#3b82f6',
            'فيزياء': '#ef4444',
            'كيمياء': '#10b981',
            'عربي': '#f59e0b',
            'إنجليزي': '#8b5cf6',
            'تاريخ': '#06b6d4',
            'جغرافيا': '#84cc16',
            'أحياء': '#f97316',
            'علوم': '#ec4899',
            'default': '#6366f1'
        };

        this.init();
    }

    createTooltip() {
        const oldTooltip = document.getElementById('dashboardTooltip');
        if (oldTooltip) oldTooltip.remove();

        const tooltip = document.createElement('div');
        tooltip.id = 'dashboardTooltip';
        tooltip.className = 'absolute z-50 hidden px-4 py-3 text-sm text-white bg-gray-900 rounded-lg shadow-xl max-w-sm';
        tooltip.style.pointerEvents = 'none';
        document.body.appendChild(tooltip);
        return tooltip;
    }

    init() {
        this.setupEventListeners();
        this.loadLectures();
        this.addCustomStyles();
    }

    setupEventListeners() {
        document.getElementById('prevBtn')?.addEventListener('click', () => this.previousPeriod());
        document.getElementById('nextBtn')?.addEventListener('click', () => this.nextPeriod());

        window.addEventListener('scroll', () => this.hideTooltip());
        window.addEventListener('resize', () => this.hideTooltip());

        document.getElementById("closeModal")?.addEventListener("click", () => {
            const modal = document.getElementById("lectureModal");
            if (modal) {
                modal.classList.add("hidden");
                modal.classList.remove("flex");
            }
        });

        document.addEventListener('keydown', (e) => {
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
            switch(e.key) {
                case 'ArrowLeft': this.nextPeriod(); break;
                case 'ArrowRight': this.previousPeriod(); break;
            }
        });
    }

    setLoading(loading) {
        this.isLoading = loading;
        const indicator = document.getElementById('loadingIndicator');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        if (indicator) {
            indicator.classList.toggle('hidden', !loading);
        }

        if (prevBtn && nextBtn) {
            prevBtn.disabled = loading;
            nextBtn.disabled = loading;
        }
    }

    updateLastUpdateTime() {
        const lastUpdateElement = document.getElementById('lastUpdate');
        if (lastUpdateElement) {
            const now = new Date();
            lastUpdateElement.textContent = now.toLocaleTimeString('ar-EG', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }

    updateStats(stats) {
        const statsElement = document.getElementById('lectureStats');
        if (statsElement && stats) {
            statsElement.textContent = `${stats.total_lectures || 0} محاضرة`;
            statsElement.classList.remove('hidden');
        }
    }

    // ------------------ جلب البيانات مع تحسينات ------------------
    async loadLectures() {
        try {
            this.setLoading(true);

            // تنظيف البيانات القديمة
            this.lectures = [];

            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth() + 1;
            const start = `${year}-${month.toString().padStart(2, '0')}-01`;
            const end = `${year}-${month.toString().padStart(2, '0')}-31`;

            console.log(`🔄 جاري جلب البيانات للفترة: ${start} إلى ${end}`);

            // تجربة الـ API الجديد أولاً
            const response = await fetch(`/admin/dashboard/calendar-data?start=${start}&end=${end}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            console.log(`📡 Response Status: ${response.status}`);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('📊 البيانات المستلمة:', data);

            if (data.lectures && Array.isArray(data.lectures)) {
                this.lectures = data.lectures;
                this.updateStats(data.stats);
                console.log(`✅ تم تحميل ${this.lectures.length} محاضرة`);
            } else {
                console.warn('⚠️ البيانات المستلمة لا تحتوي على مصفوفة محاضرات صحيحة');
                throw new Error('صيغة البيانات غير صحيحة');
            }

        } catch (error) {
            console.error("❌ خطأ في جلب المحاضرات:", error);
            console.warn("🔄 التبديل إلى البيانات التجريبية...");
            this.lectures = this.generateSampleData();
            this.updateStats({ total_lectures: this.lectures.length });
        } finally {
            this.setLoading(false);
            this.renderCalendar();
            this.updateLastUpdateTime();
        }
    }

    generateSampleData() {
        const today = new Date();
        const sampleSubjects = ['رياضيات', 'فيزياء', 'كيمياء', 'عربي', 'إنجليزي'];
        const sampleTeachers = ['أ. أحمد محمد', 'د. فاطمة علي', 'أ. محمود حسن', 'د. سارة أحمد'];

        console.log("🧪 إنشاء بيانات تجريبية...");

        return Array.from({ length: 15 }).map((_, i) => {
            const date = new Date(today.getFullYear(), today.getMonth(), Math.floor(Math.random() * 28) + 1);
            const subject = sampleSubjects[Math.floor(Math.random() * sampleSubjects.length)];
            const teacher = sampleTeachers[Math.floor(Math.random() * sampleTeachers.length)];

            return {
                id: i + 1,
                title: `محاضرة ${subject}`,
                subject: subject,
                date: date.toISOString().split('T')[0],
                start_time: `${9 + Math.floor(Math.random() * 6)}:${Math.random() > 0.5 ? '00' : '30'}`,
                end_time: `${11 + Math.floor(Math.random() * 4)}:${Math.random() > 0.5 ? '00' : '30'}`,
                teacher: teacher,
                group: `المجموعة ${String.fromCharCode(65 + Math.floor(Math.random() * 5))}`,
                room: `قاعة ${Math.floor(Math.random() * 10) + 1}`,
                students_count: Math.floor(Math.random() * 25) + 15
            };
        });
    }

    // ------------------ العرض المُصحح ------------------
    renderCalendar() {
        const grid = document.getElementById("calendarGrid");
        const currentPeriod = document.getElementById("currentPeriod");

        if (!grid || !currentPeriod) {
            console.error('❌ عناصر التقويم غير موجودة في DOM');
            return;
        }

        console.log(`🎨 عرض التقويم - عدد المحاضرات: ${this.lectures.length}`);

        this.renderMonthView(grid, currentPeriod);
        this.renderTodayLectures();
    }

    renderMonthView(grid, currentPeriod) {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        currentPeriod.textContent = `${this.monthNames[month]} ${year}`;

        const firstDay = new Date(year, month, 1);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay());

        let html = "";

        // إنشاء 42 خلية (6 أسابيع × 7 أيام)
        const current = new Date(startDate);
        for (let i = 0; i < 42; i++) {
            const isCurrentMonth = current.getMonth() === month;
            const isToday = this.isToday(current);
            const dayLectures = this.getLecturesForDate(current);
            const lectureCount = dayLectures.length;

            // إضافة log للأيام التي تحتوي على محاضرات
            if (lectureCount > 0) {
                console.log(`📅 ${current.toDateString()}: ${lectureCount} محاضرات`, dayLectures);
            }

            html += `
                <div class="calendar-day relative min-h-[80px] p-2 border border-gray-200 hover:bg-gray-50 transition-all duration-200 cursor-pointer ${
                    !isCurrentMonth ? 'bg-gray-50 text-gray-400 other-month' : 'bg-white'
                } ${isToday ? 'bg-blue-50 border-blue-300 shadow-sm today' : ''}"
                     data-date="${current.toISOString().split('T')[0]}"
                     onmouseenter="window.lecturesCalendar.showDayTooltip(event, '${current.toISOString().split('T')[0]}')"
                     onmouseleave="window.lecturesCalendar.hideTooltip()">

                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium day-number ${isToday ? 'text-blue-600 font-bold' : ''}">${current.getDate()}</span>
                        ${lectureCount > 0 ? `
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-white rounded-full ${
                                lectureCount > 3 ? 'bg-red-500' : lectureCount > 1 ? 'bg-orange-500' : 'bg-green-500'
                            }">
                                ${lectureCount}
                            </span>
                        ` : ''}
                    </div>

                    ${lectureCount > 0 ? `
                        <div class="space-y-1">
                            ${dayLectures.slice(0, 2).map(lecture => `
                                <div class="w-full h-1.5 rounded-full shadow-sm lecture-bar"
                                     style="background-color: ${this.getSubjectColor(lecture.subject)}"
                                     title="${lecture.subject}"></div>
                            `).join('')}
                            ${lectureCount > 2 ? `
                                <div class="mt-1 text-xs text-center text-gray-500">+${lectureCount - 2}</div>
                            ` : ''}
                        </div>
                    ` : ''}
                </div>
            `;

            current.setDate(current.getDate() + 1);
        }

        grid.innerHTML = html;
        console.log(`✅ تم عرض التقويم - HTML length: ${html.length}`);
    }

    showDayTooltip(event, date) {
       const dayLectures = this.getLecturesForDate(new Date(date));

        if (dayLectures.length === 0) return;

        const dateObj = new Date(date);
        const dayName = this.dayNames[dateObj.getDay()];
        const dayNumber = dateObj.getDate();

        dayLectures.sort((a, b) => a.start_time.localeCompare(b.start_time));

        this.tooltip.innerHTML = `
        <div class="pb-2 mb-3 text-center border-b border-gray-700">
            <div class="font-bold text-blue-300">${dayName}</div>
            <div class="text-xs text-gray-300">${dayNumber} ${this.monthNames[dateObj.getMonth()]}</div>
        </div>
        <div class="space-y-2 overflow-y-auto max-h-64">
            ${dayLectures.map(lecture => `
            <div class="flex items-start p-2 space-x-2 space-x-reverse text-xs bg-gray-800 rounded">
                <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0"
                    style="background-color: ${this.getSubjectColor(lecture.subject)}"></div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-white truncate">${lecture.subject || lecture.title}</div>
                    <div class="text-gray-300">⏰ ${this.formatTimeDisplay(lecture.start_time)} - ${this.formatTimeDisplay(lecture.end_time)}</div>
                    <div class="text-gray-400 truncate">👨‍🏫 ${lecture.teacher}</div>
                    <div class="text-gray-400 truncate">📚 ${lecture.group}</div>
                </div>
            </div>
            `).join('')}
        </div>
        `;

        this.tooltip.classList.remove('hidden');

        // تحديد موضع الـ tooltip
        const rect = event.currentTarget.getBoundingClientRect();
        let top = rect.top + window.scrollY - 10;
        let left = rect.right + window.scrollX + 10;

        // التأكد من أن الـ tooltip داخل الشاشة
        if (left + 300 > window.innerWidth) {
            left = rect.left + window.scrollX - 310;
        }
        if (top < 10) {
            top = rect.bottom + window.scrollY + 10;
        }

        this.tooltip.style.top = `${top}px`;
        this.tooltip.style.left = `${left}px`;
    }

    formatTimeDisplay(time) {
    if (!time) return '09:00';

    // إذا كان ISO string
    if (typeof time === 'string' && time.includes('T')) {
        try {
            const date = new Date(time);
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            const period = date.getHours() >= 12 ? 'مساءً' : 'صباحاً';
            let displayHour = date.getHours();

            // تحويل للنظام 12 ساعة
            if (displayHour === 0) displayHour = 12;
            else if (displayHour > 12) displayHour = displayHour - 12;

            return `${displayHour}:${minutes} ${period}`;
        } catch (e) {
            return time.substring(0, 5);
        }
    }

    // إذا كان وقت بسيط
    if (typeof time === 'string' && time.includes(':')) {
        const [hours, minutes] = time.split(':');
        const hour24 = parseInt(hours);
        const period = hour24 >= 12 ? 'مساءً' : 'صباحاً';
        let displayHour = hour24;

        if (displayHour === 0) displayHour = 12;
        else if (displayHour > 12) displayHour = displayHour - 12;

        return `${displayHour}:${minutes} ${period}`;
    }

    return time;
}

    renderTodayLectures() {
        const todayLectures = this.getLecturesForDate(new Date());
        const container = document.getElementById("todayLectures");

        if (!container) {
            console.error('❌ عنصر todayLectures غير موجود');
            return;
        }

        console.log(`📋 محاضرات اليوم: ${todayLectures.length}`);

        if (todayLectures.length === 0) {
            container.innerHTML = `
                <div class="py-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="font-medium text-gray-600">لا توجد محاضرات اليوم</p>
                    <p class="mt-1 text-sm text-gray-500">استمتع بيومك الحر! 🌟</p>
                </div>
            `;
            return;
        }

        todayLectures.sort((a, b) => a.start_time.localeCompare(b.start_time));

        container.innerHTML = `
            <div class="space-y-3">
                ${todayLectures.map((lecture, index) => `
                    <div class="flex items-center p-4 bg-gradient-to-r from-white to-gray-50 border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 hover:scale-[1.01]">
                        <div class="flex-shrink-0 w-3 h-12 mr-3 rounded-full shadow-sm"
                             style="background: linear-gradient(to bottom, ${this.getSubjectColor(lecture.subject)}, ${this.getSubjectColor(lecture.subject)}88)"></div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-900 truncate">${lecture.title}</p>
                                <span class="px-2 py-1 text-xs text-gray-500 bg-gray-100 rounded-full">${lecture.room || 'غير محدد'}</span>
                            </div>
                            <p class="flex items-center mt-1 text-xs text-gray-600">
                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                ${lecture.start_time} - ${lecture.end_time}
                                <span class="mr-2">👨‍🏫 ${lecture.teacher}</span>
                                <span class="mr-2">📚 ${lecture.group}</span>
                            </p>
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex items-center text-xs text-gray-500">
                                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m3 5.197H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    ${lecture.students_count || 0} طالب
                                </div>
                                <div class="text-xs">
                                    <span class="inline-flex items-center px-2 py-1 text-green-800 bg-green-100 rounded-full">
                                        مجدولة
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // ------------------ دوال مساعدة ------------------
    getSubjectColor(subject) {
        return this.subjectColors[subject] || this.subjectColors.default;
    }

    getLecturesForDate(d) {
        const dateStr = d.toISOString().split("T")[0];
        const filtered = this.lectures.filter(l => l.date === dateStr);
        // console.log(`🔍 البحث عن محاضرات في ${dateStr}: وجدت ${filtered.length}`);
        return filtered;
    }

    isToday(d) {
        return d.toDateString() === new Date().toDateString();
    }

    hideTooltip() {
        if (this.tooltip) {
            this.tooltip.classList.add("hidden");
        }
    }

    previousPeriod() {
        if (this.isLoading) return;
        this.currentDate.setMonth(this.currentDate.getMonth() - 1);
        this.loadLectures();
    }

    nextPeriod() {
        if (this.isLoading) return;
        this.currentDate.setMonth(this.currentDate.getMonth() + 1);
        this.loadLectures();
    }

    refresh() {
        if (this.isLoading) return;
        console.log('🔄 تحديث البيانات...');
        this.loadLectures();
    }

    // اختبار الاتصال بالـ API
    async testAPI() {
        try {
            console.log('🧪 اختبار الاتصال بالـ API...');
            const response = await fetch('/admin/dashboard/calendar-data', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            console.log('📡 حالة الاستجابة:', response.status);
            const data = await response.json();
            console.log('📊 بيانات الاختبار:', data);

            return data;
        } catch (error) {
            console.error('❌ خطأ في اختبار API:', error);
            return null;
        }
    }

    addCustomStyles() {
        if (document.getElementById('dashboard-calendar-styles')) return;

        const styleElement = document.createElement('style');
        styleElement.id = 'dashboard-calendar-styles';
        styleElement.textContent = `
            .calendar-day {
                position: relative;
                overflow: hidden;
                transition: all 0.2s ease;
            }

            .calendar-day:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }

            .calendar-day.today::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
                background: linear-gradient(90deg, #3b82f6, #1d4ed8);
                border-radius: 4px 4px 0 0;
            }

            .lecture-bar {
                transition: all 0.2s ease;
            }

            .calendar-day:hover .lecture-bar {
                transform: scaleY(1.2);
            }

            #dashboardTooltip {
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.1);
                box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
                z-index: 1000;
            }

            #dashboardTooltip::before {
                content: '';
                position: absolute;
                bottom: -8px;
                left: 20px;
                width: 0;
                height: 0;
                border-left: 8px solid transparent;
                border-right: 8px solid transparent;
                border-top: 8px solid rgb(17, 24, 39);
            }
        `;
        document.head.appendChild(styleElement);
    }
}

// ------------------ تشغيل ------------------
document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("calendarGrid")) {
        window.lecturesCalendar = new LecturesCalendar();
        console.log("📅 تم تحميل تقويم المحاضرات المحسن للـ Dashboard");

        // إضافة دالة اختبار لـ Console
        window.testCalendarAPI = () => window.lecturesCalendar.testAPI();

        // تحديث تلقائي كل 5 دقائق
        setInterval(() => {
            if (window.lecturesCalendar && !window.lecturesCalendar.isLoading) {
                window.lecturesCalendar.refresh();
            }
        }, 300000);
    } else {
        console.error('❌ عنصر calendarGrid غير موجود في الصفحة');
    }
});
</script>

@endpush
