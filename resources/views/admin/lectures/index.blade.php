{{-- resources/views/admin/lectures/index.blade.php --}}
@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle = 'البوابة الإدارية';
$pageTitle = 'إدارة المحاضرات والجدولة';
$pageDescription = 'إدارة المحاضرات والسلاسل والامتحانات النهائية';
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
<style>
    [x-cloak] {
        display: none !important;
    }

    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* FullCalendar Customizations */
    .fc-toolbar-title {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
    }

    .fc-button {
        background: #2778E5 !important;
        border-color: #2778E5 !important;
        font-size: 0.875rem !important;
    }

    .fc-button:hover {
        background: #1e6bb3 !important;
    }

    .fc-today-button {
        background: #EE8100 !important;
        border-color: #EE8100 !important;
    }

    /* Custom lecture types colors */
    .lecture-normal {
        background-color: #2778E5 !important;
    }

    .lecture-exam {
        background-color: #EE8100 !important;
    }

    .lecture-review {
        background-color: #28A745 !important;
    }

    .lecture-activity {
        background-color: #FFC107 !important;
        color: #000 !important;
    }

    .lecture-final-exam {
        background-color: #DC3545 !important;
    }

    .lecture-cancelled {
        background-color: #6C757D !important;
        text-decoration: line-through !important;
        opacity: 0.7 !important;
    }

    .lecture-rescheduled {
        background-color: #6F42C1 !important;
        border: 2px dashed #fff !important;
    }

    .tab-button.active {
        background-color: #2778E5;
        color: white;
        border-bottom: 3px solid #EE8100;
    }

    .time-slot {
        min-height: 60px;
        border: 1px solid #e5e7eb;
    }

    .conflict-warning {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
    }
</style>
@endpush

{{-- تمرير البيانات للـ JavaScript --}}
<script>
    window.initialStats = @json($stats ?? []);
    window.routes = {
        lecturesData: '{{ route("admin.lectures.data") }}',
        calendarData: '{{ route("admin.lectures.calendar-data") }}',
        storeLecture: '{{ route("admin.lectures.store") }}',
        updateLecture: '{{ url("/admin/lectures") }}',
        destroyLecture: '{{ url("/admin/lectures") }}',
        createSeries: '{{ route("admin.lectures.series.store") }}',
        createFinalExam: '{{ route("admin.lectures.final-exam.store") }}',
        rescheduleLecture: '{{ url("/admin/lectures") }}',
        cancelLecture: '{{ url("/admin/lectures") }}',
        searchLectures: '{{ route("admin.lectures.search") }}',

        groupsData: '{{ route("admin.groups.data") }}',
        getSubjects: '{{ route("admin.groups.subjects.available") }}',
        getGroupSubjects: '{{ route("admin.groups.subjects.for-lectures") }}',
        getTeachers: '{{ route("admin.groups.teachers.available") }}',

        // Routes للبيانات المساعدة

        // availableSubjects: '{{ route("admin.groups.subjects.available") }}',
    };
</script>

