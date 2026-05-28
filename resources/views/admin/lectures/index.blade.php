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
                                    <div class="shrink-0 ml-4">
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
                    <div class="py-4 px-4 bg-white border border-gray-100 shadow-sm rounded-xl select-none">

                        <!-- رأس التقويم -->
                        <div class="flex items-center justify-between mb-3">
                            <button @click="miniCalPrev()"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                            <span class="text-sm font-semibold text-gray-800" x-text="miniCalMonthLabel()"></span>
                            <button @click="miniCalNext()"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                        </div>

                        <!-- أسماء أيام الأسبوع -->
                        <div class="grid grid-cols-7 mb-1">
                            <template x-for="d in ['أ','إث','ث','أر','خ','ج','س']" :key="d">
                                <div class="text-center text-xs font-medium text-gray-400 py-1" x-text="d"></div>
                            </template>
                        </div>

                        <!-- شبكة الأيام -->
                        <div class="grid grid-cols-7 gap-y-0.5">
                            <template x-for="(dateStr, i) in getMiniCalDays()" :key="i">
                                <div>
                                    <!-- خلية فارغة -->
                                    <template x-if="!dateStr"><div></div></template>

                                    <!-- خلية يوم -->
                                    <template x-if="dateStr">
                                        <div class="flex flex-col items-center py-1 px-0.5 rounded-lg cursor-pointer transition-colors"
                                             :class="miniCalHoverDay === dateStr ? 'bg-gray-100' : 'hover:bg-gray-50'"
                                             @mouseenter="miniCalHoverDay = dateStr"
                                             @mouseleave="miniCalHoverDay = null"
                                             @click="goToCalendarDate(dateStr)">

                                            <!-- رقم اليوم -->
                                            <div class="text-xs font-medium leading-5 w-6 h-6 flex items-center justify-center rounded-full"
                                                 :class="isToday(dateStr)
                                                     ? 'bg-blue-600 text-white font-bold'
                                                     : getMiniCalEvents(dateStr).length
                                                         ? 'text-gray-800'
                                                         : 'text-gray-400'"
                                                 x-text="parseInt(dateStr.split('-')[2])">
                                            </div>

                                            <!-- شارات أنواع الأنشطة -->
                                            <div class="flex gap-0.5 mt-0.5 min-h-[14px] flex-wrap justify-center">
                                                <template x-for="badge in getMiniCalTypeBadges(dateStr)" :key="badge.type">
                                                    <span class="inline-flex items-center justify-center rounded text-white font-bold leading-none"
                                                          style="font-size:7px; width:15px; height:12px;"
                                                          :style="`background-color:${badge.color}`"
                                                          x-text="badge.count > 1 ? badge.count + badge.symbol : badge.symbol">
                                                    </span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- مفتاح الألوان -->
                        <div class="flex flex-wrap gap-x-3 gap-y-1 mt-3 pt-3 border-t border-gray-100">
                            <template x-for="item in [
                                {type:'lecture',   label:'محاضرة'},
                                {type:'exam',      label:'امتحان'},
                                {type:'review',    label:'مراجعة'},
                                {type:'activity',  label:'نشاط'},
                                {type:'final_exam',label:'نهائي'}
                            ]" :key="item.type">
                                <div class="flex items-center gap-1">
                                    <span class="inline-flex items-center justify-center rounded text-white font-bold"
                                          style="font-size:7px; width:14px; height:12px;"
                                          :style="`background-color:${getActivityColor(item.type)}`"
                                          x-text="getActivitySymbol(item.type)">
                                    </span>
                                    <span class="text-xs text-gray-400" x-text="item.label"></span>
                                </div>
                            </template>
                        </div>

                        <!-- لوحة التفاصيل: تعرض اليوم المحوَّم أو جدول اليوم الحالي -->
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-600"
                                      x-text="miniCalHoverDay && miniCalHoverDay !== miniCalTodayStr() ? 'أنشطة اليوم' : 'جدول اليوم'">
                                </span>
                                <span class="text-xs text-gray-400" x-text="getMiniCalDisplayLabel()"></span>
                            </div>

                            <div x-show="getMiniCalDisplayEvents().length === 0"
                                 class="text-xs text-center text-gray-400 py-3">
                                لا توجد أنشطة
                            </div>

                            <div class="space-y-1.5">
                                <template x-for="ev in getMiniCalDisplayEvents()" :key="ev.id">
                                    <div class="flex items-center gap-2 p-2 rounded-lg"
                                         :style="`background-color:${getActivityColor(ev.type)}18`">

                                        <!-- أيقونة النوع -->
                                        <div class="shrink-0 w-6 h-6 rounded flex items-center justify-center text-white font-bold text-xs"
                                             :style="`background-color:${getActivityColor(ev.type)}`"
                                             x-text="getActivitySymbol(ev.type)">
                                        </div>

                                        <!-- تفاصيل -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs font-medium text-gray-800 truncate"
                                                      x-text="ev.subject_name || ev.title"></span>
                                                <!-- نقطة لون المادة -->
                                                <span class="shrink-0 w-2 h-2 rounded-full"
                                                      :style="`background-color:${getSubjectColor(ev.subject_id, ev.subject_name)}`"></span>
                                            </div>
                                            <div class="flex items-center gap-1 mt-0.5">
                                                <span class="text-xs text-gray-500"
                                                      x-text="ev.start_time + ' - ' + ev.end_time"></span>
                                                <span class="text-gray-300">·</span>
                                                <span class="text-xs text-gray-400 truncate"
                                                      x-text="ev.group_name"></span>
                                            </div>
                                            <div class="text-xs text-gray-400 truncate mt-0.5"
                                                 x-text="ev.teacher_name"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>

                    <!-- الامتحانات القادمة -->
                    <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">امتحانات قادمة</h3>
                        <div class="space-y-3">
                            <template x-for="exam in upcomingExams.slice(0, 3)" :key="exam.id">
                                <div class="flex items-center p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="shrink-0 ml-3">
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
                    <template x-for="item in [
                        {type:'lecture',    label:'محاضرة'},
                        {type:'exam',       label:'امتحان'},
                        {type:'review',     label:'مراجعة'},
                        {type:'activity',   label:'نشاط'},
                        {type:'final_exam', label:'امتحان نهائي'}
                    ]" :key="item.type">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center rounded text-white font-bold"
                                  style="font-size:10px; width:18px; height:16px;"
                                  :style="`background-color:${getActivityColor(item.type)}`"
                                  x-text="getActivitySymbol(item.type)">
                            </span>
                            <span class="text-sm text-gray-600" x-text="item.label"></span>
                        </div>
                    </template>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center rounded text-white font-bold"
                              style="font-size:10px; width:18px; height:16px; background:#6B7280;">ل</span>
                        <span class="text-sm text-gray-600">ملغاة</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center rounded text-white font-bold"
                              style="font-size:10px; width:18px; height:16px; background:#8B5CF6; outline:2px dashed #8B5CF6; outline-offset:1px;">أ</span>
                        <span class="text-sm text-gray-600">مؤجلة</span>
                    </div>

                    <!-- مفتاح ألوان المواد -->
                    <template x-if="Object.keys(subjectColorMap).length > 0">
                        <div class="flex items-center gap-3 pr-4 border-r border-gray-200">
                            <span class="text-xs text-gray-400 font-medium">المواد:</span>
                            <template x-for="entry in Object.entries(subjectColorMap)" :key="entry[0]">
                                <div class="flex items-center gap-1">
                                    <span class="w-3 h-3 rounded-full shrink-0"
                                          :style="`background-color:${entry[1]}`"></span>
                                    <span class="text-xs text-gray-500" x-text="entry[0]"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div id="fullcalendar"></div>
            </div>
        </div>

        <!-- Popover تفاصيل الحصة -->
        <div x-show="calendarPopover.show" x-cloak
             class="fixed z-50 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden fade-in"
             style="width:272px;"
             :style="`top:${calendarPopover.y}px; left:${calendarPopover.x}px;`">

            <!-- رأس بلون نوع النشاط -->
            <div class="p-3 text-white relative"
                 :style="`background-color:${getActivityColor(calendarPopover.event?.type)}`">
                <div class="flex items-center justify-between mb-1">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center bg-white/25 rounded font-bold"
                              style="font-size:10px; width:20px; height:18px;"
                              x-text="getActivitySymbol(calendarPopover.event?.type)"></span>
                        <span class="text-xs font-medium opacity-90"
                              x-text="getTypeLabel(calendarPopover.event?.type)"></span>
                    </div>
                    <button @click="calendarPopover.show = false"
                            class="text-white hover:text-gray-200 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
                <h4 class="text-sm font-semibold leading-snug"
                    x-text="calendarPopover.event?.title"></h4>
            </div>

            <!-- تفاصيل الحصة -->
            <div class="p-3 space-y-2.5">

                <!-- التاريخ والوقت -->
                <div x-show="calendarPopover.event?.date_label"
                     class="flex items-start gap-2 pb-2 border-b border-gray-100">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <div class="text-xs font-medium text-gray-700" x-text="calendarPopover.event?.date_label"></div>
                        <div class="text-xs text-gray-500 mt-0.5"
                             x-show="calendarPopover.event?.start_time"
                             x-text="`${calendarPopover.event?.start_time} — ${calendarPopover.event?.end_time}`">
                        </div>
                    </div>
                </div>

                <!-- المادة -->
                <div x-show="calendarPopover.event?.subject_name"
                     class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full shrink-0"
                          :style="`background-color:${getSubjectColor(calendarPopover.event?.subject_id, calendarPopover.event?.subject_name)}`"></span>
                    <span class="text-xs font-semibold text-gray-700"
                          x-text="calendarPopover.event?.subject_name"></span>
                </div>

                <!-- المعلم -->
                <div x-show="calendarPopover.event?.teacher_name"
                     class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-xs text-gray-600" x-text="calendarPopover.event?.teacher_name"></span>
                </div>

                <!-- المجموعة وعدد الطلاب -->
                <div x-show="calendarPopover.event?.group_name"
                     class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                    </svg>
                    <span class="text-xs text-gray-600" x-text="calendarPopover.event?.group_name"></span>
                    <span x-show="calendarPopover.event?.students_count > 0"
                          class="text-xs text-gray-400"
                          x-text="`(${calendarPopover.event?.students_count} طالب)`"></span>
                </div>

                <!-- الحالة -->
                <div class="flex items-center gap-2">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                          :class="{
                              'bg-green-100 text-green-700':  calendarPopover.event?.status === 'scheduled',
                              'bg-blue-100 text-blue-700':    calendarPopover.event?.status === 'completed',
                              'bg-purple-100 text-purple-700':calendarPopover.event?.status === 'rescheduled',
                              'bg-red-100 text-red-700':      calendarPopover.event?.status === 'cancelled'
                          }"
                          x-text="getStatusLabel(calendarPopover.event?.status)"></span>
                </div>

                <!-- الوصف -->
                <div x-show="calendarPopover.event?.description"
                     class="pt-2 border-t border-gray-100">
                    <p class="text-xs text-gray-500 leading-relaxed"
                       x-text="calendarPopover.event?.description"></p>
                </div>
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
                                            <button
                                                x-show="lecture.date <= today && lecture.status !== 'cancelled'"
                                                @click="openAttendanceModal(lecture)"
                                                class="p-2 text-purple-600 rounded-lg hover:bg-purple-50" title="تسجيل الحضور">
                                                <i class="fas fa-clipboard-check"></i>
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
                    <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden">

                        <!-- شريط اللون العلوي -->
                        <div class="h-1 bg-gradient-to-l from-purple-500 to-purple-300"></div>

                        <div class="p-6">
                            <!-- رأس الكارت -->
                            <div class="flex items-start justify-between mb-4 gap-4">
                                <div class="flex-1 min-w-0">
                                    <!-- العنوان والبادج -->
                                    <div class="flex items-center gap-2 flex-wrap mb-1">
                                        <h3 class="text-lg font-semibold text-gray-800" x-text="series.title"></h3>
                                        <span x-show="series.subject_name"
                                              class="px-2 py-0.5 text-xs font-medium rounded-full text-white"
                                              :style="`background-color:${getSubjectColor(series.subject_id, series.subject_name)}`"
                                              x-text="series.subject_name"></span>
                                    </div>
                                    <!-- المجموعة والمعلم -->
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-users ml-1 text-gray-400"></i>
                                        <span x-text="series.group_name"></span>
                                        <span class="mx-1 text-gray-300">•</span>
                                        <i class="fas fa-chalkboard-teacher ml-1 text-gray-400"></i>
                                        <span x-text="series.teacher_name"></span>
                                    </p>
                                    <!-- الوقت والأيام -->
                                    <div class="flex items-center gap-3 mt-2 text-xs text-gray-500 flex-wrap">
                                        <span class="flex items-center gap-1">
                                            <i class="fas fa-clock text-gray-400"></i>
                                            <span x-text="`${series.start_time} — ${series.end_time}`"></span>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i class="fas fa-calendar-week text-gray-400"></i>
                                            <span x-text="getDaysNames(series.days).join(' · ')"></span>
                                        </span>
                                        <span x-show="series.end_date" class="flex items-center gap-1">
                                            <i class="fas fa-flag-checkered text-gray-400"></i>
                                            <span x-text="`ينتهي ${series.end_date}`"></span>
                                        </span>
                                    </div>
                                    <!-- الوصف -->
                                    <p x-show="series.description"
                                       class="mt-2 text-xs text-gray-400 italic line-clamp-1"
                                       x-text="series.description"></p>
                                </div>

                                <!-- الأزرار -->
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="px-3 py-1 text-xs font-medium text-purple-800 bg-purple-100 rounded-full">
                                        نشطة
                                    </span>
                                    <button @click="editSeries(series)"
                                        class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                                        <i class="fas fa-edit text-xs"></i>
                                        تعديل
                                    </button>
                                    <button @click="endSeries(series)"
                                        class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                        <i class="fas fa-stop-circle text-xs"></i>
                                        إنهاء
                                    </button>
                                </div>
                            </div>

                            <!-- إحصائيات -->
                            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                                <div class="p-3 bg-blue-50 rounded-lg text-center">
                                    <p class="text-xs text-blue-600 mb-1">الإجمالي</p>
                                    <p class="text-2xl font-bold text-blue-800" x-text="series.total_lectures"></p>
                                    <p class="text-xs text-blue-500">محاضرة</p>
                                </div>
                                <div class="p-3 bg-green-50 rounded-lg text-center">
                                    <p class="text-xs text-green-600 mb-1">مكتملة</p>
                                    <p class="text-2xl font-bold text-green-800" x-text="series.completed_lectures"></p>
                                    <p class="text-xs text-green-500">محاضرة</p>
                                </div>
                                <div class="p-3 bg-yellow-50 rounded-lg text-center">
                                    <p class="text-xs text-yellow-600 mb-1">متبقية</p>
                                    <p class="text-2xl font-bold text-yellow-800" x-text="series.remaining_lectures"></p>
                                    <p class="text-xs text-yellow-500">محاضرة</p>
                                </div>
                                <div class="p-3 bg-purple-50 rounded-lg text-center">
                                    <p class="text-xs text-purple-600 mb-1">المدة</p>
                                    <p class="text-2xl font-bold text-purple-800" x-text="series.weeks_count"></p>
                                    <p class="text-xs text-purple-500">أسبوع</p>
                                </div>
                            </div>

                            <!-- شريط التقدم -->
                            <div class="mt-4">
                                <div class="flex justify-between text-xs text-gray-500 mb-1">
                                    <span>التقدم</span>
                                    <span x-text="`${series.total_lectures > 0 ? Math.round(series.completed_lectures / series.total_lectures * 100) : 0}%`"></span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-gradient-to-l from-purple-500 to-purple-400 h-2 rounded-full transition-all duration-500"
                                         :style="`width:${series.total_lectures > 0 ? (series.completed_lectures / series.total_lectures * 100) : 0}%`">
                                    </div>
                                </div>
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

            <!-- فلاتر التقارير -->
            <div class="p-4 mb-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600">من تاريخ</label>
                        <input type="date" x-model="reportFilters.dateFrom"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600">إلى تاريخ</label>
                        <input type="date" x-model="reportFilters.dateTo"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600">المجموعة</label>
                        <select x-model="reportFilters.groupId"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">جميع المجموعات</option>
                            <template x-for="g in availableGroups" :key="g.id">
                                <option :value="g.id" x-text="g.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-gray-600">المدرس</label>
                        <select x-model="reportFilters.teacherId"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">جميع المدرسين</option>
                            <template x-for="t in reportAvailableTeachers" :key="t.id">
                                <option :value="t.id" x-text="t.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-3 mt-3">
                    <button @click="reportFilters = { dateFrom: '', dateTo: '', groupId: '', teacherId: '' }"
                        class="px-3 py-1.5 text-xs text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                        <i class="fas fa-undo ml-1"></i>إعادة تعيين
                    </button>
                    <span class="text-xs text-gray-400"
                        x-text="`${reportLectures.length} محاضرة في نطاق الفلتر`"></span>
                </div>
            </div>

            <!-- بطاقات الملخص -->
            <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-5">
                <div class="p-4 bg-white border border-gray-100 shadow-sm rounded-xl text-center">
                    <p class="text-2xl font-bold text-gray-800" x-text="reportSummary.total"></p>
                    <p class="text-xs text-gray-500 mt-1">إجمالي</p>
                </div>
                <div class="p-4 bg-green-50 border border-green-100 shadow-sm rounded-xl text-center">
                    <p class="text-2xl font-bold text-green-700" x-text="reportSummary.completed"></p>
                    <p class="text-xs text-green-600 mt-1">مكتملة</p>
                    <p class="text-xs text-green-400 mt-0.5" x-text="`${reportSummary.completionRate}%`"></p>
                </div>
                <div class="p-4 bg-blue-50 border border-blue-100 shadow-sm rounded-xl text-center">
                    <p class="text-2xl font-bold text-blue-700" x-text="reportSummary.scheduled"></p>
                    <p class="text-xs text-blue-600 mt-1">مجدولة</p>
                </div>
                <div class="p-4 bg-purple-50 border border-purple-100 shadow-sm rounded-xl text-center">
                    <p class="text-2xl font-bold text-purple-700" x-text="reportSummary.rescheduled"></p>
                    <p class="text-xs text-purple-600 mt-1">مؤجلة</p>
                </div>
                <div class="p-4 bg-red-50 border border-red-100 shadow-sm rounded-xl text-center">
                    <p class="text-2xl font-bold text-red-700" x-text="reportSummary.cancelled"></p>
                    <p class="text-xs text-red-600 mt-1">ملغاة</p>
                    <p class="text-xs text-red-400 mt-0.5" x-text="`${reportSummary.cancellationRate}%`"></p>
                </div>
            </div>

            <!-- جداول المدرسين والمجموعات -->
            <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">

                <!-- جدول أداء المدرسين -->
                <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800">أداء المدرسين</h3>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="`${reportTeachers.length} مدرس`"></p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المدرس</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">الإجمالي</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">مكتملة</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">ملغاة</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">الإنجاز</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="t in reportTeachers" :key="t.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-800" x-text="t.name"></td>
                                        <td class="px-4 py-3 text-center text-gray-600" x-text="t.total"></td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="font-medium text-green-600" x-text="t.completed"></span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="text-red-500" x-text="t.cancelled"></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                                    <div class="bg-green-500 h-1.5 rounded-full transition-all"
                                                        :style="`width:${t.total ? Math.round(t.completed/t.total*100) : 0}%`"></div>
                                                </div>
                                                <span class="text-xs text-gray-400 w-8 text-left"
                                                    x-text="`${t.total ? Math.round(t.completed/t.total*100) : 0}%`"></span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="reportTeachers.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">لا توجد بيانات</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- جدول أداء المجموعات -->
                <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800">أداء المجموعات</h3>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="`${reportGroups.length} مجموعة`"></p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المجموعة</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">المحاضرات</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">المواد</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">التقدم</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="g in reportGroups" :key="g.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-800" x-text="g.name"></td>
                                        <td class="px-4 py-3 text-center text-gray-600" x-text="g.total"></td>
                                        <td class="px-4 py-3 text-center text-gray-600" x-text="g.subjects"></td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                                    <div class="bg-blue-500 h-1.5 rounded-full transition-all"
                                                        :style="`width:${g.total ? Math.round(g.completed/g.total*100) : 0}%`"></div>
                                                </div>
                                                <span class="text-xs text-gray-400 w-8 text-left"
                                                    x-text="`${g.total ? Math.round(g.completed/g.total*100) : 0}%`"></span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="reportGroups.length === 0">
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-400 text-sm">لا توجد بيانات</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- سجل الإلغاءات والتأجيلات -->
            <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">سجل الإلغاءات والتأجيلات</h3>
                    <p class="text-xs text-gray-400 mt-0.5" x-text="`${reportCancellations.length} محاضرة`"></p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المحاضرة</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">التاريخ</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المدرس</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المجموعة</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <template x-for="l in reportCancellations.slice(0, 25)" :key="l.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-800" x-text="l.title"></td>
                                    <td class="px-4 py-3 text-xs text-gray-500" x-text="formatDate(l.date)"></td>
                                    <td class="px-4 py-3 text-gray-600" x-text="l.teacher_name"></td>
                                    <td class="px-4 py-3 text-gray-600" x-text="l.group_name"></td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 text-xs rounded-full font-medium"
                                            :class="l.status === 'cancelled'
                                                ? 'bg-red-100 text-red-700'
                                                : 'bg-purple-100 text-purple-700'"
                                            x-text="getStatusLabel(l.status)"></span>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="reportCancellations.length === 0">
                                <td colspan="5" class="px-4 py-10 text-center">
                                    <i class="fas fa-check-circle text-green-300 text-2xl mb-2 block"></i>
                                    <span class="text-sm text-gray-400">لا توجد إلغاءات أو تأجيلات</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal إضافة محاضرة -->
    <div x-show="showCreateLectureModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
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

                        <div x-show="newLecture.group_id && loadingLectureSubjects" class="mt-2 text-sm text-blue-600">
                            <i class="fas fa-spinner fa-spin ml-1"></i>
                            جاري تحميل المواد...
                        </div>

                        <div x-show="newLecture.group_id && !loadingLectureSubjects && lectureFilteredSubjects.length === 0"
                            class="mt-2 text-sm text-amber-600">
                            <i class="fas fa-info-circle ml-1"></i>
                            لا توجد مواد مرتبطة بهذه المجموعة
                        </div>

                        <div x-show="newLecture.group_id && !loadingLectureSubjects && lectureFilteredSubjects.length > 0"
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
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

    <!-- Modal تعديل السلسلة -->
    <div x-show="showEditSeriesModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         @click.self="showEditSeriesModal = false">
        <div class="w-full max-w-2xl mx-4 bg-white shadow-2xl rounded-2xl fade-in max-h-[90vh] overflow-y-auto"
             @click.stop>

            <!-- رأس الـ Modal -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800">تعديل السلسلة</h3>
                        <p class="mt-1 text-sm text-gray-500"
                           x-text="`${editSeriesFutureCount} محاضرة مستقبلية ستتأثر بالتعديل`"></p>
                    </div>
                    <button @click="showEditSeriesModal = false"
                            class="p-2 text-gray-400 rounded-lg hover:text-gray-600 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form @submit.prevent="saveSeriesEdit()" class="p-6 space-y-6">

                <!-- العنوان والوقت -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="block mb-2 text-sm font-medium text-gray-700">عنوان السلسلة *</label>
                        <input type="text" x-model="editSeriesForm.title" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">وقت البداية *</label>
                        <input type="time" x-model="editSeriesForm.start_time" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">وقت النهاية *</label>
                        <input type="time" x-model="editSeriesForm.end_time" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">المدرس *</label>
                        <select x-model="editSeriesForm.teacher_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">اختر المدرس</option>
                            <template x-for="teacher in editSeriesTeachers" :key="teacher.id">
                                <option :value="String(teacher.id)" x-text="teacher.display_name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">المادة</label>
                        <select x-model="editSeriesForm.subject_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">بدون مادة</option>
                            <template x-for="subject in editSeriesSubjects" :key="subject.id">
                                <option :value="String(subject.id)" x-text="subject.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">تاريخ الانتهاء</label>
                        <input type="date" x-model="editSeriesForm.end_date"
                               :min="new Date().toISOString().split('T')[0]"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <p class="mt-1 text-xs text-gray-400">اتركه فارغاً للإبقاء على التاريخ الحالي</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-2 text-sm font-medium text-gray-700">الوصف</label>
                        <textarea x-model="editSeriesForm.description" rows="2"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                                  placeholder="وصف اختياري للسلسلة..."></textarea>
                    </div>
                </div>

                <!-- أيام الأسبوع -->
                <div>
                    <label class="block mb-3 text-sm font-medium text-gray-700">أيام الأسبوع *</label>
                    <div class="grid grid-cols-7 gap-2">
                        <template x-for="day in [
                            {v:'0',label:'أحد'}, {v:'1',label:'إث'}, {v:'2',label:'ثلا'},
                            {v:'3',label:'أرب'}, {v:'4',label:'خمي'}, {v:'5',label:'جمع'}, {v:'6',label:'سبت'}
                        ]" :key="day.v">
                            <label class="flex flex-col items-center p-2.5 border rounded-lg cursor-pointer transition-colors"
                                   :class="editSeriesForm.days.includes(day.v)
                                       ? 'bg-purple-50 border-purple-400 text-purple-700'
                                       : 'border-gray-200 hover:bg-gray-50 text-gray-600'">
                                <input type="checkbox" :value="day.v" x-model="editSeriesForm.days" class="sr-only">
                                <span class="text-sm font-medium" x-text="day.label"></span>
                                <div class="w-2 h-2 rounded-full mt-1"
                                     :class="editSeriesForm.days.includes(day.v) ? 'bg-purple-500' : 'bg-gray-200'"></div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- تحذير تغيير الأيام -->
                <div x-show="editSeriesDaysChanged"
                     class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-amber-800">تغييرت أيام السلسلة</p>
                            <p class="text-sm text-amber-700 mt-1">
                                هل تريد حذف المحاضرات المستقبلية المجدولة وإعادة توليدها بالأيام الجديدة؟
                            </p>
                            <label class="flex items-center gap-2 mt-3 cursor-pointer">
                                <input type="checkbox" x-model="editSeriesForm.regenerate"
                                       class="w-4 h-4 text-amber-600 rounded">
                                <span class="text-sm font-medium text-amber-800">
                                    نعم، أعد توليد
                                    <span class="font-bold" x-text="editSeriesFutureCount"></span>
                                    محاضرة بالأيام الجديدة
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- أزرار الحفظ -->
                <div class="flex justify-end gap-4 pt-4 border-t border-gray-200">
                    <button type="button" @click="showEditSeriesModal = false"
                            class="px-6 py-3 font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" :disabled="savingSeriesEdit || editSeriesForm.days.length === 0"
                            class="flex items-center gap-2 px-6 py-3 font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50">
                        <i class="fas fa-save" :class="{ 'fa-spinner animate-spin': savingSeriesEdit }"></i>
                        <span x-text="savingSeriesEdit ? 'جاري الحفظ...' : 'حفظ التعديلات'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal تأجيل المحاضرة -->
    <div x-show="showRescheduleModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
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

    <!-- Modal تعديل المحاضرة -->
    <div x-show="showEditLectureModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="showEditLectureModal = false">
        <div class="w-full max-w-2xl mx-4 bg-white shadow-2xl rounded-2xl fade-in max-h-[90vh] overflow-y-auto"
            @click.stop>
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800">تعديل المحاضرة</h3>
                <button @click="showEditLectureModal = false"
                    class="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form @submit.prevent="updateLecture()" class="p-6 space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">عنوان المحاضرة *</label>
                        <input type="text" x-model="editLectureData.title" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">نوع المحاضرة *</label>
                        <select x-model="editLectureData.type" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="lecture">محاضرة عادية</option>
                            <option value="exam">امتحان</option>
                            <option value="review">مراجعة</option>
                            <option value="activity">نشاط</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">التاريخ *</label>
                        <input type="date" x-model="editLectureData.date" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">المجموعة *</label>
                        <select x-model="editLectureData.group_id" required
                            @change="loadEditLectureGroupData(editLectureData.group_id)"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">اختر المجموعة</option>
                            <template x-for="group in availableGroups" :key="group.id">
                                <option :value="group.id" x-text="group.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">وقت البداية *</label>
                        <input type="time" x-model="editLectureData.start_time" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">وقت النهاية *</label>
                        <input type="time" x-model="editLectureData.end_time" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">المدرس *</label>
                        <select x-model="editLectureData.teacher_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">اختر المدرس</option>
                            <template x-for="t in editLectureTeachers" :key="t.id">
                                <option :value="t.id" x-text="t.display_name || t.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">المادة</label>
                        <select x-model="editLectureData.subject_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">اختر المادة</option>
                            <template x-for="s in editLectureSubjects" :key="s.id">
                                <option :value="s.id" x-text="s.name || s.display_name"></option>
                            </template>
                        </select>
                        <p x-show="loadingEditLectureSubjects" class="mt-1 text-xs text-blue-500">
                            <i class="fas fa-spinner fa-spin ml-1"></i> جاري تحميل المواد...
                        </p>
                    </div>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">وصف المحاضرة</label>
                    <textarea x-model="editLectureData.description" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="وصف مختصر عن محتوى المحاضرة..."></textarea>
                </div>
                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                    <button type="button" @click="showEditLectureModal = false"
                        class="px-6 py-3 font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" :disabled="updatingLecture"
                        class="flex items-center gap-2 px-6 py-3 font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        <i class="fas fa-save" :class="{ 'animate-spin fa-spinner': updatingLecture }"></i>
                        <span x-text="updatingLecture ? 'جاري الحفظ...' : 'حفظ التعديلات'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal عرض تفاصيل المحاضرة -->
    <div x-show="showViewLectureModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="showViewLectureModal = false">
        <div class="w-full max-w-lg mx-4 bg-white shadow-2xl rounded-2xl fade-in max-h-[90vh] overflow-y-auto"
            @click.stop>
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800">تفاصيل المحاضرة</h3>
                <button @click="showViewLectureModal = false"
                    class="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <template x-if="viewingLecture">
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-3 pb-4 border-b border-gray-100">
                        <div class="flex items-center justify-center w-12 h-12 rounded-xl"
                            :class="{
                                'bg-blue-100 text-blue-600':   viewingLecture.type === 'lecture',
                                'bg-red-100 text-red-600':     viewingLecture.type === 'exam',
                                'bg-green-100 text-green-600': viewingLecture.type === 'review',
                                'bg-purple-100 text-purple-600': viewingLecture.type === 'activity'
                            }">
                            <i class="text-xl fas"
                                :class="{
                                    'fa-chalkboard-teacher': viewingLecture.type === 'lecture',
                                    'fa-file-alt':           viewingLecture.type === 'exam',
                                    'fa-sync-alt':           viewingLecture.type === 'review',
                                    'fa-star':               viewingLecture.type === 'activity'
                                }"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-900" x-text="viewingLecture.title"></h4>
                            <span class="text-sm text-gray-500" x-text="getTypeLabel(viewingLecture.type)"></span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="font-medium text-gray-500">التاريخ</p>
                            <p class="font-semibold text-gray-800" x-text="formatDate(viewingLecture.date)"></p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-500">الوقت</p>
                            <p class="font-semibold text-gray-800"
                                x-text="(viewingLecture.start_time || '').substring(0,5) + ' – ' + (viewingLecture.end_time || '').substring(0,5)"></p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-500">المجموعة</p>
                            <p class="font-semibold text-gray-800" x-text="viewingLecture.group_name || viewingLecture.group?.name || '—'"></p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-500">المدرس</p>
                            <p class="font-semibold text-gray-800" x-text="viewingLecture.teacher_name || viewingLecture.teacher?.display_name || '—'"></p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-500">المادة</p>
                            <p class="font-semibold text-gray-800" x-text="viewingLecture.subject_name || viewingLecture.subject?.name || '—'"></p>
                        </div>
                        <div>
                            <p class="font-medium text-gray-500">الحالة</p>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-blue-100 text-blue-700':   viewingLecture.status === 'scheduled',
                                    'bg-green-100 text-green-700': viewingLecture.status === 'completed',
                                    'bg-red-100 text-red-700':     viewingLecture.status === 'cancelled',
                                    'bg-yellow-100 text-yellow-700': viewingLecture.status === 'rescheduled'
                                }"
                                x-text="getStatusLabel(viewingLecture.status)"></span>
                        </div>
                    </div>
                    <div x-show="viewingLecture.description" class="pt-2 border-t border-gray-100">
                        <p class="mb-1 text-sm font-medium text-gray-500">الوصف</p>
                        <p class="text-sm text-gray-700" x-text="viewingLecture.description"></p>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button @click="showViewLectureModal = false; editLecture(viewingLecture)"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            <i class="fas fa-edit"></i>
                            تعديل
                        </button>
                        <button @click="showViewLectureModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                            إغلاق
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Modal تسجيل الحضور -->
    <div x-show="showAttendanceModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         @click.self="showAttendanceModal = false">
        <div class="w-full max-w-2xl mx-4 bg-white shadow-2xl rounded-2xl fade-in max-h-[90vh] flex flex-col"
             @click.stop>

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 shrink-0">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">تسجيل الحضور</h3>
                    <p class="mt-0.5 text-sm text-gray-500"
                       x-text="(attendanceLecture?.title ?? '') + ' — ' + (attendanceLecture?.date ?? '')"></p>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Live counts -->
                    <div class="hidden sm:flex items-center gap-3 text-xs">
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                            <span x-text="attendanceCounts.present"></span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span>
                            <span x-text="attendanceCounts.late"></span>
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                            <span x-text="attendanceCounts.absent"></span>
                        </span>
                    </div>
                    <button @click="showAttendanceModal = false"
                        class="p-2 text-gray-400 rounded-lg hover:text-gray-600 hover:bg-gray-100 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Loading -->
            <div x-show="loadingAttendance" class="flex-1 flex items-center justify-center py-16">
                <div class="text-center text-gray-400">
                    <i class="fas fa-spinner fa-spin fa-2x mb-3 text-primary"></i>
                    <p class="text-sm">جار تحميل الطلاب...</p>
                </div>
            </div>

            <!-- Empty group -->
            <div x-show="!loadingAttendance && attendanceStudents.length === 0"
                 class="flex-1 flex items-center justify-center py-16 text-gray-400 text-sm">
                لا يوجد طلاب في هذه المجموعة
            </div>

            <!-- Students list -->
            <div x-show="!loadingAttendance && attendanceStudents.length > 0" class="flex flex-col flex-1 min-h-0">

                <!-- Bulk actions -->
                <div class="px-6 py-2.5 bg-gray-50 border-b border-gray-100 flex items-center gap-2 shrink-0">
                    <span class="text-xs font-medium text-gray-500 ml-1">تحديد الجميع:</span>
                    <button type="button" @click="attendanceMarkAll('present')"
                        class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 hover:bg-green-200 transition">
                        ✓ حاضر
                    </button>
                    <button type="button" @click="attendanceMarkAll('late')"
                        class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 hover:bg-yellow-200 transition">
                        ⏰ متأخر
                    </button>
                    <button type="button" @click="attendanceMarkAll('absent')"
                        class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 hover:bg-red-200 transition">
                        ✗ غائب
                    </button>
                    <span class="mr-auto text-xs text-orange-500 font-medium"
                          x-show="attendanceUnmarked > 0"
                          x-text="attendanceUnmarked + ' طالب لم يُسجَّل'">
                    </span>
                </div>

                <!-- Students scroll area -->
                <div class="overflow-y-auto divide-y divide-gray-100 flex-1">
                    <template x-for="student in attendanceStudents" :key="student.id">
                        <div class="px-6 py-3 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold shrink-0"
                                     x-text="student.name.charAt(0)"></div>
                                <span class="text-sm font-medium text-gray-900 truncate" x-text="student.name"></span>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button"
                                    @click="attendanceSetStatus(student.id, 'present')"
                                    :class="attendanceStatuses[String(student.id)] === 'present'
                                        ? 'bg-green-500 text-white ring-2 ring-green-200'
                                        : 'bg-gray-100 text-gray-500 hover:bg-green-50'"
                                    class="px-3 py-1.5 rounded-full text-xs font-medium transition-all">
                                    حاضر
                                </button>
                                <button type="button"
                                    @click="attendanceSetStatus(student.id, 'late')"
                                    :class="attendanceStatuses[String(student.id)] === 'late'
                                        ? 'bg-yellow-400 text-white ring-2 ring-yellow-100'
                                        : 'bg-gray-100 text-gray-500 hover:bg-yellow-50'"
                                    class="px-3 py-1.5 rounded-full text-xs font-medium transition-all">
                                    متأخر
                                </button>
                                <button type="button"
                                    @click="attendanceSetStatus(student.id, 'absent')"
                                    :class="attendanceStatuses[String(student.id)] === 'absent'
                                        ? 'bg-red-500 text-white ring-2 ring-red-200'
                                        : 'bg-gray-100 text-gray-500 hover:bg-red-50'"
                                    class="px-3 py-1.5 rounded-full text-xs font-medium transition-all">
                                    غائب
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between shrink-0">
                    <p class="text-sm text-gray-500"
                       x-show="attendanceUnmarked > 0"
                       x-text="'تبقّى ' + attendanceUnmarked + ' طالب بدون تسجيل'"></p>
                    <p class="text-sm text-green-600 font-medium" x-show="attendanceUnmarked === 0">
                        جميع الطلاب مسجّلون ✓
                    </p>
                    <div class="flex gap-3">
                        <button type="button" @click="showAttendanceModal = false"
                            class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                            إلغاء
                        </button>
                        <button type="button" @click="saveAttendance()"
                            :disabled="savingAttendance || attendanceUnmarked > 0"
                            :class="attendanceUnmarked === 0 && !savingAttendance
                                ? 'bg-purple-600 hover:bg-purple-700 cursor-pointer'
                                : 'bg-gray-300 cursor-not-allowed'"
                            class="px-5 py-2 text-sm font-medium text-white rounded-lg transition-colors disabled:opacity-70">
                            <span x-show="!savingAttendance">حفظ الحضور</span>
                            <span x-show="savingAttendance">
                                <i class="fas fa-spinner fa-spin ml-1"></i> جار الحفظ...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
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
        searchLectures: '{{ route("admin.lectures.data") }}',
        groupsData: '{{ route("admin.groups.data") }}',
        getSubjects: '{{ route("admin.groups.subjects.available") }}',
        getGroupSubjects: '{{ route("admin.groups.subjects.for-lectures") }}',
        getTeachers: '{{ route("admin.groups.teachers.available") }}',
        activeSeries:  '{{ route("admin.lectures.active-series") }}',
        seriesShow:    '{{ url("/admin/lectures/series") }}',
        seriesUpdate:  '{{ url("/admin/lectures/series") }}',
        endSeries:                  '{{ url("/admin/lectures") }}',
        lectureAttendanceStudents:  '{{ url("/admin/lectures") }}',
        updateLecture:              '{{ url("/admin/lectures") }}',
    };
