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
        <!-- العنوان -->
        <h3 class="flex items-center justify-between mb-4 text-lg font-semibold text-gray-800">
            📅 التقويم الأكاديمي
            <span id="currentPeriod" class="text-sm text-gray-500"></span>
        </h3>

        <!-- أزرار التنقل -->
        <div class="flex justify-between mb-4">
            <button id="prevBtn" class="px-3 py-1 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">❮ السابق</button>
            <button id="nextBtn" class="px-3 py-1 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">التالي ❯</button>
        </div>

        <!-- شبكة الأيام -->
        <div class="grid grid-cols-7 overflow-hidden text-sm text-center border rounded-lg">
            <!-- رؤوس الأيام -->
            <div class="p-2 font-semibold bg-gray-100">الأحد</div>
            <div class="p-2 font-semibold bg-gray-100">الاثنين</div>
            <div class="p-2 font-semibold bg-gray-100">الثلاثاء</div>
            <div class="p-2 font-semibold bg-gray-100">الأربعاء</div>
            <div class="p-2 font-semibold bg-gray-100">الخميس</div>
            <div class="p-2 font-semibold bg-gray-100">الجمعة</div>
            <div class="p-2 font-semibold bg-gray-100">السبت</div>

            <!-- هنا بضيف الأيام ديناميكياً بالـ JS -->
            <div id="calendarGrid" class="grid grid-cols-7 col-span-7"></div>
        </div>

        <!-- محاضرات اليوم -->
        <div class="mt-6">
            <h4 class="mb-2 text-base font-semibold text-gray-700">📌 محاضرات اليوم</h4>
            <ul id="todayLectures" class="space-y-2 text-sm text-gray-600"></ul>
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
{{-- <script>
    // FullCalendar Initialization
    class LecturesCalendar {
    constructor() {
    this.currentDate = new Date();
    this.currentView = 'month';
    this.lectures = [];
    this.tooltip = document.getElementById('lectureTooltip');

    this.monthNames = [
    'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
    ];

    this.dayNames = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

    this.groupColors = [
    '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
    '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6366f1'
    ];

    this.init();
    }

    init() {
    this.setupEventListeners();
    this.loadLectures();
    }

    setupEventListeners() {
    // View buttons
    document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
    document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
    e.target.classList.add('active');
    this.currentView = e.target.dataset.view;
    this.renderCalendar();
    });
    });

    // Navigation buttons
    document.getElementById('prevBtn').addEventListener('click', () => {
    this.previousPeriod();
    });

    document.getElementById('nextBtn').addEventListener('click', () => {
    this.nextPeriod();
    });

    // Tooltip events
    document.addEventListener('mouseover', (e) => {
    if (e.target.classList.contains('lecture-item')) {
    this.showTooltip(e);
    }
    });

    document.addEventListener('mouseout', (e) => {
    if (e.target.classList.contains('lecture-item')) {
    this.hideTooltip();
    }
    });
    }

    async loadLectures() {
    try {
    document.getElementById('loadingState').style.display = 'flex';

    // استدعاء API لجلب البيانات
    const response = await fetch('/admin/lectures/calendar-data', {
    method: 'GET',
    headers: {
    'Accept': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    }
    });

    if (!response.ok) {
    throw new Error('فشل في جلب البيانات');
    }

    this.lectures = await response.json();

    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('calendarGrid').style.display = 'grid';

    this.renderCalendar();

    } catch (error) {
    console.error('خطأ في جلب المحاضرات:', error);

    // استخدام بيانات وهمية للتجربة
    this.lectures = this.generateSampleData();

    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('calendarGrid').style.display = 'grid';

    this.renderCalendar();
    }
    }

    generateSampleData() {
    const sampleLectures = [];
    const today = new Date();

    for (let i = 0; i < 30; i++) { const date=new Date(today); date.setDate(today.getDate() + Math.floor(Math.random() * 30)
        - 15); if (Math.random()> 0.7) { // 30% احتمال وجود محاضرة
        sampleLectures.push({
        id: i + 1,
        title: `محاضرة ${['رياضيات', 'علوم', 'لغة عربية', 'تاريخ', 'جغرافيا'][Math.floor(Math.random() * 5)]}`,
        date: date.toISOString().split('T')[0],
        start_time: `${8 + Math.floor(Math.random() * 8)}:00`,
        end_time: `${10 + Math.floor(Math.random() * 6)}:00`,
        teacher: {
        user: {
        name: `أ. ${'محمد أحمد علي فاطمة سارة'.split(' ')[Math.floor(Math.random() * 5)]}`
        }
        },
        group: {
        id: Math.floor(Math.random() * 5) + 1,
        name: `مجموعة ${['الأولى', 'الثانية', 'الثالثة', 'الرابعة', 'الخامسة'][Math.floor(Math.random() * 5)]}`
        },
        description: 'وصف المحاضرة'
        });
        }
        }

        return sampleLectures;
        }

        renderCalendar() {
        const grid = document.getElementById('calendarGrid');
        const currentPeriod = document.getElementById('currentPeriod');

        if (this.currentView === 'month') {
        this.renderMonthView(grid, currentPeriod);
        } else if (this.currentView === 'week') {
        this.renderWeekView(grid, currentPeriod);
        } else {
        this.renderDayView(grid, currentPeriod);
        }

        this.renderTodayLectures();
        }

        renderMonthView(grid, currentPeriod) {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();

        currentPeriod.textContent = `${this.monthNames[month]} ${year}`;

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay());

        let html = '';

        // Headers
        this.dayNames.forEach(day => {
        html += `<div class="calendar-day-header">${day}</div>`;
        });

        // Days
        const current = new Date(startDate);
        for (let i = 0; i < 42; i++) { const isCurrentMonth=current.getMonth()===month; const isToday=this.isToday(current);
            const dayLectures=this.getLecturesForDate(current); html +=` <div
            class="calendar-day ${!isCurrentMonth ? 'other-month' : ''} ${isToday ? 'today' : ''}">
            <div class="day-number">${current.getDate()}</div>
            ${dayLectures.map(lecture => this.renderLectureItem(lecture)).join('')}
            </div>
            `;

            current.setDate(current.getDate() + 1);
            }

            grid.innerHTML = html;
            }

            renderWeekView(grid, currentPeriod) {
            // تنفيذ عرض الأسبوع
            const startOfWeek = new Date(this.currentDate);
            startOfWeek.setDate(this.currentDate.getDate() - this.currentDate.getDay());

            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6);

            currentPeriod.textContent = `${startOfWeek.getDate()} - ${endOfWeek.getDate()}
            ${this.monthNames[startOfWeek.getMonth()]} ${startOfWeek.getFullYear()}`;

            // نفس منطق الشهر لكن لأسبوع واحد
            this.renderMonthView(grid, currentPeriod);
            }

            renderDayView(grid, currentPeriod) {
            const today = new Date();
            currentPeriod.textContent = `${today.getDate()} ${this.monthNames[today.getMonth()]} ${today.getFullYear()}`;

            const todayLectures = this.getLecturesForDate(today);

            let html = '<div class="calendar-day-header">اليوم</div>';
            html += `
            <div class="calendar-day today" style="grid-column: 1 / -1; min-height: 400px;">
                <div class="day-number">${today.getDate()}</div>
                ${todayLectures.map(lecture => this.renderLectureItem(lecture)).join('')}
                ${todayLectures.length === 0 ? '<div style="text-align: center; color: #6b7280; margin-top: 50px;">لا توجد محاضرات اليوم</div>' : ''}
            </div>
            `;

            grid.innerHTML = html;
            }

            renderLectureItem(lecture) {
            const groupColorIndex = (lecture.group.id - 1) % this.groupColors.length;
            const hasStartedClass = lecture.has_started ? 'lecture-started' : '';
            const isTodayClass = lecture.is_today ? 'lecture-today' : '';

            return `
            <div class="lecture-item ${hasStartedClass} ${isTodayClass}"
                style="background-color: ${this.groupColors[groupColorIndex]}" data-lecture='${JSON.stringify(lecture)}'>
                ${lecture.start_time} ${lecture.title}
                ${lecture.has_started ? ' ✓' : ''}
            </div>
            `;
            }

            renderTodayLectures() {
                const todayLectures = this.getLecturesForDate(new Date());
                const container = document.getElementById('todayLectures');
                const list = document.getElementById('todayLecturesList');

                if (todayLectures.length > 0) {
                container.style.display = 'block';
                list.innerHTML = todayLectures.map(lecture => `
                <div class="today-lecture-item ${lecture.has_started ? 'started' : ''}">
                    <div class="lecture-time">${lecture.start_time} - ${lecture.end_time}</div>
                    <div class="lecture-details">
                        <div class="lecture-title">
                            ${lecture.title}
                            ${lecture.has_started ? '<span class="status-badge started">بدأت</span>' : '<span class="status-badge pending">قادمة</span>'}
                        </div>
                        <div class="lecture-group-teacher">
                            ${lecture.group.name} • ${lecture.teacher.user.name}
                        </div>
                        <div class="attendance-info">
                            الحضور: ${lecture.attendance_summary.present}/${lecture.attendance_summary.total} طالب
                        </div>
                    </div>
                </div>
                `).join('');
                } else {
                container.style.display = 'none';
                }
            }

            getLecturesForDate(date) {
            const dateStr = date.toISOString().split('T')[0];
            return this.lectures.filter(lecture => lecture.date === dateStr);
            }

            isToday(date) {
            const today = new Date();
            return date.toDateString() === today.toDateString();
            }

            showTooltip(e) {
           const lectureData = JSON.parse(e.target.dataset.lecture);
        const attendanceSummary = lectureData.attendance_summary;

        this.tooltip.innerHTML = `
        <div class="tooltip-title">${lectureData.title}</div>
        <div class="tooltip-info">
            <div class="tooltip-row">
                <span>الوقت:</span>
                <span>${lectureData.start_time} - ${lectureData.end_time}</span>
            </div>
            <div class="tooltip-row">
                <span>المجموعة:</span>
                <span>${lectureData.group.name}</span>
            </div>
            <div class="tooltip-row">
                <span>المدرس:</span>
                <span>${lectureData.teacher.user.name}</span>
            </div>
            <div class="tooltip-row">
                <span>الحضور:</span>
                <span>${attendanceSummary.present}/${attendanceSummary.total}</span>
            </div>
            <div class="tooltip-row">
                <span>الحالة:</span>
                <span>${lectureData.has_started ? 'بدأت' : 'لم تبدأ بعد'}</span>
            </div>
            ${lectureData.description ? `<div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #374151;">
                ${lectureData.description}</div>` : ''}
        </div>
        `;

            // Position tooltip
            const rect = e.target.getBoundingClientRect();
            this.tooltip.style.left = rect.left + (rect.width / 2) + 'px';
            this.tooltip.style.top = (rect.top - 10) + 'px';
            this.tooltip.style.transform = 'translateX(-50%) translateY(-100%)';

            this.tooltip.classList.add('show');
            }

            hideTooltip() {
            this.tooltip.classList.remove('show');
            }

            previousPeriod() {
            if (this.currentView === 'month') {
            this.currentDate.setMonth(this.currentDate.getMonth() - 1);
            } else if (this.currentView === 'week') {
            this.currentDate.setDate(this.currentDate.getDate() - 7);
            } else {
            this.currentDate.setDate(this.currentDate.getDate() - 1);
            }
            this.renderCalendar();
            }

            nextPeriod() {
            if (this.currentView === 'month') {
            this.currentDate.setMonth(this.currentDate.getMonth() + 1);
            } else if (this.currentView === 'week') {
            this.currentDate.setDate(this.currentDate.getDate() + 7);
            } else {
            this.currentDate.setDate(this.currentDate.getDate() + 1);
            }
            this.renderCalendar();
            }
            }

    document.addEventListener('DOMContentLoaded', function() {
        new LecturesCalendar();
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
</script> --}}