@section('content')
<div class="min-h-screen p-6 bg-gray-50" x-data="lecturesManager()" x-init="loadData()">

    <!-- Loading State -->
    <div x-show="loading" class="flex items-center justify-center p-8">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 border-4 border-blue-500 rounded-full border-t-transparent animate-spin"></div>
            <span class="text-lg text-gray-600">جاري تحميل البيانات...</span>
        </div>
    </div>

    <!-- Content -->
    <div x-show="!loading" x-cloak>
        <!-- إحصائيات سريعة -->
        <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-6">
            <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="text-xl text-blue-600 fas fa-calendar-alt"></i>
                    </div>
                    <div class="mr-4">
                        <p class="text-sm text-gray-600">إجمالي المحاضرات</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.total_lectures">0</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="text-xl text-green-600 fas fa-calendar-day"></i>
                    </div>
                    <div class="mr-4">
                        <p class="text-sm text-gray-600">محاضرات اليوم</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.today_lectures">0</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="text-xl text-purple-600 fas fa-calendar-week"></i>
                    </div>
                    <div class="mr-4">
                        <p class="text-sm text-gray-600">هذا الأسبوع</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.this_week_lectures">0</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <i class="text-xl text-yellow-600 fas fa-redo-alt"></i>
                    </div>
                    <div class="mr-4">
                        <p class="text-sm text-gray-600">السلاسل النشطة</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.active_series">0</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center">
                    <div class="p-3 bg-red-100 rounded-lg">
                        <i class="text-xl text-red-600 fas fa-graduation-cap"></i>
                    </div>
                    <div class="mr-4">
                        <p class="text-sm text-gray-600">امتحانات قادمة</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.upcoming_exams">0</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-lg">
                        <i class="text-xl text-orange-600 fas fa-calendar-times"></i>
                    </div>
                    <div class="mr-4">
                        <p class="text-sm text-gray-600">هذا الشهر</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.this_month_lectures">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- أزرار الإجراءات الرئيسية -->
        <div class="flex flex-wrap gap-4 mb-8">
            <button @click="showCreateLectureModal = true"
                class="flex items-center gap-2 px-6 py-3 font-medium text-white transition-all duration-200 bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 hover:shadow-lg">
                <i class="fas fa-plus"></i>
                إضافة محاضرة
            </button>

            <button @click="showCreateSeriesModal = true"
                class="flex items-center gap-2 px-6 py-3 font-medium text-white transition-all duration-200 bg-purple-600 rounded-lg shadow-md hover:bg-purple-700 hover:shadow-lg">
                <i class="fas fa-redo-alt"></i>
                إنشاء سلسلة متكررة
            </button>

            <button @click="showFinalExamModal = true"
                class="flex items-center gap-2 px-6 py-3 font-medium text-white transition-all duration-200 bg-red-600 rounded-lg shadow-md hover:bg-red-700 hover:shadow-lg">
                <i class="fas fa-graduation-cap"></i>
                امتحان نهائي
            </button>

            <button @click="refreshData()"
                class="flex items-center gap-2 px-6 py-3 font-medium text-white transition-all duration-200 bg-green-600 rounded-lg shadow-md hover:bg-green-700 hover:shadow-lg">
                <i class="fas fa-sync-alt" :class="{ 'animate-spin': refreshing }"></i>
                تحديث البيانات
            </button>

            <button onclick="window.print()"
                class="flex items-center gap-2 px-6 py-3 font-medium text-white transition-all duration-200 bg-gray-600 rounded-lg shadow-md hover:bg-gray-700 hover:shadow-lg">
                <i class="fas fa-print"></i>
                طباعة الجدول
            </button>
        </div>

        <!-- التبويبات -->
        <div class="mb-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 space-x-reverse">
                    <button @click="activeTab = 'dashboard'"
                        :class="activeTab === 'dashboard' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
                        <i class="fas fa-tachometer-alt ml-2"></i>
                        لوحة التحكم
                    </button>

                    <button @click="activeTab = 'calendar'"
                        :class="activeTab === 'calendar' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
                        <i class="fas fa-calendar ml-2"></i>
                        التقويم
                    </button>

                    <button @click="activeTab = 'list'"
                        :class="activeTab === 'list' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
                        <i class="fas fa-list ml-2"></i>
                        قائمة المحاضرات
                    </button>

                    <button @click="activeTab = 'series'"
                        :class="activeTab === 'series' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
                        <i class="fas fa-redo-alt ml-2"></i>
                        السلاسل النشطة
                    </button>

                    <button @click="activeTab = 'reports'"
                        :class="activeTab === 'reports' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="py-2 px-1 border-b-2 font-medium text-sm whitespace-nowrap">
                        <i class="fas fa-chart-bar ml-2"></i>
                        التقارير
                    </button>
                </nav>
            </div>
        </div>

        <!-- محتوى التبويبات -->

        <!-- لوحة التحكم -->
        <div x-show="activeTab === 'dashboard'" x-cloak>
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- المحاضرات القادمة -->
                <div class="lg:col-span-2">
                    <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-800">المحاضرات القادمة</h3>
                            <button @click="activeTab = 'list'" class="text-sm text-blue-600 hover:text-blue-800">
                                عرض الكل
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="lecture in upcomingLectures.slice(0, 5)" :key="lecture.id">
                                <div
                                    class="flex items-center p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                                    <div class="flex-shrink-0 ml-4">
                                        <div :class="{
                                                'bg-blue-100 text-blue-600': lecture.type === 'lecture',
                                                'bg-red-100 text-red-600': lecture.type === 'final_exam',
                                                'bg-orange-100 text-orange-600': lecture.type === 'exam',
                                                'bg-green-100 text-green-600': lecture.type === 'review'
                                            }" class="p-3 rounded-lg">
                                            <i :class="{
                                                    'fas fa-chalkboard-teacher': lecture.type === 'lecture',
                                                    'fas fa-graduation-cap': lecture.type === 'final_exam',
                                                    'fas fa-clipboard-check': lecture.type === 'exam',
                                                    'fas fa-book-reader': lecture.type === 'review'
                                                }"></i>
                                        </div>
                                    </div>

                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900" x-text="lecture.title"></h4>
                                        <div class="mt-1 text-sm text-gray-600">
                                            <span x-text="lecture.group_name"></span> •
                                            <span x-text="lecture.teacher_name"></span>
                                        </div>
                                        <div class="mt-1 text-sm text-gray-500">
                                            <span x-text="formatDate(lecture.date)"></span> •
                                            <span x-text="`${lecture.start_time} - ${lecture.end_time}`"></span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <button @click="editLecture(lecture)"
                                            class="p-2 text-blue-600 rounded-lg hover:bg-blue-50" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="rescheduleLecture(lecture)"
                                            class="p-2 text-yellow-600 rounded-lg hover:bg-yellow-50" title="تأجيل">
                                            <i class="fas fa-clock"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div x-show="upcomingLectures.length === 0" class="py-8 text-center text-gray-500">
                                <i class="mb-3 text-gray-300 fas fa-calendar-times fa-3x"></i>
                                <p>لا توجد محاضرات قادمة</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- التقويم المصغر -->
                <div class="space-y-8">
                    <!-- تقويم الشهر -->
                    <div class="py-6 px-4 bg-white border border-gray-100 shadow-sm rounded-xl">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">تقويم سريع</h3>
                        <div id="mini-calendar"></div>
                    </div>

                    <!-- الامتحانات القادمة -->
                    <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">امتحانات قادمة</h3>
                        <div class="space-y-3">
                            <template x-for="exam in upcomingExams.slice(0, 3)" :key="exam.id">
                                <div class="flex items-center p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="flex-shrink-0 ml-3">
                                        <div class="p-2 bg-red-100 rounded-lg">
                                            <i class="text-red-600 fas fa-graduation-cap"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-red-900" x-text="exam.title"></p>
                                        <p class="text-xs text-red-700" x-text="formatDate(exam.date)"></p>
                                    </div>
                                </div>
                            </template>

                            <div x-show="upcomingExams.length === 0" class="py-4 text-center text-gray-500">
                                <p class="text-sm">لا توجد امتحانات قادمة</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- التقويم -->
        <div x-show="activeTab === 'calendar'" x-cloak>
            <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">التقويم التفاعلي</h3>

                    <!-- فلاتر التقويم -->
                    <div class="flex items-center gap-4">
                        <select x-model="calendarFilters.group" @change="filterCalendar()"
                            class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">جميع المجموعات</option>
                            <template x-for="group in availableGroups" :key="group.id">
                                <option :value="group.id" x-text="group.name"></option>
                            </template>
                        </select>

                        <select x-model="calendarFilters.teacher" @change="filterCalendar()"
                            class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">جميع المدرسين</option>
                            <template x-for="teacher in lectureAvailableTeachers" :key="teacher.id">
                                <option :value="teacher.id" x-text="teacher.display_name"></option>
                            </template>
                        </select>

                        <select x-model="calendarFilters.type" @change="filterCalendar()"
                            class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">جميع الأنواع</option>
                            <option value="lecture">محاضرات</option>
                            <option value="exam">امتحانات</option>
                            <option value="review">مراجعات</option>
                            <option value="final_exam">امتحانات نهائية</option>
                        </select>
                    </div>
                </div>

                <!-- مفاتيح الألوان -->
                <div class="flex flex-wrap gap-4 mb-6">
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-blue-500 rounded"></div>
                        <span class="text-sm text-gray-600">محاضرات عادية</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-orange-500 rounded"></div>
                        <span class="text-sm text-gray-600">امتحانات</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-green-500 rounded"></div>
                        <span class="text-sm text-gray-600">مراجعات</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-red-500 rounded"></div>
                        <span class="text-sm text-gray-600">امتحانات نهائية</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-gray-500 rounded"></div>
                        <span class="text-sm text-gray-600">ملغاة</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 bg-purple-500 rounded border-2 border-dashed border-white"></div>
                        <span class="text-sm text-gray-600">مؤجلة</span>
                    </div>
                </div>

                <div id="fullcalendar"></div>
            </div>
        </div>

        <!-- قائمة المحاضرات -->
        <div x-show="activeTab === 'list'" x-cloak>
            <div class="mb-6 p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                <!-- فلاتر البحث -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                    <input type="text" x-model="searchTerm" placeholder="البحث في المحاضرات..."
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                    <input type="date" x-model="filters.date_from"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                    <input type="date" x-model="filters.date_to"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">

                    <select x-model="filters.status"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">جميع الحالات</option>
                        <option value="scheduled">مجدولة</option>
                        <option value="completed">مكتملة</option>
                        <option value="rescheduled">مؤجلة</option>
                        <option value="cancelled">ملغاة</option>
                    </select>

                    <select x-model="filters.type"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">جميع الأنواع</option>
                        <option value="lecture">محاضرة</option>
                        <option value="exam">امتحان</option>
                        <option value="review">مراجعة</option>
                        <option value="final_exam">امتحان نهائي</option>
                    </select>
                </div>

                <div class="flex justify-between items-center mt-4">
                    <button @click="applyFilters()"
                        class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-search ml-2"></i>
                        تطبيق الفلاتر
                    </button>

                    <button @click="resetFilters()"
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-undo ml-2"></i>
                        إعادة تعيين
                    </button>
                </div>
            </div>

            <!-- جدول المحاضرات -->
            <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">قائمة المحاضرات</h3>
                        <span class="text-sm text-gray-600" x-text="`عرض ${filteredLectures.length} محاضرة`"></span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-4 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                                    المحاضرة</th>
                                <th
                                    class="px-4 py-4 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                                    التاريخ والوقت</th>
                                <th
                                    class="px-4 py-4 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                                    المدرس</th>
                                <th
                                    class="px-4 py-4 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                                    المجموعة</th>
                                <th
                                    class="px-4 py-4 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                                    الحالة</th>
                                <th
                                    class="px-6 py-4 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                                    الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="lecture in filteredLectures" :key="lecture.id">
                                <tr class="transition-colors hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div :class="{
                                                    'bg-blue-100 text-blue-600': lecture.type === 'lecture',
                                                    'bg-red-100 text-red-600': lecture.type === 'final_exam',
                                                    'bg-orange-100 text-orange-600': lecture.type === 'exam',
                                                    'bg-green-100 text-green-600': lecture.type === 'review'
                                                }" class="p-2 ml-3 rounded-lg">
                                                <i :class="{
                                                        'fas fa-chalkboard-teacher': lecture.type === 'lecture',
                                                        'fas fa-graduation-cap': lecture.type === 'final_exam',
                                                        'fas fa-clipboard-check': lecture.type === 'exam',
                                                        'fas fa-book-reader': lecture.type === 'review'
                                                    }"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900" x-text="lecture.title">
                                                </div>
                                                <div class="text-sm text-gray-500" x-text="getTypeLabel(lecture.type)">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-900" x-text="formatDate(lecture.date)"></div>
                                        <div class="text-sm text-gray-500"
                                            x-text="`${lecture.start_time} - ${lecture.end_time}`"></div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-gray-900" x-text="lecture.teacher_name"></span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="text-sm text-gray-900" x-text="lecture.group_name"></span>
                                        <span class="text-xs text-gray-500"
                                            x-text="`(${lecture.students_count} طالب)`"></span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span :class="{
                                                'bg-green-100 text-green-800': lecture.status === 'scheduled',
                                                'bg-blue-100 text-blue-800': lecture.status === 'completed',
                                                'bg-purple-100 text-purple-800': lecture.status === 'rescheduled',
                                                'bg-red-100 text-red-800': lecture.status === 'cancelled'
                                            }" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            <span x-text="getStatusLabel(lecture.status)"></span>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <button @click="viewLecture(lecture)"
                                                class="p-2 text-blue-600 rounded-lg hover:bg-blue-50" title="عرض">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button @click="editLecture(lecture)"
                                                class="p-2 text-green-600 rounded-lg hover:bg-green-50" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button @click="rescheduleLecture(lecture)"
                                                class="p-2 text-yellow-600 rounded-lg hover:bg-yellow-50" title="تأجيل">
                                                <i class="fas fa-clock"></i>
                                            </button>
                                            <button @click="cancelLecture(lecture)"
                                                class="p-2 text-red-600 rounded-lg hover:bg-red-50" title="إلغاء">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div x-show="filteredLectures.length === 0" class="py-12 text-center">
                        <i class="mb-4 text-gray-300 fas fa-calendar-times fa-3x"></i>
                        <h3 class="text-lg font-medium text-gray-900">لا توجد محاضرات</h3>
                        <p class="text-gray-600">لم يتم العثور على محاضرات تطابق معايير البحث</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- السلاسل النشطة -->
        <div x-show="activeTab === 'series'" x-cloak>
            <div class="space-y-6">
                <template x-for="series in activeSeries" :key="series.series_id">
                    <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800" x-text="series.title"></h3>
                                <p class="text-sm text-gray-600"
                                    x-text="`${series.group_name} • ${series.teacher_name}`"></p>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="px-3 py-1 text-sm text-purple-800 bg-purple-100 rounded-full">
                                    سلسلة نشطة
                                </span>
                                <button @click="endSeries(series)"
                                    class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700">
                                    إنهاء السلسلة
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div class="p-4 bg-blue-50 rounded-lg">
                                <p class="text-sm text-blue-600">إجمالي المحاضرات</p>
                                <p class="text-xl font-bold text-blue-800" x-text="series.total_lectures">0</p>
                            </div>
                            <div class="p-4 bg-green-50 rounded-lg">
                                <p class="text-sm text-green-600">مكتملة</p>
                                <p class="text-xl font-bold text-green-800" x-text="series.completed_lectures">0</p>
                            </div>
                            <div class="p-4 bg-yellow-50 rounded-lg">
                                <p class="text-sm text-yellow-600">متبقية</p>
                                <p class="text-xl font-bold text-yellow-800" x-text="series.remaining_lectures">0</p>
                            </div>
                            <div class="p-4 bg-purple-50 rounded-lg">
                                <p class="text-sm text-purple-600">مدة السلسلة</p>
                                <p class="text-xl font-bold text-purple-800" x-text="`${series.weeks_count} أسبوع`">0
                                </p>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="activeSeries.length === 0" class="py-12 text-center">
                    <i class="mb-4 text-gray-300 fas fa-redo-alt fa-3x"></i>
                    <h3 class="text-lg font-medium text-gray-900">لا توجد سلاسل نشطة</h3>
                    <p class="text-gray-600">لم يتم إنشاء أي سلاسل محاضرات متكررة بعد</p>
                    <button @click="showCreateSeriesModal = true"
                        class="px-6 py-3 mt-4 text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                        إنشاء أول سلسلة
                    </button>
                </div>
            </div>
        </div>

        <!-- التقارير -->
        <div x-show="activeTab === 'reports'" x-cloak>
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- تقرير أداء المدرسين -->
                <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                    <h3 class="mb-6 text-lg font-semibold text-gray-800">أداء المدرسين</h3>
                    <div class="space-y-4">
                        <template x-for="teacher in teacherReports" :key="teacher.id">
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-900" x-text="teacher.name"></h4>
                                    <p class="text-sm text-gray-600" x-text="`${teacher.total_lectures} محاضرة`"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-green-600"
                                        x-text="`${teacher.attendance_rate}% حضور`"></p>
                                    <p class="text-xs text-gray-500" x-text="`${teacher.completed_lectures} مكتملة`">
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- تقرير أداء المجموعات -->
                <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                    <h3 class="mb-6 text-lg font-semibold text-gray-800">أداء المجموعات</h3>
                    <div class="space-y-4">
                        <template x-for="group in groupReports" :key="group.id">
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-900" x-text="group.name"></h4>
                                    <p class="text-sm text-gray-600" x-text="`${group.total_lectures} محاضرة`"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-blue-600"
                                        x-text="`${group.average_attendance}% متوسط الحضور`"></p>
                                    <p class="text-xs text-gray-500" x-text="`${group.students_count} طالب`"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal إضافة محاضرة -->
    <div x-show="showCreateLectureModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="showCreateLectureModal = false">
        <div class="w-full max-w-2xl mx-4 bg-white shadow-2xl rounded-2xl fade-in max-h-[90vh] overflow-y-auto"
            @click.stop>
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800">إضافة محاضرة جديدة</h3>
                    <button @click="showCreateLectureModal = false"
                        class="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form @submit.prevent="createLecture()" class="p-6 space-y-6">
                <!-- معلومات أساسية -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">عنوان المحاضرة *</label>
                        <input type="text" x-model="newLecture.title" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="مثال: مراجعة الجبر المتقدم">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">نوع المحاضرة *</label>
                        <select x-model="newLecture.type" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="lecture">محاضرة عادية</option>
                            <option value="exam">امتحان</option>
                            <option value="review">مراجعة</option>
                            <option value="activity">نشاط</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">التاريخ *</label>
                        <input type="date" x-model="newLecture.date" required
                            :min="new Date().toISOString().split('T')[0]"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">المجموعة *</label>
                        <select x-model="newLecture.group_id" required @change="updateAvailableTeachersAndSubjects()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">اختر المجموعة</option>
                            <template x-for="group in availableGroups" :key="group.id">
                                <option :value="group.id" x-text="group.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">وقت البداية *</label>
                        <input type="time" x-model="newLecture.start_time" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">وقت النهاية *</label>
                        <input type="time" x-model="newLecture.end_time" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">المدرس *</label>
                        <select x-model="newLecture.teacher_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">اختر المدرس</option>
                            <template x-for="teacher in lectureAvailableTeachers" :key="teacher.id">
                                <option :value="teacher.id" x-text="teacher.display_name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">المادة</label>

                        <!-- عرض المواد المفلترة حسب المجموعة -->
                        <select x-model="newLecture.subject_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :disabled="!newLecture.group_id">
                            <option value="">اختر المادة</option>
                            <!-- ✅ استخدام lectureFilteredSubjects بدلاً من availableSubjects -->
                            <template x-for="subject in lectureFilteredSubjects" :key="subject.id">
                                <option :value="subject.id" x-text="subject.name || subject.display_name"></option>
                            </template>
                        </select>

                        <!-- رسائل المساعدة -->
                        <div x-show="!newLecture.group_id" class="mt-2 text-sm text-gray-500">
                            <i class="fas fa-arrow-up ml-1"></i>
                            يرجى اختيار المجموعة أولاً
                        </div>

                        <div x-show="newLecture.group_id && loadingSubjects" class="mt-2 text-sm text-blue-600">
                            <i class="fas fa-spinner fa-spin ml-1"></i>
                            جاري تحميل المواد...
                        </div>

                        <div x-show="newLecture.group_id && !loadingSubjects && lectureFilteredSubjects.length === 0"
                            class="mt-2 text-sm text-amber-600">
                            <i class="fas fa-info-circle ml-1"></i>
                            لا توجد مواد مرتبطة بهذه المجموعة
                        </div>

                        <div x-show="newLecture.group_id && !loadingSubjects && lectureFilteredSubjects.length > 0"
                            class="mt-2 text-sm text-green-600">
                            <i class="fas fa-check-circle ml-1"></i>
                            <span x-text="`${lectureFilteredSubjects.length} مادة متاحة`"></span>
                        </div>
                    </div>

                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">وصف المحاضرة</label>
                    <textarea x-model="newLecture.description" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="وصف مختصر عن محتوى المحاضرة..."></textarea>
                </div>

                <!-- تحذير تضارب الأوقات -->
                <div x-show="timeConflicts.length > 0" class="p-4 border border-red-200 rounded-lg conflict-warning">
                    <h4 class="font-medium text-red-800">تحذير: يوجد تضارب في الأوقات!</h4>
                    <ul class="mt-2 text-sm text-red-700">
                        <template x-for="conflict in timeConflicts" :key="conflict.id">
                            <li
                                x-text="`• ${conflict.title} - ${conflict.date} ${conflict.start_time}-${conflict.end_time}`">
                            </li>
                        </template>
                    </ul>
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                    <button type="button" @click="showCreateLectureModal = false"
                        class="px-6 py-3 font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" :disabled="creating || timeConflicts.length > 0"
                        class="flex items-center gap-2 px-6 py-3 font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        <i class="fas fa-plus" :class="{ 'animate-spin fa-spinner': creating }"></i>
                        <span x-text="creating ? 'جاري الإنشاء...' : 'إنشاء المحاضرة'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal إنشاء سلسلة متكررة -->
    <div x-show="showCreateSeriesModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="showCreateSeriesModal = false">
        <div class="w-full max-w-3xl mx-4 bg-white shadow-2xl rounded-2xl fade-in max-h-[90vh] overflow-y-auto"
            @click.stop>
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800">إنشاء سلسلة محاضرات متكررة</h3>
                    <button @click="showCreateSeriesModal = false"
                        class="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="mt-2 text-sm text-gray-600">إنشاء محاضرات متكررة أسبوعياً حتى تحديد امتحان نهائي</p>
            </div>

            <form @submit.prevent="createSeries()" class="p-6 space-y-6">
                <!-- معلومات السلسلة -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">عنوان السلسلة *</label>
                        <input type="text" x-model="newSeries.title" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="مثال: محاضرات الرياضيات - الفصل الأول">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">تاريخ البداية *</label>
                        <input type="date" x-model="newSeries.start_date" required
                            :min="new Date().toISOString().split('T')[0]"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">المجموعة *</label>
                        <select x-model="newSeries.group_id" required
                            @change="updateAvailableTeachersAndSubjectsForSeries()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">اختر المجموعة</option>
                            <template x-for="group in availableGroups" :key="group.id">
                                <option :value="group.id" x-text="group.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">المدرس *</label>
                        <select x-model="newSeries.teacher_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">اختر المدرس</option>
                            <template x-for="teacher in seriesAvailableTeachers" :key="teacher.id">
                                <option :value="teacher.id" x-text="teacher.display_name"></option>
                            </template>
                        </select>

                        <!-- رسائل المساعدة للمدرسين -->
                        <div x-show="!newSeries.group_id" class="mt-2 text-sm text-gray-500">
                            <i class="fas fa-arrow-up ml-1"></i>
                            يرجى اختيار المجموعة أولاً
                        </div>
                    </div>

                    <!-- ✅ إضافة حقل المادة -->
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">المادة *</label>
                        <select x-model="newSeries.subject_id" required
                            @change="updateAvailableTeachersAndSubjectsForSeries()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :disabled="!newSeries.group_id">
                            <option value="">اختر المادة</option>
                            <template x-for="subject in seriesFilteredSubjects" :key="subject.id">
                                <option :value="subject.id" x-text="subject.name || subject.display_name"></option>
                            </template>
                        </select>

                        <!-- رسائل المساعدة للمواد -->
                        <div x-show="!newSeries.group_id" class="mt-2 text-sm text-gray-500">
                            <i class="fas fa-arrow-up ml-1"></i>
                            يرجى اختيار المجموعة أولاً
                        </div>

                        <div x-show="newSeries.group_id && loadingSeriesSubjects" class="mt-2 text-sm text-blue-600">
                            <i class="fas fa-spinner fa-spin ml-1"></i>
                            جاري تحميل المواد...
                        </div>

                        <div x-show="newSeries.group_id && !loadingSeriesSubjects && seriesFilteredSubjects.length === 0"
                            class="mt-2 text-sm text-amber-600">
                            <i class="fas fa-info-circle ml-1"></i>
                            لا توجد مواد مرتبطة بهذه المجموعة
                        </div>

                        <div x-show="newSeries.group_id && !loadingSeriesSubjects && seriesFilteredSubjects.length > 0"
                            class="mt-2 text-sm text-green-600">
                            <i class="fas fa-check-circle ml-1"></i>
                            <span x-text="`${seriesFilteredSubjects.length} مادة متاحة`"></span>
                        </div>
                    </div>


                    {{-- <div class="mt-4 p-4 bg-gray-50 rounded-lg" style="border: 2px dashed #ccc;">
                        <h4 class="text-sm font-bold text-gray-700 mb-2">🔧 أدوات التصحيح المؤقتة:</h4>
                        <div class="flex gap-2">
                            <button type="button" @click="debugLectureData()"
                                class="px-3 py-1 text-xs bg-blue-500 text-white rounded">
                                تصحيح بيانات المحاضرة
                            </button>

                            <button type="button" @click="debugSeriesData()"
                                class="px-3 py-1 text-xs bg-purple-500 text-white rounded">
                                تصحيح بيانات السلسلة
                            </button>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">
                            اضغط هذه الأزرار واطلع على الـ console لرؤية حالة البيانات
                        </p>
                    </div> --}}



                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">وصف السلسلة</label>
                        <textarea x-model="newSeries.description" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="وصف مختصر عن محتوى السلسلة..."></textarea>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">وقت البداية *</label>
                        <input type="time" x-model="newSeries.start_time" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">وقت النهاية *</label>
                        <input type="time" x-model="newSeries.end_time" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                </div>

                <!-- اختيار أيام الأسبوع -->
                <div>
                    <label class="block mb-3 text-sm font-medium text-gray-700">أيام الأسبوع *</label>
                    <div class="grid grid-cols-7 gap-2">
                        <label
                            class="flex flex-col items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50"
                            :class="{ 'bg-blue-50 border-blue-300': newSeries.days.includes('0') }">
                            <input type="checkbox" value="0" x-model="newSeries.days" class="mb-2">
                            <span class="text-sm font-medium">الأحد</span>
                        </label>
                        <label
                            class="flex flex-col items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50"
                            :class="{ 'bg-blue-50 border-blue-300': newSeries.days.includes('1') }">
                            <input type="checkbox" value="1" x-model="newSeries.days" class="mb-2">
                            <span class="text-sm font-medium">الإثنين</span>
                        </label>
                        <label
                            class="flex flex-col items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50"
                            :class="{ 'bg-blue-50 border-blue-300': newSeries.days.includes('2') }">
                            <input type="checkbox" value="2" x-model="newSeries.days" class="mb-2">
                            <span class="text-sm font-medium">الثلاثاء</span>
                        </label>
                        <label
                            class="flex flex-col items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50"
                            :class="{ 'bg-blue-50 border-blue-300': newSeries.days.includes('3') }">
                            <input type="checkbox" value="3" x-model="newSeries.days" class="mb-2">
                            <span class="text-sm font-medium">الأربعاء</span>
                        </label>
                        <label
                            class="flex flex-col items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50"
                            :class="{ 'bg-blue-50 border-blue-300': newSeries.days.includes('4') }">
                            <input type="checkbox" value="4" x-model="newSeries.days" class="mb-2">
                            <span class="text-sm font-medium">الخميس</span>
                        </label>
                        <label
                            class="flex flex-col items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50"
                            :class="{ 'bg-blue-50 border-blue-300': newSeries.days.includes('5') }">
                            <input type="checkbox" value="5" x-model="newSeries.days" class="mb-2">
                            <span class="text-sm font-medium">الجمعة</span>
                        </label>
                        <label
                            class="flex flex-col items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50"
                            :class="{ 'bg-blue-50 border-blue-300': newSeries.days.includes('6') }">
                            <input type="checkbox" value="6" x-model="newSeries.days" class="mb-2">
                            <span class="text-sm font-medium">السبت</span>
                        </label>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">اختر الأيام التي ستتكرر فيها المحاضرات أسبوعياً</p>
                </div>



                <!-- معاينة السلسلة -->
                <div x-show="newSeries.days.length > 0" class="p-4 bg-blue-50 rounded-lg">
                    <h4 class="font-medium text-blue-800">معاينة السلسلة:</h4>
                    <p class="text-sm text-blue-700" x-text="`سيتم إنشاء ${newSeries.days.length} محاضرة أسبوعياً`"></p>
                    <p class="text-sm text-blue-600" x-text="`الأيام: ${getDaysNames(newSeries.days).join(', ')}`"></p>
                    <p class="text-sm text-blue-600" x-text="`الوقت: ${newSeries.start_time} - ${newSeries.end_time}`">
                    </p>
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                    <button type="button" @click="showCreateSeriesModal = false"
                        class="px-6 py-3 font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" :disabled="creatingSeries || newSeries.days.length === 0"
                        class="flex items-center gap-2 px-6 py-3 font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50">
                        <i class="fas fa-redo-alt" :class="{ 'animate-spin': creatingSeries }"></i>
                        <span x-text="creatingSeries ? 'جاري الإنشاء...' : 'إنشاء السلسلة'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal الامتحان النهائي -->
    <div x-show="showFinalExamModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="showFinalExamModal = false">
        <div class="w-full max-w-2xl mx-4 bg-white shadow-2xl rounded-2xl fade-in max-h-[90vh] overflow-y-auto"
            @click.stop>
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800">إنشاء امتحان نهائي</h3>
                    <button @click="showFinalExamModal = false"
                        class="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="mt-2 text-sm text-gray-600">سيؤدي إنشاء الامتحان النهائي إلى إنهاء السلسلة المتكررة</p>
            </div>

            <form @submit.prevent="createFinalExam()" class="p-6 space-y-6">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">السلسلة *</label>
                    <select x-model="finalExam.series_id" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">اختر السلسلة</option>
                        <template x-for="series in activeSeries" :key="series.series_id">
                            <option :value="series.series_id" x-text="`${series.title} - ${series.group_name}`">
                            </option>
                        </template>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">عنوان الامتحان *</label>
                        <input type="text" x-model="finalExam.title" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="مثال: الامتحان النهائي - الرياضيات">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">تاريخ الامتحان *</label>
                        <input type="date" x-model="finalExam.date" required
                            :min="new Date().toISOString().split('T')[0]"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">وقت البداية *</label>
                        <input type="time" x-model="finalExam.start_time" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">مدة الامتحان (بالدقائق) *</label>
                        <input type="number" x-model="finalExam.duration" required min="30" max="480"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="120">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">القاعة</label>
                        <input type="text" x-model="finalExam.room"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="القاعة الرئيسية">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">الدرجة الكاملة *</label>
                        <input type="number" x-model="finalExam.total_marks" required min="1" max="1000"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="100">
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">ملاحظات الامتحان</label>
                    <textarea x-model="finalExam.notes" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="ملاحظات مهمة للامتحان (آلة حاسبة مسموحة، كتاب مفتوح، إلخ)"></textarea>
                </div>

                <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                    <h4 class="font-medium text-red-800">تنبيه مهم:</h4>
                    <p class="text-sm text-red-700">إنشاء الامتحان النهائي سيؤدي إلى:</p>
                    <ul class="mt-2 text-sm text-red-600 list-disc list-inside">
                        <li>إيقاف التكرار التلقائي للمحاضرات</li>
                        <li>حذف جميع المحاضرات المجدولة بعد تاريخ الامتحان</li>
                        <li>إنهاء السلسلة رسمياً</li>
                    </ul>
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                    <button type="button" @click="showFinalExamModal = false"
                        class="px-6 py-3 font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" :disabled="creatingFinalExam"
                        class="flex items-center gap-2 px-6 py-3 font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50">
                        <i class="fas fa-graduation-cap" :class="{ 'animate-spin fa-spinner': creatingFinalExam }"></i>
                        <span x-text="creatingFinalExam ? 'جاري الإنشاء...' : 'إنشاء الامتحان النهائي'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal تأجيل المحاضرة -->
    <div x-show="showRescheduleModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="showRescheduleModal = false">
        <div class="w-full max-w-md mx-4 bg-white shadow-2xl rounded-2xl fade-in max-h-[90vh] overflow-y-auto"
            @click.stop>
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">تأجيل المحاضرة</h3>
                <p class="text-sm text-gray-600" x-text="`تأجيل: ${selectedLecture?.title}`"></p>
            </div>

            <form @submit.prevent="confirmReschedule()" class="p-6 space-y-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">التاريخ الجديد *</label>
                    <input type="date" x-model="rescheduleData.new_date" required
                        :min="new Date().toISOString().split('T')[0]"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">وقت البداية الجديد *</label>
                        <input type="time" x-model="rescheduleData.new_start_time" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">وقت النهاية الجديد *</label>
                        <input type="time" x-model="rescheduleData.new_end_time" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">سبب التأجيل *</label>
                    <textarea x-model="rescheduleData.reason" required rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="اذكر سبب تأجيل المحاضرة..."></textarea>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-gray-200">
                    <button type="button" @click="showRescheduleModal = false"
                        class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" :disabled="rescheduling"
                        class="px-4 py-2 text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 disabled:opacity-50">
                        <span x-text="rescheduling ? 'جاري التأجيل...' : 'تأجيل المحاضرة'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- رسائل التنبيه -->
    <div x-show="showAlert" x-cloak class="fixed z-50 transform -translate-x-1/2 top-4 left-1/2 fade-in">
        <div :class="{
                'bg-green-100 border-green-500 text-green-700': alertType === 'success',
                'bg-red-100 border-red-500 text-red-700': alertType === 'error',
                'bg-yellow-100 border-yellow-500 text-yellow-700': alertType === 'warning'
            }" class="max-w-md p-4 border-r-4 rounded-lg shadow-lg">
            <div class="flex items-center">
                <i :class="{
                        'fas fa-check-circle text-green-500': alertType === 'success',
                        'fas fa-exclamation-circle text-red-500': alertType === 'error',
                        'fas fa-exclamation-triangle text-yellow-500': alertType === 'warning'
                    }" class="ml-3"></i>
                <p x-text="alertMessage" class="font-medium"></p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    function lecturesManager() {

        return {
            // حالة التحميل
            loading: true,
            refreshing: false,
            creating: false,
            creatingSeries: false,
            creatingFinalExam: false,
            rescheduling: false,


            // البيانات
            lectures: [],
            activeSeries: [],
            upcomingLectures: [],
            upcomingExams: [],
            teacherReports: [],
            groupReports: [],
            availableGroups: [],
            availableSubjects: [],

            lectureAvailableTeachers: [],
            lectureFilteredSubjects: [],
            loadingLectureSubjects: false,

            seriesFilteredSubjects: [],
            seriesAvailableTeachers: [],
            loadingSeriesSubjects: false,
            timeConflicts: [],

            async updateSeriesTeachersAndSubjects() {
            // إعادة تعيين البيانات
            this.seriesAvailableTeachers = [];
            this.seriesFilteredSubjects = [];
            this.newSeries.teacher_id = '';
            this.newSeries.subject_id = '';

            if (!this.newSeries.group_id) {
            this.loadingSeriesSubjects = false;
            return;
            }

            try {
            this.loadingSeriesSubjects = true;

            const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            };

            // تحميل المدرسين والمواد بشكل متوازي
            const promises = [];

            // تحميل المدرسين
            if (window.routes.getTeachers) {
            promises.push(
            fetch(`${window.routes.getTeachers}?group_id=${this.newSeries.group_id}`, { headers })
            );
            }

            // تحميل المواد
            if (window.routes.getGroupSubjects) {
            promises.push(
            fetch(`${window.routes.getGroupSubjects}?group_id=${this.newSeries.group_id}`, { headers })
            );
            }

            const responses = await Promise.all(promises);

            // معالجة المدرسين
            if (responses[0] && responses[0].ok) {
            const teachersData = await responses[0].json();
            if (teachersData.success) {
            this.seriesAvailableTeachers = this.validateTeachersData(teachersData.teachers || []);
            console.log('Series teachers loaded:', this.seriesAvailableTeachers.length);
            } else {
            console.error('Series teachers API error:', teachersData.message);
            this.showAlertMessage('warning', 'تعذر تحميل المدرسين للسلسلة');
            }
            }

            // معالجة المواد
            if (responses[1] && responses[1].ok) {
            const subjectsData = await responses[1].json();
            if (subjectsData.success) {
            this.seriesFilteredSubjects = this.validateSubjectsData(subjectsData.subjects || []);
            console.log('Series subjects loaded:', this.seriesFilteredSubjects.length);

            if (this.seriesFilteredSubjects.length > 0) {
            console.log('✅ تم تحميل مواد السلسلة بنجاح للمجموعة');
            }
            } else {
            console.error('Series subjects API error:', subjectsData.message);
            this.showAlertMessage('warning', 'تعذر تحميل مواد السلسلة للمجموعة المختارة');
            }
            }

            } catch (error) {
            console.error('Error loading series group data:', error);
            this.showAlertMessage('error', 'حدث خطأ في تحميل بيانات السلسلة');
            } finally {
            this.loadingSeriesSubjects = false;
            }
            },

            // الإحصائيات
            stats: {
                total_lectures: window.initialStats.total_lectures || 0,
                today_lectures: window.initialStats.today_lectures || 0,
                this_week_lectures: window.initialStats.this_week_lectures || 0,
                this_month_lectures: window.initialStats.this_month_lectures || 0,
                active_series: window.initialStats.active_series || 0,
                upcoming_exams: window.initialStats.upcoming_exams || 0,
            },

            // المتغيرات
            activeTab: 'dashboard',
            searchTerm: '',

            // فلاتر
            filters: {
                date_from: '',
                date_to: '',
                group_id: '',
                teacher_id: '',
                subject_id: '',
                status: '',
                type: ''
            },

            calendarFilters: {
                group: '',
                teacher: '',
                type: ''
            },

            // Modals
            showCreateLectureModal: false,
            showCreateSeriesModal: false,
            showFinalExamModal: false,
            showRescheduleModal: false,

            // بيانات النماذج
            newLecture: {
                title: '',
                type: 'lecture',
                date: '',
                start_time: '',
                end_time: '',
                teacher_id: '',
                group_id: '',
                subject_id: '',
                description: ''
            },

            newSeries: {
                title: '',
                start_date: '',
                start_time: '',
                end_time: '',
                teacher_id: '',
                group_id: '',
                subject_id: '',
                days: [],
                description: ''
            },

            finalExam: {
                series_id: '',
                title: '',
                date: '',
                start_time: '',
                duration: 120,
                teacher_id: '',
                group_id: '',
                subject_id: '',
                room: '',
                total_marks: 100,
                notes: ''
            },

            rescheduleData: {
                new_date: '',
                new_start_time: '',
                new_end_time: '',
                reason: ''
            },

            selectedLecture: null,

            // التنبيهات
            showAlert: false,
            alertType: 'success',
            alertMessage: '',

            // Computed properties
            get filteredLectures() {
                return this.lectures.filter(lecture => {
                    const matchesSearch = lecture.title.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                        lecture.teacher_name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                        lecture.group_name.toLowerCase().includes(this.searchTerm.toLowerCase());

                    const matchesStatus = !this.filters.status || lecture.status === this.filters.status;
                    const matchesType = !this.filters.type || lecture.type === this.filters.type;

                    return matchesSearch && matchesStatus && matchesType;
                });
            },

            // تحميل البيانات
            async loadData() {
                this.loading = true;
                try {
                    await Promise.all([
                        this.loadLectures(),
                        this.loadActiveSeries(),
                        this.loadAvailableData()
                    ]);
                } catch (error) {
                    console.error('Error loading data:', error);
                    this.showAlertMessage('error', 'حدث خطأ في تحميل البيانات');
                } finally {
                    this.loading = false;
                }
            },

            async loadLectures() {
                try {
                    const response = await fetch(window.routes.lecturesData, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.lectures = data.lectures;
                        this.upcomingLectures = data.lectures
                            .filter(l => new Date(l.date + 'T' + l.start_time) >= new Date())
                            .sort((a, b) => new Date(a.date + 'T' + a.start_time) - new Date(b.date + 'T' + b.start_time));
                        this.upcomingExams = data.lectures
                            .filter(l => l.type === 'final_exam' && new Date(l.date) >= new Date());
                    }
                } catch (error) {
                    console.error('Error loading lectures:', error);
                    throw error;
                }
            },

            async loadActiveSeries() {
                // هنا ستكون دالة لتحميل السلاسل النشطة
                // سيتم تطويرها لاحقاً
                this.activeSeries = [];
            },

            async loadAvailableData() {
                const headers = {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };

                try {
                    // تحميل المجموعات
                    if (window.routes.groupsData) {
                        const groupsResponse = await fetch(window.routes.groupsData, { headers });
                        const groupsData = await groupsResponse.json();
                        if (groupsData.success) {
                            this.availableGroups = groupsData.groups;
                            console.log('Groups loaded:', this.availableGroups.length);
                        }
                    }

                    // تحميل جميع المواد (للاستخدام العام - مثل التقارير)
                    if (window.routes.getSubjects) {
                        const subjectsResponse = await fetch(window.routes.getSubjects, { headers });
                        const subjectsData = await subjectsResponse.json();
                        if (subjectsData.success) {
                            this.availableSubjects = subjectsData.subjects;
                            console.log('All subjects loaded:', this.availableSubjects.length);
                        }
                    }

                } catch (error) {
                    console.error('Error loading available data:', error);
                    this.showAlertMessage('error', 'حدث خطأ في تحميل البيانات المساعدة');
                }
            },

            // تحديث المدرسين والمواد حسب المجموعة المختارة
            async updateAvailableTeachersAndSubjects() {
                // لا نعيد تعيين القيم المختارة إلا إذا تغيرت المجموعة فقط
                const currentGroupId = this.newLecture.group_id;

                if (!currentGroupId) {
                // فقط عند عدم اختيار مجموعة نفرغ البيانات
                this.lectureAvailableTeachers = [];
                this.lectureFilteredSubjects = [];
                this.newLecture.teacher_id = '';
                this.newLecture.subject_id = '';
                this.loadingLectureSubjects = false;
                return;
                }

                try {
                this.loadingLectureSubjects = true;

                const headers = {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };

                // تحميل البيانات بالتوازي
                const promises = [];

                // المدرسين
                if (window.routes.getTeachers) {
                promises.push(
                fetch(`${window.routes.getTeachers}?group_id=${currentGroupId}`, { headers })
                );
                }

                // المواد
                if (window.routes.getGroupSubjects) {
                promises.push(
                fetch(`${window.routes.getGroupSubjects}?group_id=${currentGroupId}`, { headers })
                );
                }

                const responses = await Promise.all(promises);

                // معالجة المدرسين
                if (responses[0] && responses[0].ok) {
                const teachersData = await responses[0].json();
                if (teachersData.success) {
                this.lectureAvailableTeachers = this.validateTeachersData(teachersData.teachers || []);
                console.log('Lecture teachers loaded:', this.lectureAvailableTeachers.length);
                } else {
                console.error('Teachers API error:', teachersData.message);
                this.showAlertMessage('warning', 'تعذر تحميل المدرسين');
                }
                }

                // معالجة المواد
                if (responses[1] && responses[1].ok) {
                const subjectsData = await responses[1].json();
                if (subjectsData.success) {
                this.lectureFilteredSubjects = this.validateSubjectsData(subjectsData.subjects || []);
                console.log('Lecture subjects loaded:', this.lectureFilteredSubjects.length);
                } else {
                console.error('Subjects API error:', subjectsData.message);
                this.showAlertMessage('warning', 'تعذر تحميل المواد للمجموعة المختارة');
                }
                }

                } catch (error) {
                console.error('Error loading lecture group data:', error);
                this.showAlertMessage('error', 'حدث خطأ في تحميل بيانات المحاضرة');
                } finally {
                this.loadingLectureSubjects = false;
                }

            },

            async updateAvailableTeachersAndSubjectsForSeries() {
            const currentGroupId = this.newSeries.group_id;

            if (!currentGroupId) {
            this.seriesAvailableTeachers = [];
            this.seriesFilteredSubjects = [];
            this.newSeries.teacher_id = '';
            this.newSeries.subject_id = '';
            this.loadingSeriesSubjects = false;
            return;
            }

            try {
            this.loadingSeriesSubjects = true;

            const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            };

            const promises = [];

            // المدرسين
            if (window.routes.getTeachers) {
            promises.push(
            fetch(`${window.routes.getTeachers}?group_id=${currentGroupId}`, { headers })
            );
            }

            // المواد
            if (window.routes.getGroupSubjects) {
            promises.push(
            fetch(`${window.routes.getGroupSubjects}?group_id=${currentGroupId}`, { headers })
            );
            }

            const responses = await Promise.all(promises);

            // معالجة المدرسين
            if (responses[0] && responses[0].ok) {
            const teachersData = await responses[0].json();
            if (teachersData.success) {
            this.seriesAvailableTeachers = this.validateTeachersData(teachersData.teachers || []);
            console.log('Series teachers loaded:', this.seriesAvailableTeachers.length);
            }
            }

            // معالجة المواد
            if (responses[1] && responses[1].ok) {
            const subjectsData = await responses[1].json();
            if (subjectsData.success) {
            this.seriesFilteredSubjects = this.validateSubjectsData(subjectsData.subjects || []);
            console.log('Series subjects loaded:', this.seriesFilteredSubjects.length);
            }
            }

            } catch (error) {
            console.error('Error loading series group data:', error);
            this.showAlertMessage('error', 'حدث خطأ في تحميل بيانات السلسلة');
            } finally {
            this.loadingSeriesSubjects = false;
            }
            },

            // التحقق من صحة بيانات المواد
            validateSubjectsData(subjects) {
                if (!Array.isArray(subjects)) {
                    console.error('Expected subjects to be an array, got:', typeof subjects);
                    return [];
                }

                return subjects.filter(subject => {
                    return subject &&
                           (subject.id || subject.subject_id) &&
                           (subject.name || subject.display_name || subject.subject_name);
                }).map(subject => ({
                    id: subject.id || subject.subject_id,
                    name: subject.name || subject.display_name || subject.subject_name,
                    display_name: subject.display_name || subject.name || subject.subject_name
                }));
            },

            // التحقق من صحة بيانات المدرسين
            validateTeachersData(teachers) {
                if (!Array.isArray(teachers)) {
                    console.error('Expected teachers to be an array, got:', typeof teachers);
                    return [];
                }

                return teachers.filter(teacher => {
                    return teacher && teacher.id && (teacher.name || teacher.display_name);
                }).map(teacher => ({
                    id: teacher.id,
                    name: teacher.name,
                    display_name: teacher.display_name || teacher.name
                }));
            },

            // إنشاء محاضرة جديدة
            async createLecture() {
                this.creating = true;
                try {
                    const response = await fetch(window.routes.storeLecture, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newLecture)
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.lectures.push(data.lecture);
                        this.showCreateLectureModal = false;
                        this.resetNewLecture();
                        this.showAlertMessage('success', data.message || 'تم إنشاء المحاضرة بنجاح');
                        // إعادة تحميل التقويم إذا كان موجوداً
                        if (this.calendar) {
                            this.calendar.refetchEvents();
                        }
                    } else {
                        if (data.conflicts) {
                            this.timeConflicts = data.conflicts;
                        }
                        throw new Error(data.message || 'فشل في إنشاء المحاضرة');
                    }
                } catch (error) {
                    this.showAlertMessage('error', error.message);
                } finally {
                    this.creating = false;
                }
            },

            // إنشاء سلسلة متكررة
            async createSeries() {
                this.creatingSeries = true;
                try {
                    const response = await fetch(window.routes.createSeries, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.newSeries)
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showCreateSeriesModal = false;
                        this.resetNewSeries();
                        this.showAlertMessage('success', data.message || 'تم إنشاء السلسلة بنجاح');
                        await this.loadData(); // إعادة تحميل جميع البيانات
                    } else {
                        throw new Error(data.message || 'فشل في إنشاء السلسلة');
                    }
                } catch (error) {
                    this.showAlertMessage('error', error.message);
                } finally {
                    this.creatingSeries = false;
                }
            },

            // إنشاء امتحان نهائي
            async createFinalExam() {
                this.creatingFinalExam = true;
                try {
                    const response = await fetch(window.routes.createFinalExam, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.finalExam)
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showFinalExamModal = false;
                        this.resetFinalExam();
                        this.showAlertMessage('success', data.message || 'تم إنشاء الامتحان النهائي بنجاح');
                        await this.loadData();
                    } else {
                        throw new Error(data.message || 'فشل في إنشاء الامتحان النهائي');
                    }
                } catch (error) {
                    this.showAlertMessage('error', error.message);
                } finally {
                    this.creatingFinalExam = false;
                }
            },

            // تأجيل محاضرة
            rescheduleLecture(lecture) {
                this.selectedLecture = lecture;
                this.rescheduleData = {
                    new_date: lecture.date,
                    new_start_time: lecture.start_time,
                    new_end_time: lecture.end_time,
                    reason: ''
                };
                this.showRescheduleModal = true;
            },

            async confirmReschedule() {
                this.rescheduling = true;
                try {
                    const response = await fetch(`${window.routes.rescheduleLecture}/${this.selectedLecture.id}/reschedule`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.rescheduleData)
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showRescheduleModal = false;
                        this.showAlertMessage('success', data.message || 'تم تأجيل المحاضرة بنجاح');
                        await this.loadData();
                    } else {
                        throw new Error(data.message || 'فشل في تأجيل المحاضرة');
                    }
                } catch (error) {
                    this.showAlertMessage('error', error.message);
                } finally {
                    this.rescheduling = false;
                }
            },

            // إلغاء محاضرة
            async cancelLecture(lecture) {
                const reason = prompt('يرجى تحديد سبب إلغاء المحاضرة:');
                if (!reason || reason.trim() === '') return;

                try {
                    const response = await fetch(`${window.routes.cancelLecture}/${lecture.id}/cancel`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ reason: reason.trim() })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showAlertMessage('success', data.message || 'تم إلغاء المحاضرة بنجاح');
                        await this.loadData();
                    } else {
                        throw new Error(data.message || 'فشل في إلغاء المحاضرة');
                    }
                } catch (error) {
                    this.showAlertMessage('error', error.message);
                }
            },

            // وظائف أخرى
            editLecture(lecture) {
                // TODO: إضافة نموذج تعديل المحاضرة
                this.showAlertMessage('success', `سيتم فتح نموذج تعديل ${lecture.title}`);
            },

            viewLecture(lecture) {
                // TODO: إضافة نموذج عرض تفاصيل المحاضرة
                this.showAlertMessage('success', `سيتم عرض تفاصيل ${lecture.title}`);
            },

            endSeries(series) {
                // TODO: إضافة تأكيد إنهاء السلسلة
                this.showAlertMessage('success', `سيتم إنهاء السلسلة ${series.title}`);
            },

            // فلترة التقويم
            filterCalendar() {
                if (this.calendar) {
                    this.calendar.refetchEvents();
                }
            },

            // تطبيق الفلاتر
            applyFilters() {
                this.showAlertMessage('success', 'تم تطبيق الفلاتر');
                // TODO: إضافة منطق الفلترة الفعلي
            },

            resetFilters() {
                this.filters = {
                    date_from: '',
                    date_to: '',
                    group_id: '',
                    teacher_id: '',
                    subject_id: '',
                    status: '',
                    type: ''
                };
                this.searchTerm = '';
                this.showAlertMessage('success', 'تم إعادة تعيين الفلاتر');
            },

            // تحديث البيانات
            async refreshData() {
                this.refreshing = true;
                try {
                    await this.loadData();
                    this.showAlertMessage('success', 'تم تحديث البيانات بنجاح');
                } catch (error) {
                    this.showAlertMessage('error', 'فشل في تحديث البيانات');
                } finally {
                    this.refreshing = false;
                }
            },

            // وظائف مساعدة
            formatDate(date) {
                return new Date(date).toLocaleDateString('ar-EG', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            },

            getTypeLabel(type) {
                const types = {
                    'lecture': 'محاضرة',
                    'exam': 'امتحان',
                    'review': 'مراجعة',
                    'activity': 'نشاط',
                    'final_exam': 'امتحان نهائي'
                };
                return types[type] || type;
            },

            getStatusLabel(status) {
                const statuses = {
                    'scheduled': 'مجدولة',
                    'completed': 'مكتملة',
                    'rescheduled': 'مؤجلة',
                    'cancelled': 'ملغاة'
                };
                return statuses[status] || status;
            },

            getDaysNames(days) {
                const dayNames = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
                return days.map(day => dayNames[parseInt(day)]);
            },

            // وظائف إعادة التعيين
            resetNewLecture() {
                this.newLecture = {
                    title: '',
                    type: 'lecture',
                    date: '',
                    start_time: '',
                    end_time: '',
                    teacher_id: '',
                    group_id: '',
                    subject_id: '',
                    description: ''
                };
                this.timeConflicts = [];
                this.lectureFilteredSubjects = [];
                this.lectureAvailableTeachers = [];
                this.loadingLectureSubjects = false;
            },

            resetNewSeries() {
                this.newSeries = {
                    title: '',
                    start_date: '',
                    start_time: '',
                    end_time: '',
                    teacher_id: '',
                    group_id: '',
                    subject_id: '',
                    days: [],
                    description: ''
                };

                this.seriesFilteredSubjects = [];
                this.seriesAvailableTeachers = [];
                this.loadingSeriesSubjects = false;
            },
            // ✅ إضافة دالات للتصحيح
            debugLectureData() {
            console.log('=== تصحيح بيانات المحاضرات ===');
            console.log('المجموعة المختارة:', this.newLecture.group_id);
            console.log('المواد المفلترة:', this.lectureFilteredSubjects.length);
            console.log('المدرسين المتاحين:', this.lectureAvailableTeachers.length);
            console.log('المدرس المختار:', this.newLecture.teacher_id);
            console.log('المادة المختارة:', this.newLecture.subject_id);
            },

            debugSeriesData() {
            console.log('=== تصحيح بيانات السلاسل ===');
            console.log('المجموعة المختارة:', this.newSeries.group_id);
            console.log('المواد المفلترة:', this.seriesFilteredSubjects.length);
            console.log('المدرسين المتاحين:', this.seriesAvailableTeachers.length);
            console.log('المدرس المختار:', this.newSeries.teacher_id);
            console.log('المادة المختارة:', this.newSeries.subject_id);
            },

            resetFinalExam() {
                this.finalExam = {
                    series_id: '',
                    title: '',
                    date: '',
                    start_time: '',
                    duration: 120,
                    teacher_id: '',
                    group_id: '',
                    subject_id: '',
                    room: '',
                    total_marks: 100,
                    notes: ''
                };
            },

            // رسائل التنبيه
            showAlertMessage(type, message) {
                this.alertType = type;
                this.alertMessage = message;
                this.showAlert = true;

                setTimeout(() => {
                    this.showAlert = false;
                }, 5000);
            },

            // تهيئة التقويم
            initializeCalendar() {
                this.$nextTick(() => {
                const calendarEl = document.getElementById('fullcalendar');
                if (calendarEl) {
                // إذا كان التقويم موجودًا بالفعل، نقوم بتدميره أولاً
                if (this.calendar) {
                this.calendar.destroy();
                }

                this.calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'ar',
                direction: 'rtl',
                headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                today: 'اليوم',
                month: 'شهر',
                week: 'أسبوع',
                day: 'يوم'
                },
                events: async (info, successCallback, failureCallback) => {
                try {
                // إضافة الفلاتر إذا كانت موجودة
                let url = `${window.routes.calendarData}?start=${info.startStr}&end=${info.endStr}`;

                if (this.calendarFilters.group) {
                url += `&group_id=${this.calendarFilters.group}`;
                }
                if (this.calendarFilters.teacher) {
                url += `&teacher_id=${this.calendarFilters.teacher}`;
                }
                if (this.calendarFilters.type) {
                url += `&type=${this.calendarFilters.type}`;
                }

                const response = await fetch(url);
                const events = await response.json();

                // تحويل البيانات إلى التنسيق المتوقع من FullCalendar
                const formattedEvents = events.map(event => ({
                id: event.id,
                title: event.title,
                start: event.start, // تأكد أن هذا حقل تاريخ ISO 8601
                end: event.end, // تأكد أن هذا حقل تاريخ ISO 8601
                backgroundColor: event.backgroundColor,
                borderColor: event.borderColor,
                extendedProps: {
                description: event.description,
                group_name: event.group_name,
                series_id: event.series_id,
                status: event.status,
                students_count: event.students_count,
                subject_name: event.subject_name,
                teacher_name: event.teacher_name,
                type: event.type
                }
                }));

                successCallback(formattedEvents);
                } catch (error) {
                console.error('Error loading calendar events:', error);
                failureCallback(error);
                }
                },
                // ... rest of the configuration
                });

                this.calendar.render();
                }
                });
            },

            // تهيئة التقويم المصغر
            initializeMiniCalendar() {
                this.$nextTick(() => {
                const miniCalendarEl = document.getElementById('mini-calendar');
                if (miniCalendarEl && !this.miniCalendar) {
                this.miniCalendar = new FullCalendar.Calendar(miniCalendarEl, {
                initialView: 'dayGridMonth',
                locale: 'ar',
                direction: 'rtl',
                headerToolbar: {
                left: 'prev',
                center: 'title',
                right: 'next'
                },
                height: 300,
                dayMaxEvents: false,
                eventDisplay: 'dot',
                dayHeaderContent: function (arg) {
                const dayNames = {
                0: 'أ', 1: 'ث', 2: 'ت', 3: 'ر',
                4: 'خ', 5: 'ج', 6: 'س'
                };
                return dayNames[arg.date.getDay()];
                },
                events: async (info) => {
                try {
                const response = await fetch(window.routes.calendarData +
                `?start=${info.startStr}&end=${info.endStr}`);

                if (!response.ok) {
                console.warn('Mini calendar: فشل في تحميل البيانات');
                return [];
                }

                const events = await response.json();
                return Array.isArray(events) ? events : [];

                } catch (error) {
                console.error('Error loading mini calendar events:', error);
                return []; // ✅ إرجاع مصفوفة فارغة عند الفشل
                }
                },
                dateClick: (info) => {
                this.activeTab = 'calendar';
                this.$nextTick(() => {
                if (this.calendar) {
                this.calendar.gotoDate(info.date);
                }
                });
                }
                });

                this.miniCalendar.render();
                }
                });
            },

            // مراقبة تغيير التبويبات
            watchTabChange() {
                this.$watch('activeTab', (newTab) => {
                    if (newTab === 'calendar') {
                        this.initializeCalendar();
                    } else if (newTab === 'dashboard') {
                        this.initializeMiniCalendar();
                    }
                });
            },

            // التحقق من تضارب الأوقات
            async checkTimeConflicts() {
                try {
                    this.timeConflicts = this.lectures.filter(lecture =>
                        lecture.teacher_id == this.newLecture.teacher_id &&
                        lecture.date === this.newLecture.date &&
                        lecture.status !== 'cancelled' &&
                        (
                            (this.newLecture.start_time >= lecture.start_time && this.newLecture.start_time < lecture.end_time) ||
                            (this.newLecture.end_time > lecture.start_time && this.newLecture.end_time <= lecture.end_time) ||
                            (this.newLecture.start_time <= lecture.start_time && this.newLecture.end_time >= lecture.end_time)
                        )
                    );
                } catch (error) {
                    console.error('Error checking time conflicts:', error);
                }
            },

            // دالة للتصحيح
            debugGroupSubjects() {
                console.log('=== تصحيح بيانات المجموعات والمواد ===');
                console.log('المجموعة المختارة:', this.newLecture.group_id);
                console.log('المجموعات المتاحة:', this.availableGroups.length);
                console.log('المواد المفلترة:', this.lectureFilteredSubjects.length);
                console.log('المدرسين المتاحين:', this.lectureAvailableTeachers.length);
                console.log('حالة التحميل:', this.loadingSubjects);

                if (this.lectureFilteredSubjects.length > 0) {
                    console.log('عينة من المواد:', this.lectureFilteredSubjects.slice(0, 3));
                }
            },

            // تهيئة المكون
            init() {
                this.watchTabChange();

                // تهيئة التقويم المصغر عند تحميل لوحة التحكم
                if (this.activeTab === 'dashboard') {
                    this.initializeMiniCalendar();
                }

                // مراقبة تغيير بيانات المحاضرة الجديدة للتحقق من التضارب
                this.$watch('newLecture', async (newLecture) => {
                    if (newLecture.teacher_id && newLecture.date && newLecture.start_time && newLecture.end_time) {
                        await this.checkTimeConflicts();
                    }
                }, { deep: true });
            }
        }

    }
</script>
@endpush