</script>
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
            miniCalDate: new Date(),
            miniCalHoverDay: null,
            subjectColorMap: {},
            calendarPopover: { show: false, x: 0, y: 0, event: null },
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

            // فلاتر التقارير
            reportFilters: {
                dateFrom: '',
                dateTo: '',
                groupId: '',
                teacherId: '',
            },

            // Modals
            showCreateLectureModal: false,
            showCreateSeriesModal: false,
            showFinalExamModal: false,
            showRescheduleModal: false,
            showEditSeriesModal: false,
            showAttendanceModal: false,
            showEditLectureModal: false,
            showViewLectureModal: false,

            // حضور الأدمن
            attendanceLecture: null,
            attendanceStudents: [],
            attendanceStatuses: {},
            loadingAttendance: false,
            savingAttendance: false,
            today: new Date().toISOString().slice(0, 10),

            // تعديل السلسلة
            editingSeriesId: null,
            savingSeriesEdit: false,
            editSeriesOriginalDays: [],
            editSeriesForm: {
                title: '', start_time: '', end_time: '', end_date: '',
                teacher_id: '', subject_id: '', description: '', days: [],
                regenerate: false,
            },
            editSeriesTeachers: [],
            editSeriesSubjects: [],
            editSeriesFutureCount: 0,

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
            viewingLecture: null,

            editLectureData: {
                id: null,
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
            editLectureTeachers: [],
            editLectureSubjects: [],
            loadingEditLectureSubjects: false,
            updatingLecture: false,

            // التنبيهات
            showAlert: false,
            alertType: 'success',
            alertMessage: '',

            // Computed properties
            get filteredLectures() {
                const today = new Date().toISOString().split('T')[0];
                return this.lectures.filter(lecture => {
                    const matchesSearch = lecture.title.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                        lecture.teacher_name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                        lecture.group_name.toLowerCase().includes(this.searchTerm.toLowerCase());

                    const matchesStatus = !this.filters.status || lecture.status === this.filters.status;
                    const matchesType = !this.filters.type || lecture.type === this.filters.type;
                    const matchesDateFrom = !this.filters.date_from || lecture.date >= this.filters.date_from;
                    const matchesDateTo = !this.filters.date_to || lecture.date <= this.filters.date_to;

                    return matchesSearch && matchesStatus && matchesType && matchesDateFrom && matchesDateTo;
                }).sort((a, b) => {
                    const aFuture = a.date >= today;
                    const bFuture = b.date >= today;
                    if (aFuture && bFuture) {
                        // كلاهما اليوم أو المستقبل → تصاعدي (الأقرب أولاً)
                        return a.date !== b.date
                            ? a.date.localeCompare(b.date)
                            : a.start_time.localeCompare(b.start_time);
                    }
                    if (!aFuture && !bFuture) {
                        // كلاهما ماضٍ → تنازلي (الأحدث أولاً)
                        return a.date !== b.date
                            ? b.date.localeCompare(a.date)
                            : b.start_time.localeCompare(a.start_time);
                    }
                    // المستقبل قبل الماضي
                    return aFuture ? -1 : 1;
                });
            },

            get reportLectures() {
                const f = this.reportFilters;
                return this.lectures.filter(l => {
                    if (f.dateFrom && l.date < f.dateFrom) return false;
                    if (f.dateTo   && l.date > f.dateTo)   return false;
                    if (f.groupId   && l.group_id   != f.groupId)   return false;
                    if (f.teacherId && l.teacher_id != f.teacherId) return false;
                    return true;
                });
            },

            get reportSummary() {
                const ls = this.reportLectures;
                const total      = ls.length;
                const completed  = ls.filter(l => l.status === 'completed').length;
                const cancelled  = ls.filter(l => l.status === 'cancelled').length;
                const rescheduled = ls.filter(l => l.status === 'rescheduled').length;
                const scheduled  = ls.filter(l => l.status === 'scheduled').length;
                return {
                    total, completed, cancelled, rescheduled, scheduled,
                    completionRate:   total ? Math.round(completed  / total * 100) : 0,
                    cancellationRate: total ? Math.round(cancelled  / total * 100) : 0,
                };
            },

            get reportTeachers() {
                const map = {};
                this.reportLectures.forEach(l => {
                    if (!l.teacher_id) return;
                    if (!map[l.teacher_id]) map[l.teacher_id] = {
                        id: l.teacher_id, name: l.teacher_name,
                        total: 0, completed: 0, cancelled: 0, rescheduled: 0,
                    };
                    map[l.teacher_id].total++;
                    if (l.status === 'completed')   map[l.teacher_id].completed++;
                    if (l.status === 'cancelled')   map[l.teacher_id].cancelled++;
                    if (l.status === 'rescheduled') map[l.teacher_id].rescheduled++;
                });
                return Object.values(map).sort((a, b) => b.total - a.total);
            },

            get reportGroups() {
                const map = {};
                this.reportLectures.forEach(l => {
                    if (!l.group_id) return;
                    if (!map[l.group_id]) map[l.group_id] = {
                        id: l.group_id, name: l.group_name,
                        total: 0, completed: 0, cancelled: 0, subjectSet: new Set(),
                    };
                    map[l.group_id].total++;
                    if (l.status === 'completed') map[l.group_id].completed++;
                    if (l.status === 'cancelled') map[l.group_id].cancelled++;
                    if (l.subject_name) map[l.group_id].subjectSet.add(l.subject_name);
                });
                return Object.values(map).map(g => ({
                    id: g.id, name: g.name,
                    total: g.total, completed: g.completed, cancelled: g.cancelled,
                    subjects: g.subjectSet.size,
                })).sort((a, b) => b.total - a.total);
            },

            get reportCancellations() {
                return this.reportLectures
                    .filter(l => l.status === 'cancelled' || l.status === 'rescheduled')
                    .sort((a, b) => b.date.localeCompare(a.date));
            },

            get reportAvailableTeachers() {
                const seen = new Set();
                return this.lectures.filter(l => {
                    if (!l.teacher_id || seen.has(l.teacher_id)) return false;
                    seen.add(l.teacher_id);
                    return true;
                }).map(l => ({ id: l.teacher_id, name: l.teacher_name }));
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
                        this.buildSubjectColorMap();
                    }
                } catch (error) {
                    console.error('Error loading lectures:', error);
                    throw error;
                }
            },

            async loadActiveSeries() {
                try {
                if (!window.routes.activeSeries) {
                console.warn('Active series route not defined');
                this.activeSeries = [];
                return;
                }

                const response = await fetch(window.routes.activeSeries, {
                headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
                });

                if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                this.activeSeries = data.series || [];
                } else {
                console.error('API returned error:', data.message);
                this.activeSeries = [];
                }
                } catch (error) {
                console.error('Error loading active series:', error);
                this.activeSeries = [];
                // يمكن إضافة رسالة خطأ للمستخدم
                this.showAlertMessage('error', 'حدث خطأ في تحميل السلاسل النشطة');
                }
            },

            async endSeries(series) {
                if (!confirm(`هل أنت متأكد من إنهاء السلسلة "${series.title}"؟\nسيتم إلغاء جميع المحاضرات المستقبلية المجدولة.`)) return;
                try {
                    const response = await fetch(`${window.routes.endSeries}/${series.series_id}/end-series`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.showAlertMessage('success', data.message || 'تم إنهاء السلسلة بنجاح');
                        await this.loadActiveSeries();
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    this.showAlertMessage('error', 'حدث خطأ في إنهاء السلسلة: ' + error.message);
                }
            },

            async editSeries(series) {
                this.editingSeriesId        = series.series_id;
                this.editSeriesOriginalDays = [...(series.days || [])];
                this.editSeriesFutureCount  = series.remaining_lectures || 0;

                // نضبط كل الحقول — teacher_id وsubject_id نتركهم فارغين مؤقتاً
                this.editSeriesForm = {
                    title:       series.title       || '',
                    start_time:  series.start_time  || '',
                    end_time:    series.end_time     || '',
                    end_date:    series.end_date     || '',
                    teacher_id:  '',
                    subject_id:  '',
                    description: series.description  || '',
                    days:        (series.days || []).map(String),
                    regenerate:  false,
                };

                // ننتظر تحميل القوائم أولاً
                await this.loadEditSeriesDropdowns(series.group_id);

                // بعد تحميل الخيارات نضبط القيمتين — Alpine يختار الخيار الصحيح
                this.editSeriesForm.teacher_id = String(series.teacher_id || '');
                this.editSeriesForm.subject_id = String(series.subject_id || '');

                this.showEditSeriesModal = true;
            },

            async loadEditSeriesDropdowns(groupId, currentTeacherId, currentSubjectId) {
                if (!groupId) return;
                const headers = {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                };
                try {
                    const [tRes, sRes] = await Promise.all([
                        fetch(`${window.routes.getTeachers}?group_id=${groupId}`, { headers }),
                        fetch(`${window.routes.getGroupSubjects}?group_id=${groupId}`, { headers }),
                    ]);
                    if (tRes.ok) {
                        const td = await tRes.json();
                        this.editSeriesTeachers = td.success ? this.validateTeachersData(td.teachers || []) : [];
                    }
                    if (sRes.ok) {
                        const sd = await sRes.json();
                        this.editSeriesSubjects = sd.success ? this.validateSubjectsData(sd.subjects || []) : [];
                    }
                } catch (e) {
                    console.error('Error loading edit series dropdowns', e);
                }
            },

            get editSeriesDaysChanged() {
                const orig = [...this.editSeriesOriginalDays].sort().join(',');
                const curr = [...this.editSeriesForm.days].sort().join(',');
                return orig !== curr;
            },

            async saveSeriesEdit() {
                if (this.savingSeriesEdit) return;
                this.savingSeriesEdit = true;
                try {
                    const response = await fetch(`${window.routes.seriesUpdate}/${this.editingSeriesId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.editSeriesForm),
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.showAlertMessage('success', data.message || 'تم تحديث السلسلة بنجاح');
                        this.showEditSeriesModal = false;
                        await this.loadData();
                    } else if (response.status === 422) {
                        const firstErr = Object.values(data.errors || {})[0];
                        this.showAlertMessage('error', Array.isArray(firstErr) ? firstErr[0] : (data.message || 'بيانات غير صحيحة'));
                    } else {
                        throw new Error(data.message || 'فشل التحديث');
                    }
                } catch (error) {
                    this.showAlertMessage('error', 'حدث خطأ: ' + error.message);
                } finally {
                    this.savingSeriesEdit = false;
                }
            },

            async loadAvailableData() {
                const headers = {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };

                try {
                    const fetches = [];
                    if (window.routes.groupsData) fetches.push(fetch(window.routes.groupsData, { headers }));
                    if (window.routes.getSubjects) fetches.push(fetch(window.routes.getSubjects, { headers }));

                    const responses = await Promise.all(fetches);
                    const results = await Promise.all(responses.map(r => r.json()));

                    let idx = 0;
                    if (window.routes.groupsData) {
                        if (results[idx]?.success) this.availableGroups = results[idx].groups;
                        idx++;
                    }
                    if (window.routes.getSubjects) {
                        if (results[idx]?.success) this.availableSubjects = results[idx].subjects;
                    }

                } catch (error) {
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
            }
            }

            // معالجة المواد
            if (responses[1] && responses[1].ok) {
            const subjectsData = await responses[1].json();
            if (subjectsData.success) {
            this.seriesFilteredSubjects = this.validateSubjectsData(subjectsData.subjects || []);
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

            // حضور الأدمن
            async openAttendanceModal(lecture) {
                this.attendanceLecture  = lecture;
                this.attendanceStudents = [];
                this.attendanceStatuses = {};
                this.loadingAttendance  = true;
                this.showAttendanceModal = true;
                try {
                    const res  = await fetch(`${window.routes.lectureAttendanceStudents}/${lecture.id}/attendance-students`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.attendanceStudents = data.students;
                        const norm = {};
                        Object.entries(data.existing_statuses).forEach(([k, v]) => { norm[String(k)] = v; });
                        this.attendanceStatuses = norm;
                    }
                } catch (e) {
                    this.showAlertMessage('error', 'تعذّر تحميل بيانات الطلاب');
                    this.showAttendanceModal = false;
                } finally {
                    this.loadingAttendance = false;
                }
            },

            attendanceMarkAll(status) {
                const updated = {};
                this.attendanceStudents.forEach(s => { updated[String(s.id)] = status; });
                this.attendanceStatuses = updated;
            },

            attendanceSetStatus(studentId, status) {
                this.attendanceStatuses = { ...this.attendanceStatuses, [String(studentId)]: status };
            },

            get attendanceUnmarked() {
                return this.attendanceStudents.filter(s => !this.attendanceStatuses[String(s.id)]).length;
            },

            get attendanceCounts() {
                const vals = Object.values(this.attendanceStatuses);
                return {
                    present: vals.filter(v => v === 'present').length,
                    late:    vals.filter(v => v === 'late').length,
                    absent:  vals.filter(v => v === 'absent').length,
                };
            },

            async saveAttendance() {
                if (this.attendanceUnmarked > 0) return;
                this.savingAttendance = true;
                try {
                    const res  = await fetch(`${window.routes.lectureAttendanceStudents}/${this.attendanceLecture.id}/attendance`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ attendance: this.attendanceStatuses }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.showAttendanceModal = false;
                        this.showAlertMessage('success', data.message);
                    } else {
                        this.showAlertMessage('error', data.message || 'حدث خطأ في الحفظ');
                    }
                } catch (e) {
                    this.showAlertMessage('error', 'تعذّر الاتصال بالخادم');
                } finally {
                    this.savingAttendance = false;
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
            async editLecture(lecture) {
                this.editLectureData = {
                    id:          lecture.id,
                    title:       lecture.title,
                    type:        lecture.type       || 'lecture',
                    date:        lecture.date,
                    start_time:  lecture.start_time ? lecture.start_time.substring(0, 5) : '',
                    end_time:    lecture.end_time   ? lecture.end_time.substring(0, 5)   : '',
                    teacher_id:  lecture.teacher_id || (lecture.teacher ? lecture.teacher.id : ''),
                    group_id:    lecture.group_id   || (lecture.group   ? lecture.group.id   : ''),
                    subject_id:  lecture.subject_id || (lecture.subject ? lecture.subject.id : ''),
                    description: lecture.description || ''
                };
                this.editLectureTeachers = [];
                this.editLectureSubjects = [];
                this.showEditLectureModal = true;
                // تحميل المدرسين والمواد للمجموعة المختارة
                if (this.editLectureData.group_id) {
                    await this.loadEditLectureGroupData(this.editLectureData.group_id);
                }
            },

            async loadEditLectureGroupData(groupId) {
                this.loadingEditLectureSubjects = true;
                const headers = {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };
                try {
                    const [tRes, sRes] = await Promise.all([
                        fetch(`${window.routes.getTeachers}?group_id=${groupId}`, { headers }),
                        fetch(`${window.routes.getGroupSubjects}?group_id=${groupId}`, { headers })
                    ]);
                    if (tRes.ok) {
                        const d = await tRes.json();
                        this.editLectureTeachers = d.teachers || [];
                    }
                    if (sRes.ok) {
                        const d = await sRes.json();
                        this.editLectureSubjects = d.subjects || [];
                    }
                } catch (e) {
                    console.error('فشل تحميل بيانات المجموعة للتعديل:', e);
                } finally {
                    this.loadingEditLectureSubjects = false;
                }
            },

            async updateLecture() {
                this.updatingLecture = true;
                try {
                    const response = await fetch(`${window.routes.updateLecture}/${this.editLectureData.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.editLectureData)
                    });
                    const data = await response.json();
                    if (data.success) {
                        // تحديث المحاضرة في القائمة المحلية
                        const idx = this.lectures.findIndex(l => l.id === this.editLectureData.id);
                        if (idx !== -1) this.lectures.splice(idx, 1, data.lecture);
                        const uIdx = this.upcomingLectures.findIndex(l => l.id === this.editLectureData.id);
                        if (uIdx !== -1) this.upcomingLectures.splice(uIdx, 1, data.lecture);
                        this.showEditLectureModal = false;
                        this.showAlertMessage('success', data.message || 'تم تحديث المحاضرة بنجاح');
                        if (this.calendar) this.calendar.refetchEvents();
                    } else {
                        throw new Error(data.message || 'فشل في تحديث المحاضرة');
                    }
                } catch (error) {
                    this.showAlertMessage('error', error.message);
                } finally {
                    this.updatingLecture = false;
                }
            },

            viewLecture(lecture) {
                this.viewingLecture = lecture;
                this.showViewLectureModal = true;
            },

            // فلترة التقويم
            filterCalendar() {
                if (this.calendar) {
                    this.calendar.refetchEvents();
                }
            },

            // تطبيق الفلاتر
            applyFilters() {
                // الفلترة تحدث تلقائياً عبر filteredLectures getter
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

            // تحميل FullCalendar عند الحاجة فقط
            loadFullCalendar() {
                if (window.FullCalendar) return Promise.resolve();
                return new Promise((resolve, reject) => {
                    if (!document.querySelector('link[data-fc]')) {
                        const link = document.createElement('link');
                        link.rel = 'stylesheet';
                        link.setAttribute('data-fc', '1');
                        link.href = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css';
                        document.head.appendChild(link);
                    }
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js';
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            },

            // تهيئة التقويم
            async initializeCalendar() {
                await this.loadFullCalendar();
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
                const formattedEvents = events.map(event => {
                const ep = event.extendedProps || {};
                return {
                id: event.id,
                title: event.title,
                start: event.start,
                end: event.end,
                backgroundColor: 'transparent',
                borderColor: 'transparent',
                extendedProps: {
                type:           ep.type         || 'lecture',
                status:         ep.status       || 'scheduled',
                teacher_name:   ep.teacher_name || '',
                group_name:     ep.group_name   || '',
                subject_name:   ep.subject_name || '',
                subject_id:     ep.subject_id   || null,
                start_time:     ep.start_time   || '',
                end_time:       ep.end_time     || '',
                students_count: ep.students_count || 0,
                series_id:      ep.series_id,
                description:    ep.description  || '',
                }
                };
                });

                successCallback(formattedEvents);
                } catch (error) {
                console.error('Error loading calendar events:', error);
                failureCallback(error);
                }
                },

                eventContent: (arg) => {
                const type = arg.event.extendedProps.type || 'lecture';
                const actColor = this.getActivityColor(type);
                const symbol = this.getActivitySymbol(type);
                const subjectName = arg.event.extendedProps.subject_name;
                const subjectId = arg.event.extendedProps.subject_id;
                const subColor = this.getSubjectColor(subjectId, subjectName);
                const status = arg.event.extendedProps.status;
                const isCancelled = status === 'cancelled';
                const isRescheduled = status === 'rescheduled';
                const title = arg.event.title;
                const el = document.createElement('div');

                if (arg.view.type === 'dayGridMonth') {
                    el.style.cssText = `display:flex;align-items:center;gap:3px;padding:1px 5px;border-radius:4px;background:${actColor}1a;border-right:3px solid ${actColor};opacity:${isCancelled?0.5:1};text-decoration:${isCancelled?'line-through':'none'};width:100%;overflow:hidden;cursor:pointer;outline:${isRescheduled?'2px dashed '+actColor:'none'};`;
                    el.innerHTML = `<span style="background:${actColor};color:#fff;font-weight:700;font-size:9px;width:14px;height:14px;border-radius:3px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">${symbol}</span><span style="font-size:11px;color:#1f2937;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;flex:1;">${title}</span><span style="width:6px;height:6px;border-radius:50%;background:${subColor};flex-shrink:0;"></span>`;
                } else {
                    el.style.cssText = `padding:4px 6px;border-right:4px solid ${actColor};background:${subColor}18;height:100%;width:100%;border-radius:4px;overflow:hidden;opacity:${isCancelled?0.6:1};cursor:pointer;box-sizing:border-box;${isRescheduled?'border-style:dashed;':''}`;
                    el.innerHTML = `<div style="display:flex;align-items:center;gap:3px;margin-bottom:2px;"><span style="background:${actColor};color:#fff;font-weight:700;font-size:9px;padding:1px 5px;border-radius:3px;flex-shrink:0;">${symbol}</span><span style="font-size:11px;font-weight:600;color:#111827;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">${title}</span></div><div style="font-size:10px;color:#6b7280;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">${arg.event.extendedProps.teacher_name||''}</div><div style="font-size:10px;color:#9ca3af;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;"><span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:${subColor};margin-left:3px;vertical-align:middle;"></span>${arg.event.extendedProps.group_name||''}</div>`;
                }
                return { domNodes: [el] };
                },

                eventDidMount: (arg) => {
                arg.el.style.border = 'none';
                arg.el.style.backgroundColor = 'transparent';
                arg.el.style.boxShadow = 'none';
                arg.el.style.padding = '0';
                },

                eventClick: (info) => {
                info.jsEvent.preventDefault();
                const rect = info.el.getBoundingClientRect();
                const popW = 280, popH = 380;
                let left = rect.left - popW - 10;
                if (left < 8) left = rect.right + 10;
                if (left + popW > window.innerWidth - 8) left = window.innerWidth - popW - 8;
                let top = rect.top;
                if (top + popH > window.innerHeight - 8) top = window.innerHeight - popH - 8;
                if (top < 8) top = 8;
                const e = info.event;
                const ep = e.extendedProps;

                // تنسيق التاريخ والوقت
                const startIso = e.startStr || '';
                const datePart = startIso.split('T')[0] || '';
                let dateLabel = '';
                if (datePart) {
                    const [y, m, d] = datePart.split('-');
                    const months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
                    const dayNames = ['الأحد','الإثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
                    const dateObj = new Date(datePart + 'T12:00:00');
                    dateLabel = `${dayNames[dateObj.getDay()]} ${parseInt(d)} ${months[parseInt(m)-1]} ${y}`;
                }
                const startTime = ep.start_time ? ep.start_time.slice(0,5) : (startIso.split('T')[1]||'').slice(0,5);
                const endTime   = ep.end_time   ? ep.end_time.slice(0,5)   : (e.endStr||'').split('T')[1]?.slice(0,5) || '';

                this.calendarPopover = {
                    show: true, x: left, y: top,
                    event: {
                        title:        e.title,
                        type:         ep.type         || 'lecture',
                        status:       ep.status       || 'scheduled',
                        teacher_name: ep.teacher_name || '',
                        group_name:   ep.group_name   || '',
                        subject_name: ep.subject_name || '',
                        subject_id:   ep.subject_id   || null,
                        description:  ep.description  || '',
                        students_count: ep.students_count || 0,
                        date_label:   dateLabel,
                        start_time:   startTime,
                        end_time:     endTime,
                    }
                };
                },
                });

                this.calendar.render();

                calendarEl.addEventListener('click', (e) => {
                    if (!e.target.closest('.fc-event')) this.calendarPopover.show = false;
                });
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') this.calendarPopover.show = false;
                });
                }
                });
            },

            // ===== التقويم السريع المخصص =====

            initializeMiniCalendar() { /* مستبدل بالتقويم السريع المخصص */ },

            miniCalPrev() {
                const d = new Date(this.miniCalDate);
                d.setMonth(d.getMonth() - 1);
                this.miniCalDate = d;
            },

            miniCalNext() {
                const d = new Date(this.miniCalDate);
                d.setMonth(d.getMonth() + 1);
                this.miniCalDate = d;
            },

            miniCalMonthLabel() {
                const months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو',
                                'يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
                return months[this.miniCalDate.getMonth()] + ' ' + this.miniCalDate.getFullYear();
            },

            getMiniCalDays() {
                const y = this.miniCalDate.getFullYear();
                const m = this.miniCalDate.getMonth();
                const firstDow = new Date(y, m, 1).getDay();
                const daysInMonth = new Date(y, m + 1, 0).getDate();
                const cells = [];
                for (let i = 0; i < firstDow; i++) cells.push(null);
                for (let d = 1; d <= daysInMonth; d++) {
                    cells.push(`${y}-${String(m + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`);
                }
                return cells;
            },

            miniCalTodayStr() {
                const t = new Date();
                return `${t.getFullYear()}-${String(t.getMonth()+1).padStart(2,'0')}-${String(t.getDate()).padStart(2,'0')}`;
            },

            isToday(dateStr) { return dateStr === this.miniCalTodayStr(); },

            getMiniCalEvents(dateStr) {
                if (!dateStr) return [];
                return this.lectures.filter(l => l.date === dateStr && l.status !== 'cancelled');
            },

            getMiniCalTypeBadges(dateStr) {
                const events = this.getMiniCalEvents(dateStr);
                if (!events.length) return [];
                const order = ['final_exam','exam','review','lecture','activity'];
                const counts = {};
                events.forEach(e => { counts[e.type] = (counts[e.type] || 0) + 1; });
                return order.filter(t => counts[t]).map(t => ({
                    type: t, count: counts[t],
                    symbol: this.getActivitySymbol(t),
                    color: this.getActivityColor(t),
                }));
            },

            getActivityColor(type) {
                return { lecture:'#3B82F6', exam:'#EF4444', review:'#F59E0B',
                         activity:'#10B981', final_exam:'#DC2626' }[type] || '#6B7280';
            },

            getActivitySymbol(type) {
                return { lecture:'م', exam:'ا', review:'ر',
                         activity:'ش', final_exam:'ن' }[type] || '؟';
            },

            buildSubjectColorMap() {
                const palette = ['#8B5CF6','#EC4899','#06B6D4','#6366F1','#F97316',
                                  '#14B8A6','#84CC16','#F43F5E','#7C3AED','#0EA5E9'];
                const map = {};
                let idx = 0;
                this.lectures.forEach(l => {
                    const key = l.subject_name || String(l.subject_id);
                    if (key && !map[key]) {
                        map[key] = palette[idx % palette.length];
                        idx++;
                    }
                });
                this.subjectColorMap = map;
            },

            getSubjectColor(subjectId, subjectName) {
                const key = subjectName || String(subjectId);
                if (!key) return '#9CA3AF';
                return this.subjectColorMap[key] || '#9CA3AF';
            },

            getMiniCalDisplayEvents() {
                const day = this.miniCalHoverDay || this.miniCalTodayStr();
                return this.getMiniCalEvents(day).sort((a,b) => a.start_time.localeCompare(b.start_time));
            },

            getMiniCalDisplayLabel() {
                const day = this.miniCalHoverDay || this.miniCalTodayStr();
                if (!day) return '';
                const [y, m, d] = day.split('-');
                const months = ['يناير','فبراير','مارس','أبريل','مايو','يونيو',
                                'يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
                const days   = ['الأحد','الإثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
                const date   = new Date(`${y}-${m}-${d}`);
                return `${days[date.getDay()]} ${parseInt(d)} ${months[parseInt(m)-1]}`;
            },

            goToCalendarDate(dateStr) {
                this.activeTab = 'calendar';
                this.$nextTick(() => { if (this.calendar) this.calendar.gotoDate(dateStr); });
            },

            // مراقبة تغيير التبويبات
            watchTabChange() {
                this.$watch('activeTab', (newTab) => {
                    this.calendarPopover.show = false;
                    if (newTab === 'calendar') this.initializeCalendar();
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

            // تهيئة المكون
            init() {
                this.watchTabChange();

                // تهيئة التقويم المصغر عند تحميل لوحة التحكم
                if (this.activeTab === 'dashboard') {
                    this.initializeMiniCalendar();
                }

                // مراقبة تغيير الحقول المؤثرة في التضارب فقط (مع debounce 300ms)
                let conflictTimer = null;
                this.$watch(
                    () => `${this.newLecture.teacher_id}|${this.newLecture.date}|${this.newLecture.start_time}|${this.newLecture.end_time}`,
                    (val) => {
                        clearTimeout(conflictTimer);
                        const [tid, d, st, et] = val.split('|');
                        if (tid && d && st && et) {
                            conflictTimer = setTimeout(() => this.checkTimeConflicts(), 300);
                        } else {
                            this.timeConflicts = [];
                        }
                    }
                );
            }
        }

    }
</script>
@endpush