<script>
    // 📅 تقويم المحاضرات - نسخة كاملة وموحدة
class LecturesCalendar {
    constructor() {
        this.currentDate = new Date();
        this.currentView = 'month';
        this.lectures = [];
        this.tooltip = document.getElementById('lectureTooltip');

        this.monthNames = [
            'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
            'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
        ];

        this.dayNames = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

        this.groupColors = [
            '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
            '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6366f1'
        ];

        this.init();
    }

    // ------------------ التهيئة ------------------
    init() {
        this.setupEventListeners();
        this.loadLectures();
    }

    setupEventListeners() {
        // أزرار العرض
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.currentView = e.target.dataset.view;
                this.renderCalendar();
            });
        });

        // أزرار التنقل
        document.getElementById('prevBtn')?.addEventListener('click', () => this.previousPeriod());
        document.getElementById('nextBtn')?.addEventListener('click', () => this.nextPeriod());

        // Tooltip
        document.addEventListener('mouseover', (e) => {
            if (e.target.classList.contains('lecture-item')) this.showTooltip(e);
        });
        document.addEventListener('mouseout', (e) => {
            if (e.target.classList.contains('lecture-item')) this.hideTooltip();
        });

        // إغلاق الـ Tooltip عند التمرير/تغيير الحجم
        window.addEventListener('scroll', () => this.hideTooltip());
        window.addEventListener('resize', () => this.hideTooltip());

        // إغلاق الـ Modal
        document.getElementById("closeModal")?.addEventListener("click", () => {
            const modal = document.getElementById("lectureModal");
            modal.classList.add("hidden");
            modal.classList.remove("flex");
        });

        // اختصارات الكيبورد
        document.addEventListener('keydown', (e) => {
            if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
            switch(e.key) {
                case 'ArrowLeft': this.nextPeriod(); break;
                case 'ArrowRight': this.previousPeriod(); break;
                case '1': this.switchView('today'); break;
                case '2': this.switchView('week'); break;
                case '3': this.switchView('month'); break;
            }
        });
    }

    // ------------------ جلب البيانات ------------------
    async loadLectures() {
        try {
            const response = await fetch("{{ route('admin.lectures.calendar-data') }}");
            if (!response.ok) throw new Error(`HTTP error ${response.status}`);
            this.lectures = await response.json();
        } catch (error) {
            console.warn("⚠️ خطأ في جلب المحاضرات، استخدام بيانات وهمية:", error);
            this.lectures = this.generateSampleData();
        } finally {
            this.renderCalendar();
        }
    }

    generateSampleData() {
        const today = new Date();
        return Array.from({ length: 10 }).map((_, i) => ({
            id: i + 1,
            title: `محاضرة اختبار ${i+1}`,
            date: new Date(today.getFullYear(), today.getMonth(), today.getDate() + i).toISOString().split("T")[0],
            start_time: "09:00",
            end_time: "11:00",
            description: "محاضرة تجريبية",
            teacher: { user: { name: "أ. محمد" } },
            group: { id: 1, name: "المجموعة الأولى" },
            is_today: i === 0,
            has_started: false,
            attendance_summary: { present: 15, total: 20, absent: 3, late: 2 }
        }));
    }

    // ------------------ العرض ------------------
    renderCalendar() {
        const grid = document.getElementById("calendarGrid");
        const currentPeriod = document.getElementById("currentPeriod");
        if (!grid || !currentPeriod) return;

        if (this.currentView === "month") this.renderMonthView(grid, currentPeriod);
        if (this.currentView === "week") this.renderWeekView(grid, currentPeriod);
        if (this.currentView === "today") this.renderDayView(grid, currentPeriod);

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
        this.dayNames.forEach(d => html += `<div class="calendar-day-header">${d}</div>`);

        const current = new Date(startDate);
        for (let i = 0; i < 42; i++) {
            const isCurrentMonth = current.getMonth() === month;
            const isToday = this.isToday(current);
            const dayLectures = this.getLecturesForDate(current);
            html += `
                <div class="calendar-day ${!isCurrentMonth ? "other-month" : ""} ${isToday ? "today" : ""}">
                    <div class="day-number">${current.getDate()}</div>
                    ${dayLectures.map(l => this.renderLectureItem(l)).join("")}
                </div>`;
            current.setDate(current.getDate() + 1);
        }

        grid.innerHTML = html;
    }

    renderWeekView(grid, currentPeriod) {
        const startOfWeek = new Date(this.currentDate);
        startOfWeek.setDate(this.currentDate.getDate() - this.currentDate.getDay());
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);

        currentPeriod.textContent = `${startOfWeek.getDate()} - ${endOfWeek.getDate()} ${this.monthNames[startOfWeek.getMonth()]} ${startOfWeek.getFullYear()}`;

        let html = "";
        this.dayNames.forEach(d => html += `<div class="calendar-day-header">${d}</div>`);

        const current = new Date(startOfWeek);
        for (let i = 0; i < 7; i++) {
            const isToday = this.isToday(current);
            const dayLectures = this.getLecturesForDate(current);
            html += `
                <div class="calendar-day ${isToday ? "today" : ""}">
                    <div class="day-number">${current.getDate()}</div>
                    ${dayLectures.map(l => this.renderLectureItem(l)).join("")}
                </div>`;
            current.setDate(current.getDate() + 1);
        }
        grid.innerHTML = html;
    }

    renderDayView(grid, currentPeriod) {
        const d = this.currentDate;
        currentPeriod.textContent = `${d.getDate()} ${this.monthNames[d.getMonth()]} ${d.getFullYear()}`;
        const dayLectures = this.getLecturesForDate(d);

        let html = '<div class="calendar-day-header">اليوم</div>';
        html += `<div class="calendar-day today" style="grid-column:1/-1;min-height:200px;">${dayLectures.map(l => this.renderDetailedLectureItem(l)).join("")}</div>`;
        grid.innerHTML = html;
    }

    renderLectureItem(l) {
        const color = this.groupColors[(l.group.id - 1) % this.groupColors.length];
        return `<div class="lecture-item" style="background:${color}" data-lecture='${JSON.stringify(l)}'>${l.start_time} ${l.title}</div>`;
    }

    renderDetailedLectureItem(l) {
        return `<div class="p-3 mb-2 text-sm bg-blue-100 rounded">${l.start_time} - ${l.end_time} | ${l.title}<br><span class="text-gray-600">${l.group.name} • ${l.teacher.user.name}</span></div>`;
    }

    renderTodayLectures() {
        const todayLectures = this.getLecturesForDate(new Date());
        const container = document.getElementById("todayLectures");
        if (!container) return;
        container.innerHTML = todayLectures.length
            ? todayLectures.map(l => `<li>${l.start_time} - ${l.end_time} | ${l.title} (${l.group.name})</li>`).join("")
            : "<li>لا توجد محاضرات اليوم</li>";
    }

    // ------------------ الأدوات ------------------
    getLecturesForDate(d) {
        return this.lectures.filter(l => l.date === d.toISOString().split("T")[0]);
    }
    isToday(d) { return d.toDateString() === new Date().toDateString(); }

    showTooltip(e) {
        const lecture = JSON.parse(e.target.dataset.lecture);
        this.tooltip.innerHTML = `<strong>${lecture.title}</strong><br>⏰ ${lecture.start_time} - ${lecture.end_time}<br>👨‍🏫 ${lecture.teacher.user.name}`;
        this.tooltip.classList.remove("hidden");
        this.tooltip.style.top = (e.pageY + 10) + "px";
        this.tooltip.style.left = (e.pageX + 10) + "px";
    }
    hideTooltip() { this.tooltip?.classList.add("hidden"); }

    openModal(l) {
        const modal = document.getElementById("lectureModal");
        const content = document.getElementById("lectureModalContent");
        content.innerHTML = `<p><strong>${l.title}</strong></p><p>المعلم: ${l.teacher.user.name}</p><p>المجموعة: ${l.group.name}</p>`;
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    }

    // ------------------ التنقل ------------------
    previousPeriod() {
        if (this.currentView === "month") this.currentDate.setMonth(this.currentDate.getMonth() - 1);
        if (this.currentView === "week") this.currentDate.setDate(this.currentDate.getDate() - 7);
        if (this.currentView === "today") this.currentDate.setDate(this.currentDate.getDate() - 1);
        this.renderCalendar();
    }
    nextPeriod() {
        if (this.currentView === "month") this.currentDate.setMonth(this.currentDate.getMonth() + 1);
        if (this.currentView === "week") this.currentDate.setDate(this.currentDate.getDate() + 7);
        if (this.currentView === "today") this.currentDate.setDate(this.currentDate.getDate() + 1);
        this.renderCalendar();
    }
}

// ------------------ تشغيل ------------------
document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("calendarGrid")) {
        window.lecturesCalendar = new LecturesCalendar();
        console.log("📅 تم تحميل تقويم المحاضرات");
    }
});
</script>

@endpush
