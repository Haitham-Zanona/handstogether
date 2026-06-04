@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle = 'البوابة الإدارية';
$pageTitle = 'طلبات الانتساب';
$pageDescription = 'إدارة ومراجعة طلبات انتساب الطلاب الجدد';
@endphp

@push('styles')

<style>
    :root {
        --primary-blue: #2778E5;
        --primary-orange: #EE8100;
        --white: #ffffff;
        --black: #000000;
    }

    /* خليه RTL لو واجهتك بالعربي */
    .flatpickr-calendar animate arrowTop arrowLeft open {
        direction: rtl;
        background: var(--white) !important;
        border: 2px solid var(--primary-orange) !important;
        border-radius: 12px !important;
        box-shadow: 0 8px 20px rgba(0, 0, 0, .15) !important;
        overflow: hidden !important;
    }

    /* الهيدر (الشهر/السنة) */
    .flatpickr-months {
        background: var(--primary-blue) !important;
        color: var(--white) !important;
        border-bottom: 2px solid var(--primary-orange) !important;
        padding: 8px 0 !important;
    }

    /* عنوان الشهر والسنة */
    .flatpickr-current-month .flatpickr-monthDropdown-months,
    .flatpickr-current-month .cur-year {
        background: transparent !important;
        border: none !important;
        color: var(--white) !important;
        font-weight: 700 !important;
        font-size: 16px !important;
    }

    /* أزرار السابق/التالي (لاحظ إنها داخل .flatpickr-months) */
    .flatpickr-months .flatpickr-prev-month,
    .flatpickr-months .flatpickr-next-month {
        background: var(--primary-orange) !important;
        border-radius: 50% !important;
        padding: 6px !important;
        cursor: pointer !important;
        transition: background .2s ease !important;
    }

    .flatpickr-months .flatpickr-prev-month svg path,
    .flatpickr-months .flatpickr-next-month svg path {
        fill: var(--white) !important;
    }

    .flatpickr-months .flatpickr-prev-month:hover,
    .flatpickr-months .flatpickr-next-month:hover {
        background: var(--primary-blue) !important;
    }

    /* أسماء أيام الأسبوع */
    .flatpickr-weekdaycontainer .flatpickr-weekday {
        color: var(--primary-orange) !important;
        font-weight: 700 !important;
        font-size: 14px !important;
    }

    /* شبكة الأيام */
    .flatpickr-days {
        background: var(--white) !important;
    }

    .flatpickr-day {
        border-radius: 8px !important;
        transition: background .15s ease, color .15s ease, transform .05s ease !important;
    }

    /* اليوم الحالي (بحيث ما يغطي على selected) */
    .flatpickr-day.today:not(.selected) {
        background: rgba(238, 129, 0, .15) !important;
        color: var(--primary-orange) !important;
        font-weight: 700 !important;
    }

    /* اليوم المحدد + بداية/نهاية الرينج */
    .flatpickr-day.selected,
    .flatpickr-day.selected:hover,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        background: var(--primary-blue) !important;
        color: var(--white) !important;
        border-color: var(--primary-blue) !important;
        font-weight: 700 !important;
    }

    /* أيام داخل الرينج */
    .flatpickr-day.inRange {
        background: rgba(39, 120, 229, .15) !important;
        color: var(--black) !important;
        border-color: transparent !important;
    }

    /* هوفر عام */
    .flatpickr-day:hover {
        background: var(--primary-blue) !important;
        color: var(--white) !important;
    }

    /* أيام الشهر السابق/التالي */
    .flatpickr-day.prevMonthDay,
    .flatpickr-day.nextMonthDay {
        color: #9aa1ab !important;
        opacity: .7 !important;
    }

    /* تعطيل */
    .flatpickr-day.disabled,
    .flatpickr-day.notAllowed {
        color: #c0c4cc !important;
        background: transparent !important;
        cursor: not-allowed !important;
    }

    /* السهم العلوي للتقويم */
    .flatpickr-calendar.arrowTop:before {
        border-bottom-color: var(--primary-orange) !important;
    }

    .flatpickr-calendar.arrowTop:after {
        border-bottom-color: var(--white) !important;
    }


    /* تصميم التقويم المخصص */
    .custom-datepicker {
        position: relative;
    }

    .custom-datepicker input[type="date"] {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 10px;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        width: 100%;
    }

    .custom-datepicker input[type="date"]::-webkit-calendar-picker-indicator {
        background: white;
        border-radius: 3px;
        cursor: pointer;
    }

    /* تصميم الحقول مع الأخطاء */
    .field-error {
        border: 2px solid #ef4444 !important;
        background-color: #fef2f2;
    }

    .error-message {
        color: #ef4444;
        font-size: 12px;
        margin-top: 4px;
        display: none;
    }

    .field-error~.error-message {
        display: block;
    }

    /* تصميم popup النجاح */
    .success-popup {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .progress-bar {
        height: 4px;
        background: rgba(255, 255, 255, 0.8);
        border-radius: 2px;
        animation: shrink 5s linear forwards;
    }

    @keyframes shrink {
        from {
            width: 100%;
        }

        to {
            width: 0%;
        }
    }

    /* تحسين تصميم الفورم */
    .form-section {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .form-section h4 {
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 8px;
        border-bottom: 2px solid #667eea;
    }

    /* تحسين القوائم المنسدلة */
    select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: left 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-left: 2.5rem;
    }

    #application_number {
        letter-spacing: 0.15em;
    }

    .loading-spinner {
        /* أنيميشن التحميل */
    }
</style>
@endpush

@section('content')
<!-- Statistics -->
<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-4">
    <div class="p-4 border border-yellow-200 rounded-lg bg-yellow-50">
        <div class="flex items-center">
            <div class="p-2 ml-3 bg-yellow-500 rounded-full">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-600">في الانتظار</p>
                <p class="text-2xl font-bold text-yellow-700">{{ $admissions->where('status', 'pending')->count() }}</p>
            </div>
        </div>
    </div>

    <div class="p-4 border border-green-200 rounded-lg bg-green-50">
        <div class="flex items-center">
            <div class="p-2 ml-3 bg-green-500 rounded-full">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-600">مقبول</p>
                <p class="text-2xl font-bold text-green-700">{{ $admissions->where('status', 'approved')->count() }}</p>
            </div>
        </div>
    </div>

    <div class="p-4 border border-red-200 rounded-lg bg-red-50">
        <div class="flex items-center">
            <div class="p-2 ml-3 bg-red-500 rounded-full">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-600">مرفوض</p>
                <p class="text-2xl font-bold text-red-700">{{ $admissions->where('status', 'rejected')->count() }}</p>
            </div>
        </div>
    </div>

    <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
        <div class="flex items-center">
            <div class="p-2 ml-3 bg-blue-500 rounded-full">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-600">إجمالي الطلبات</p>
                <p class="text-2xl font-bold text-blue-700">{{ $admissions->count() }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Admissions Table -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 bg-white border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">طلبات الانتساب</h3>
            <div class="flex items-center space-x-3 space-x-reverse">
                <!-- البحث بالاسم مع زر -->
                <div class="flex items-center">
                    <input type="text" id="nameSearch" placeholder="البحث بالاسم..."
                        class="w-48 px-3 py-2 text-sm border border-gray-300 rounded-r-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button onclick="searchByName()"
                        class="px-3 py-2 text-sm transition-colors bg-gray-100 border border-r-0 border-gray-300 rounded-l-md hover:bg-gray-200">
                        🔍
                    </button>
                </div>

                <!-- قائمة المجموعات -->
                <select id="groupFilter"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">جميع المجموعات</option>
                    <!-- سيتم تعبئتها من قاعدة البيانات -->
                </select>

                <!-- فلتر الحالة -->
                <select id="statusFilter"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">جميع الحالات</option>
                    <option value="pending">في الانتظار</option>
                    <option value="approved">مقبول</option>
                    <option value="rejected">مرفوض</option>
                </select>

                <!-- زر الإضافة -->
                <button onclick="openAddAdmissionModal()"
                    class="px-4 py-2 mr-2 text-sm font-medium text-white transition-colors rounded-md bg-primary hover:bg-blue-700">
                    إضافة طلب جديد
                </button>
            </div>
        </div>
    </div>

    <div>
        <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 w-[22%]">اسم الطالب</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 w-[18%]">ولي الأمر</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 w-[14%]">المجموعة</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 w-[13%]">رقم الهاتف</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 w-[9%]">الحالة</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 w-[10%]">التاريخ</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 w-[14%]">الإجراءات</th>
                </tr>
            </thead>
            <tbody id="admissionsTableBody" class="bg-white divide-y divide-gray-200">
                @forelse($admissions as $admission)
                <tr class="hover:bg-gray-50 transition-colors" data-status="{{ $admission->status }}">
                    <td class="px-4 py-3">
                        <div class="text-sm font-semibold text-gray-900 leading-snug">{{ $admission->student_name }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">#{{ $admission->application_number }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm text-gray-800">{{ $admission->parent_name }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @if($admission->group)
                        <div class="text-sm text-gray-800">{{ $admission->group->name }}</div>
                        @else
                        <div class="text-xs text-gray-400 italic">غير محدد</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm text-gray-700 font-mono">{{ $admission->father_phone }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @php
                        $statusClasses = [
                            'pending'  => 'bg-yellow-100 text-yellow-800',
                            'approved' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                        ];
                        @endphp
                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusClasses[$admission->status] ?? '' }}">
                            {{ $admission->status_in_arabic }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                        {{ $admission->created_at->format('Y-m-d') }}
                    </td>
                    <td class="px-4 py-3">
                        @if($admission->status === 'pending')
                        <div class="flex items-center gap-2">
                            <button onclick="openApproveModal({{ $admission->id }}, '{{ $admission->student_name }}')"
                                class="text-xs font-semibold text-green-600 hover:text-green-800 transition-colors">
                                قبول
                            </button>
                            <span class="text-gray-300">|</span>
                            <button onclick="openRejectModal({{ $admission->id }})"
                                class="text-xs font-semibold text-red-500 hover:text-red-700 transition-colors">
                                رفض
                            </button>
                        </div>
                        @elseif($admission->status === 'approved')
                        <button onclick="showAdmissionCredentials({{ $admission->id }})"
                            class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-white bg-primary rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap">
                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            بيانات الدخول
                        </button>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr id="emptyRow">
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        لا توجد طلبات انتساب
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $admissions->links() }}
    </div>
</div>

<!-- Add New Admission Modal -->
<div id="add-admission-modal" class="fixed inset-0 z-50 hidden w-full h-full overflow-y-auto bg-gray-600/50">
    <div class="relative max-w-4xl mx-auto bg-white border rounded-lg shadow-lg top-10">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <button onclick="closeAddAdmissionModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div
            class="p-8 bg-gradient-to-br from-gray-50 to-white rounded-2xl shadow-2xl border-[3px] border-orange-500 max-w-6xl mx-auto font-[Segoe UI] text-right">

            <!-- Header -->
            <div class="pb-5 mb-8 text-center border-b-4 border-orange-500">
                <h1 class="text-3xl font-bold text-blue-600">🎓 نموذج انتساب جديد</h1>
                <p class="mt-2 text-sm text-gray-500">تاريخ الإصدار: {{ now()->format('Y/m/d') }}</p>
            </div>

            <form id="add-admission-form" method="POST" action="{{ route('admin.admissions.store') }}">
                @csrf

                <!-- 📋 بيانات الطلب -->
                <div class="p-6 mb-6 text-white bg-gradient-to-r from-blue-600 to-blue-900 rounded-xl">
                    <h4 class="pb-2 mb-4 text-lg font-semibold border-b-2 border-orange-500">📋 بيانات الطلب</h4>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <label class="block mb-2 text-sm font-medium">اليوم</label>
                            <select name="day" id="day"
                                class="w-full px-8 py-2 text-black bg-white border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                <option value="">اختر اليوم</option>
                                <option value="الأحد">الأحد</option>
                                <option value="الإثنين">الإثنين</option>
                                <option value="الثلاثاء">الثلاثاء</option>
                                <option value="الأربعاء">الأربعاء</option>
                                <option value="الخميس">الخميس</option>
                                <option value="الجمعة">الجمعة</option>
                                <option value="السبت">السبت</option>
                            </select>
                            <div class="error-message">يرجى اختيار اليوم</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium">تاريخ تقديم الطلب</label>
                            <input type="date" name="application_date" id="application_date"
                                class="w-full px-3 py-2 bg-white text-black border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                placeholder="YYYY-MM-DD" required>
                            <div class="error-message">يرجى اختيار تاريخ تقديم الطلب</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium">رقم الطلب</label>
                            <div class="relative">
                                <input type="text" id="application_number"
                                    class="w-full px-3 py-2 bg-gray-100 text-black border border-gray-300 rounded-md cursor-not-allowed font-mono text-center text-lg font-bold"
                                    placeholder="جاري التحميل..." readonly>
                                <div id="app-number-loading" class="absolute left-3 top-1/2 -translate-y-1/2 hidden">
                                    <svg class="animate-spin h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.568 3 7.291l3-2.291z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div id="app-number-warning" class="mt-1 text-sm text-yellow-700 bg-yellow-50 border border-yellow-300 rounded px-2 py-1 hidden">
                                ⚠️ تحذير: اقتربت من الحد الأقصى لأرقام الطلبات، المتبقي: <span id="app-number-remaining"></span> رقم
                            </div>
                            <p class="mt-1 text-xs text-gray-500">يُولَّد تلقائياً عند الحفظ</p>
                        </div>
                    </div>
                </div>

                <!-- 👨‍🎓 بيانات الطالب -->
                <div class="p-6 mb-6 bg-white border-r-4 border-blue-600 shadow-sm rounded-xl">
                    <h4 class="pb-2 mb-4 text-lg font-semibold text-blue-600 border-b-2 border-orange-500">👨‍🎓 بيانات
                        الطالب
                    </h4>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium">اسم الطالب</label>
                            <input type="text" name="student_name" id="student_name"
                                class="w-full px-3 py-2 text-black border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                placeholder="الطالب الأب الجد العائلة" required>
                            <div class="error-message">يرجى إدخال الاسم الرباعي كاملاً</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium">رقم الهوية</label>
                            <input type="text" name="student_id" id="student_id" maxlength="9" placeholder="9 أرقام"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <div class="error-message">يرجى إدخال رقم هوية صحيح (9 أرقام)</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium">تاريخ الميلاد</label>
                            <input type="date" name="birth_date" id="birth_date"
                                class="w-full px-3 py-2 text-black border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                placeholder="YYYY-MM-DD" required>
                            <div class="error-message">يرجى اختيار تاريخ الميلاد</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium">المرحلة الدراسية</label>
                            <select name="grade" id="grade"
                                class="w-full px-8 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="">اختر المرحلة الدراسية</option>
                                <option value="صف أول ابتدائي">صف أول ابتدائي</option>
                                <option value="صف ثاني ابتدائي">صف ثاني ابتدائي</option>
                                <option value="صف ثالث ابتدائي">صف ثالث ابتدائي</option>
                                <option value="صف رابع ابتدائي">صف رابع ابتدائي</option>
                                <option value="صف خامس ابتدائي">صف خامس ابتدائي</option>
                                <option value="صف سادس ابتدائي">صف سادس ابتدائي</option>
                                <option value="صف سابع">صف سابع</option>
                                <option value="صف ثامن">صف ثامن</option>
                                <option value="صف تاسع">صف تاسع</option>
                                <option value="صف عاشر">صف عاشر</option>
                            </select>
                            <div class="error-message">يرجى اختيار المرحلة الدراسية</div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium">المستوى الأكاديمي</label>
                            <select name="academic_level" id="academic_level"
                                class="w-full px-8 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="">اختر المستوى الأكاديمي</option>
                                <option value="راسب">راسب</option>
                                <option value="مقبول">مقبول</option>
                                <option value="جيد">جيد</option>
                                <option value="جيد جداً">جيد جداً</option>
                                <option value="ممتاز">ممتاز</option>
                                <option value="ممتاز جداً">ممتاز جداً</option>
                            </select>
                            <div class="error-message">يرجى اختيار المستوى الأكاديمي</div>
                        </div>
                    </div>
                </div>

                <!-- 👨‍👩‍👦 بيانات ولي الأمر -->
                <div class="p-6 mb-6 border-l-4 border-orange-500 shadow-sm bg-gray-50 rounded-xl">
                    <h4 class="pb-2 mb-4 text-lg font-semibold text-orange-600 border-b-2 border-blue-600">👨‍👩‍👦
                        بيانات ولي
                        الأمر</h4>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="block mb-2 text-sm font-medium">الاسم الثلاثي</label>
                            <input type="text" name="parent_name" id="parent_name" placeholder="الأب الجد العائلة"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <div class="error-message">يرجى إدخال اسم ولي الأمر ثلاثياً</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium">رقم الهوية</label>
                            <input type="text" name="parent_id" id="parent_id" maxlength="9" placeholder="9 أرقام"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <div class="error-message">يرجى إدخال رقم هوية صحيح (9 أرقام)</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium">المهنة</label>
                            <input type="text" name="parent_job" id="parent_job"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="مهنة ولي الأمر" required>
                            <div class="error-message">يرجى إدخال مهنة ولي الأمر</div>
                        </div>
                    </div>
                </div>

                <!-- 📞 بيانات الاتصال -->
                <div class="p-6 mb-6 bg-gray-100 border-t-4 border-blue-600 shadow-sm rounded-xl">
                    <h4 class="pb-2 mb-4 text-lg font-semibold text-blue-700 border-b-2 border-orange-500">📞 بيانات
                        الاتصال
                    </h4>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium">رقم جوال الأب</label>
                            <input type="tel" name="father_phone" id="father_phone" placeholder="05xxxxxxxx"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <div class="error-message">يرجى إدخال رقم جوال الأب</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium">رقم جوال الأم</label>
                            <input type="tel" name="mother_phone" id="mother_phone" placeholder="05xxxxxxxx"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="error-message">يرجى إدخال رقم جوال صحيح</div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium">عنوان السكن بالتفصيل</label>
                            <textarea name="address" id="address" rows="3" placeholder="اكتب العنوان بالتفصيل"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required></textarea>
                            <div class="error-message">يرجى إدخال عنوان السكن</div>
                        </div>
                    </div>
                </div>

                <!-- 💰 المعلومات المالية -->
                <div
                    class="p-6 mb-6 border-b-4 border-orange-500 shadow-sm bg-gradient-to-r from-gray-100 to-gray-200 rounded-xl">
                    <h4 class="pb-2 mb-4 text-lg font-semibold text-blue-700 border-b-2 border-orange-500">💰 المعلومات
                        المالية
                    </h4>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <label class="block mb-2 text-sm font-medium">قيمة القسط الشهري</label>
                            <input type="number" name="monthly_fee" id="monthly_fee"
                                class="w-full px-3 py-2 text-black border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-600"
                                placeholder="0.00" step="1.00" required>
                            <div class="error-message">يرجى إدخال قيمة الرسوم الشهرية</div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">عدد الدفعات</label>
                            <input type="number" name="num_payments" id="num_payments"
                                class="w-full px-3 py-2 text-black border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-600"
                                placeholder="10" min="1" max="120" required>
                            <p class="mt-1 text-xs text-gray-500">عدد الأقساط الشهرية المطلوبة</p>
                            <div class="error-message">يرجى إدخال عدد الدفعات</div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">تاريخ بدء الدراسة</label>
                            <input type="date" name="study_start_date" id="study_start_date"
                                class="w-full px-3 py-2 text-black transition-colors duration-200 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-600"
                                placeholder="YYYY-MM-DD" required>
                            <div class="error-message">يرجى اختيار تاريخ بدء الدراسة</div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between pt-6 mt-6 border-t border-gray-200">
                    <button type="button" onclick="closeAddAdmissionModal()"
                        class="px-6 py-3 text-gray-700 bg-gray-300 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        إلغاء
                    </button>
                    <button type="submit"
                        class="px-6 py-3 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        حفظ البيانات
                    </button>
                </div>

            </form>
        </div>


    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="fixed inset-0 hidden w-full h-full bg-gray-600/50 z-60">
    <div class="relative max-w-lg mx-auto mt-20 success-popup">
        <!-- Progress Bar -->
        <div class="progress-bar"></div>

        <div class="p-8 text-center text-white">
            <div class="flex justify-center mb-4">
                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
            </div>
            <h3 class="mb-4 text-2xl font-bold">تم حفظ البيانات بنجاح!</h3>
            <p class="mb-6 text-blue-100">يمكنك الآن إصدار البيانات كصورة أو ملف PDF</p>

            <div class="flex justify-center space-x-4 space-x-reverse">
                <button onclick="exportAsImage()"
                    class="px-6 py-3 font-medium text-blue-600 bg-white rounded-lg hover:bg-blue-50">
                    إصدار كصورة
                </button>
                <button onclick="exportAsPDF()"
                    class="px-6 py-3 font-medium text-white bg-blue-800 rounded-lg hover:bg-blue-900">
                    إصدار كـ PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div id="approve-modal" class="fixed inset-0 z-50 hidden w-full h-full overflow-y-auto bg-gray-600/50">
    <div class="relative p-5 mx-auto bg-white border rounded-md shadow-lg top-20 w-96">
        <div class="mt-3">
            <h3 class="mb-4 text-lg font-medium text-center text-gray-900">قبول طلب الانتساب</h3>
            <form id="approve-form" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">اختر المجموعة</label>
                    <select name="group_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary">
                        <option value="">اختر المجموعة</option>
                        @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->students_count ?? 0 }} طالب)
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">حالة الدفع</label>
                    <select name="payment_status" id="approve-payment-status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary">
                        <option value="paid" selected>تم الدفع (30 ₪)</option>
                        <option value="exempt">إعفاء</option>
                    </select>
                </div>
                <div class="flex items-center justify-between">
                    <button type="button" onclick="closeApproveModal()"
                        class="px-4 py-2 text-gray-700 bg-gray-300 rounded-md hover:bg-gray-400">
                        إلغاء
                    </button>
                    <button type="submit" class="px-4 py-2 text-white bg-green-500 rounded-md hover:bg-green-600">
                        قبول الطلب
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="fixed inset-0 z-50 hidden bg-gray-600/50">
    <div class="relative p-5 mx-auto mt-20 bg-white rounded shadow w-96">
        <h3 class="mb-4 text-lg font-medium text-center">رفض الطلب</h3>
        <form id="reject-form" method="POST">
            @csrf
            @method('PATCH')
            <div class="mb-4">
                <label class="block mb-2 text-sm font-medium text-gray-700">سبب الرفض (اختياري)</label>
                <textarea name="reason" rows="3" placeholder="اكتب سبب الرفض..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
            </div>
            <div class="flex justify-between">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 rounded">إلغاء</button>
                <button type="submit" class="px-4 py-2 text-white bg-red-500 rounded">رفض الطلب</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══ Credentials Modal ═══ -->
<div id="credentials-modal"
    class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/60 p-4"
    style="display:none">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-auto">

        <!-- Header -->
        <div class="flex items-center gap-3 px-6 py-4 bg-green-50 rounded-t-2xl border-b border-green-200">
            <div class="flex items-center justify-center w-10 h-10 bg-green-500 rounded-full text-white shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h3 class="font-bold text-gray-900">بيانات الدخول</h3>
                <p id="cred-student-name" class="text-sm text-gray-500 truncate"></p>
            </div>
            <button onclick="closeCredentialsModal()" class="mr-auto text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-5 space-y-4" id="credentials-print-area">

            <!-- ولي الأمر -->
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                <h4 class="flex items-center gap-2 text-sm font-bold text-blue-800 mb-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    بيانات دخول ولي الأمر
                </h4>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-gray-500 shrink-0">اسم المستخدم (رقم هوية الأب):</span>
                        <span id="cred-parent-id" class="font-mono font-bold text-blue-900 bg-blue-100 px-2 py-0.5 rounded select-all"></span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-gray-500 shrink-0">كلمة المرور (رقم الجوال):</span>
                        <span id="cred-father-phone" class="font-mono font-bold text-blue-900 bg-blue-100 px-2 py-0.5 rounded select-all"></span>
                    </div>
                </div>
            </div>

            <!-- الطالب -->
            <div class="bg-green-50 rounded-xl p-4 border border-green-200">
                <h4 class="flex items-center gap-2 text-sm font-bold text-green-800 mb-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                    بيانات دخول الطالب
                </h4>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-gray-500 shrink-0">اسم المستخدم (رقم هوية الطالب):</span>
                        <span id="cred-student-id" class="font-mono font-bold text-green-900 bg-green-100 px-2 py-0.5 rounded select-all"></span>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-gray-500 shrink-0">كلمة المرور (رقم الطلب):</span>
                        <span id="cred-app-number" class="font-mono font-bold text-green-900 bg-green-100 px-2 py-0.5 rounded select-all text-lg"></span>
                    </div>
                </div>
            </div>

            <p class="text-xs text-center text-gray-400">سلّم هذه البيانات للطالب وولي أمره • تستطيع طباعتها أو نسخها</p>
        </div>

        <!-- Actions -->
        <div class="flex gap-2 px-5 py-4 border-t border-gray-100">
            <button onclick="printCredentials()"
                class="flex items-center gap-2 flex-1 justify-center px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                طباعة
            </button>
            <button onclick="copyCredentials()"
                class="flex items-center gap-2 flex-1 justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                نسخ
            </button>
            <button onclick="closeCredentialsModal()"
                class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                إغلاق
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
    // نظام إدارة طلبات الانتساب - الكود الكامل
    document.addEventListener("DOMContentLoaded", function() {
        console.log('🎓 بدء تحميل نظام إدارة طلبات الانتساب...');

        // ==================== المتغيرات العامة ====================
        let savedAdmissionData = {};
        let applicationValidationTimeout;
        let isCheckingApplicationNumber = false;

        const form = document.getElementById("add-admission-form");
        const successModal = document.getElementById("success-modal");
        const statusFilter = document.getElementById('statusFilter');
        const groupFilter = document.getElementById('groupFilter');
        const nameSearch = document.getElementById('nameSearch');

        const studyStartDateInput = document.getElementById('study_start_date');





        // إخفاء رسائل الخطأ افتراضياً
        document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');

        // ==================== تحميل المجموعات ====================
        async function loadGroups() {
            const groupSelect = document.getElementById('groupFilter');
            if (!groupSelect) return;

            try {
                const response = await fetch("{{ route('admin.admissions.groups') }}", {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const groups = await response.json();
                    groupSelect.innerHTML = '<option value="">جميع المجموعات</option>';

                    groups.forEach(group => {
                        const option = document.createElement('option');
                        option.value = group.id;
                        option.textContent = group.name;
                        groupSelect.appendChild(option);
                    });

                    console.log(`تم تحميل ${groups.length} مجموعة من الخادم`);
                } else {
                    throw new Error('فشل في جلب المجموعات');
                }
            } catch (error) {
                console.warn('استخدام المجموعات الافتراضية:', error);

                const defaultGroups = [
                    { id: 1, name: 'مجموعة الصف الأول' },
                    { id: 2, name: 'مجموعة الصف الثاني' },
                    { id: 3, name: 'مجموعة الصف الثالث' },
                    { id: 4, name: 'مجموعة الصف الرابع' },
                    { id: 5, name: 'مجموعة الصف الخامس' },
                    { id: 6, name: 'مجموعة الصف السادس' }
                ];

                groupSelect.innerHTML = '<option value="">جميع المجموعات</option>';
                defaultGroups.forEach(group => {
                    const option = document.createElement('option');
                    option.value = group.id;
                    option.textContent = group.name;
                    groupSelect.appendChild(option);
                });
            }
        }

        // ==================== البحث والفلترة ====================
        async function searchByName() {
            if (!nameSearch) {
                showNotification('حقل البحث غير موجود', 'error');
                return;
            }

            const searchTerm = nameSearch.value.trim();

            if (searchTerm === '') {
                showNotification('يرجى إدخال اسم للبحث', 'warning');
                return;
            }

            if (searchTerm.length < 2) {
                showNotification('يرجى إدخال حرفين على الأقل للبحث', 'warning');
                return;
            }

            console.log('البحث بالاسم:', searchTerm);

            try {
                const response = await fetch(`/admin/admissions?search=${encodeURIComponent(searchTerm)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    let admissions;

                    if (data.admissions && data.admissions.data) {
                        admissions = data.admissions.data;
                    } else if (data.data) {
                        admissions = data.data;
                    } else if (Array.isArray(data)) {
                        admissions = data;
                    } else {
                        throw new Error('تنسيق البيانات غير متوقع');
                    }

                    handleSearchResults(admissions, searchTerm);
                } else {
                    throw new Error(`HTTP ${response.status}`);
                }
            } catch (error) {
                console.warn('فشل البحث في الخادم، استخدام البحث المحلي:', error);
                searchInTable(searchTerm);
            }
        }

        function handleSearchResults(admissions, searchTerm) {
            if (admissions.length > 0) {
                updateTableFromServerData(admissions, searchTerm);
                showNotification(`تم العثور على ${admissions.length} نتيجة للبحث عن "${searchTerm}"`, 'success');
                addShowAllButton();
            } else {
                showNotification(`لم يتم العثور على نتائج للبحث عن "${searchTerm}"`, 'info');
                updateTableFromServerData([], searchTerm);
                addShowAllButton();
            }
        }

        function updateTableFromServerData(admissions, searchTerm) {
            const tableBody = document.getElementById('admissionsTableBody');
            if (!tableBody) return;

            tableBody.innerHTML = '';

            if (admissions.length === 0) {
            const noResultsRow = document.createElement('tr');
            noResultsRow.className = 'temp-message';
            noResultsRow.innerHTML = `
            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                <div class="flex flex-col items-center">
                    <svg class="w-8 h-8 mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <p class="font-medium">لا توجد نتائج للبحث عن "${searchTerm}"</p>
                    <p class="mt-1 text-sm text-gray-400">جرب البحث بكلمة أخرى أو انقر "إظهار الكل"</p>
                </div>
            </td>
            `;
            tableBody.appendChild(noResultsRow);
            return;
            }

            admissions.forEach(admission => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50 border-b border-gray-200';
            row.setAttribute('data-status', admission.status || 'pending');

            if (admission.group_id) {
            row.setAttribute('data-group-id', admission.group_id);
            }
            row.style.backgroundColor = '#fff3cd';

            let groupDisplay = '';

            if (admission.group && admission.group.name) {
            groupDisplay = `<div class="text-sm text-gray-900">${admission.group.name}</div>`;
            } else if (admission.group_name) {
            groupDisplay = `<div class="text-sm text-gray-900">${admission.group_name}</div>`;
            } else {
            groupDisplay = `<div class="text-sm text-gray-500">غير مخصص لمجموعة</div>`;
            }

            row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${admission.student_name || 'غير محدد'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${admission.parent_name || 'غير محدد'}</div>
            </td>

            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${admission.group_name || 'غير محدد'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${admission.father_phone || admission.phone || 'غير محدد'}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 text-xs font-semibold rounded-full ${getStatusBadgeClass(admission.status)}">
                    ${getStatusText(admission.status)}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                ${admission.created_at ? new Date(admission.created_at).toLocaleDateString('ar-PS') : 'غير محدد'}
            </td>
            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                <div class="flex items-center space-x-2 space-x-reverse">
                    ${admission.status === 'pending' ? `
                    <button onclick="openApproveModal(${admission.id}, '${admission.student_name}')"
                        class="font-semibold mr-1 text-green-600 transition-colors duration-200 hover:text-green-800">
                        قبول
                    </button>
                    <button onclick="openRejectModal(${admission.id})"
                        class="font-semibold mr-1 text-red-600 transition-colors duration-200 hover:text-red-800">
                        رفض
                    </button>
                    ` : `
                    <span class="font-medium text-gray-400">تم المعالجة</span>
                    `}
                </div>
            </td>
            `;

            tableBody.appendChild(row);
            });
        }

        function searchInTable(searchTerm) {
            const tableBody = document.getElementById('admissionsTableBody');
            if (!tableBody) {
            showNotification('لم يتم العثور على جدول البيانات', 'warning');
            return;
            }

            const rows = tableBody.querySelectorAll('tr');
            let foundCount = 0;
            let totalRows = 0;

            rows.forEach(row => {
            if (row.cells && row.cells.length >= 7 && !row.classList.contains('temp-message')) { // تغيير من 3 إلى 7
            totalRows++;
            let matchFound = false;
            const searchTermLower = searchTerm.toLowerCase().trim();
            // البحث في عمود اسم الطالب (0) وولي الأمر (1) والمجموعة (2)
            const columnsToSearch = [0, 1, 2];

            for (const columnIndex of columnsToSearch) {
            if (row.cells[columnIndex]) {
            const cellText = row.cells[columnIndex].textContent.toLowerCase().trim();
            if (cellText.includes(searchTermLower)) {
            matchFound = true;
            break;
            }
            }
            }

            if (matchFound) {
            row.style.display = '';
            row.style.backgroundColor = '#fff3cd';
            foundCount++;
            } else {
            row.style.display = 'none';
            }
            }
            });

            if (foundCount > 0) {
            showNotification(`تم العثور على ${foundCount} نتيجة محلية من ${totalRows} سجل`, 'success');
            addShowAllButton();
            } else {
            showNotification(`لم يتم العثور على نتائج محلية للبحث عن "${searchTerm}"`, 'info');
            addShowAllButton();
            }
        }

        async function filterAdmissions() {
            const statusFilterValue = statusFilter?.value;
            const groupFilterValue = groupFilter?.value;

            console.log('تطبيق الفلترة:', { status: statusFilterValue, group: groupFilterValue });

            try {
                const params = new URLSearchParams();
                if (statusFilterValue) params.append('status', statusFilterValue);
                if (groupFilterValue) params.append('group_id', groupFilterValue);

                const url = `/admin/admissions${params.toString() ? '?' + params.toString() : ''}`;

                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    let admissions;

                    if (data.admissions && data.admissions.data) {
                        admissions = data.admissions.data;
                    } else if (data.data) {
                        admissions = data.data;
                    } else if (Array.isArray(data)) {
                        admissions = data;
                    } else {
                        throw new Error('تنسيق البيانات غير متوقع');
                    }

                    updateTableFromServerData(admissions, 'فلتر');
                    showNotification(`تم تطبيق الفلتر: ${admissions.length} نتيجة`, 'info', 2000);
                } else {
                    throw new Error(`HTTP ${response.status}`);
                }
            } catch (error) {
                console.warn('فشل الفلترة في الخادم، استخدام الفلترة المحلية:', error);
                filterLocalTable(statusFilterValue, groupFilterValue);
            }
        }

        // أضف هذا الكود داخل DOMContentLoaded في نهاية التهيئة:

        // معالجة نموذج الموافقة
        const approveForm = document.getElementById('approve-form');
        if (approveForm) {
        approveForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const groupId = formData.get('group_id');
        const paymentStatus = formData.get('payment_status') || 'paid';

        if (!groupId) {
        showNotification('يرجى اختيار المجموعة', 'warning');
        return;
        }

        try {
        const response = await fetch(this.action, {
        method: 'PATCH',
        headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
        group_id: groupId,
        payment_status: paymentStatus
        })
        });

        const result = await response.json();

        if (result.success) {
        closeApproveModal();
        if (result.credentials) {
            showCredentialsModal(result.credentials);
        } else {
            showNotification(result.message, 'success');
            setTimeout(() => location.reload(), 1500);
        }
        } else {
        showNotification(result.message || 'حدث خطأ في الموافقة', 'error');
        }
        } catch (error) {
        console.error('خطأ في الموافقة:', error);
        showNotification('حدث خطأ أثناء معالجة الطلب', 'error');
        }
        });
        }

        // معالجة نموذج الرفض
        const rejectForm = document.getElementById('reject-form');
        if (rejectForm) {
        rejectForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!confirm('هل أنت متأكد من رفض هذا الطلب؟')) {
        return;
        }

        const formData = new FormData(this);

        try {
        const response = await fetch(this.action, {
        method: 'PATCH',
        headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
        reason: formData.get('reason') || null
        })
        });

        const result = await response.json();

        if (result.success) {
        showNotification(result.message, 'success');
        closeRejectModal();
        setTimeout(() => location.reload(), 1500);
        } else {
        showNotification(result.message || 'حدث خطأ في الرفض', 'error');
        }
        } catch (error) {
        console.error('خطأ في الرفض:', error);
        showNotification('حدث خطأ أثناء معالجة الطلب', 'error');
        }
        });
        }

        function filterLocalTable(status, groupId) {
            const tableBody = document.getElementById('admissionsTableBody');
            if (!tableBody) return;

            const rows = tableBody.querySelectorAll('tr');
            let visibleCount = 0;

            rows.forEach(row => {
                if (row.cells && row.cells.length >= 3 && !row.classList.contains('temp-message')) {
                    let shouldShow = true;

                    if (status && status !== '') {
                        const rowStatus = row.getAttribute('data-status');
                        if (rowStatus !== status) {
                            shouldShow = false;
                        }
                    }

                    if (groupId && groupId !== '' && shouldShow) {
                        const rowGroupId = row.getAttribute('data-group-id');
                        if (rowGroupId !== groupId) {
                            shouldShow = false;
                        }
                    }

                    if (shouldShow) {
                        row.style.display = '';
                        row.style.backgroundColor = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                }
            });

            const message = (status || groupId) ? `تم تطبيق الفلتر المحلي: ${visibleCount} نتيجة` : `تم إظهار جميع النتائج: ${visibleCount} سجل`;
            showNotification(message, 'info', 2000);
        }

        function filterTable() {
            const filterValue = statusFilter?.value;
            const tableBody = document.getElementById('admissionsTableBody');
            if (!tableBody) return;

            const dataRows = tableBody.querySelectorAll('tr[data-status]');
            const emptyRow = document.getElementById('emptyRow');
            let visibleRowsCount = 0;

            if (emptyRow && filterValue !== '') {
                emptyRow.style.display = 'none';
            } else if (emptyRow && filterValue === '') {
                emptyRow.style.display = dataRows.length === 0 ? '' : 'none';
            }

            dataRows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                if (filterValue === '' || rowStatus === filterValue) {
                    row.style.display = '';
                    visibleRowsCount++;
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function addShowAllButton() {
            let existingButton = document.getElementById('show-all-results');
            if (existingButton) {
                existingButton.remove();
            }

            const button = document.createElement('button');
            button.id = 'show-all-results';
            button.className = 'px-3 py-2 text-sm bg-gray-100 border border-gray-300 rounded-l-md hover:bg-gray-200 transition-colors';
            button.textContent = 'إظهار الكل';
            button.onclick = showAllResults;

            const searchButton = document.querySelector('button[onclick="searchByName()"]');
            if (searchButton && searchButton.parentElement) {
                searchButton.parentElement.appendChild(button);
            }
        }

        function showAllResults() {
            const url = new URL(window.location);
            url.searchParams.delete('search');
            url.searchParams.delete('status');
            url.searchParams.delete('group_id');
            window.location.href = url.toString();
        }

        // ==================== إدارة النوافذ المنبثقة ====================
        function openAddAdmissionModal() {
            const modal = document.getElementById('add-admission-modal');
            if (modal) {
                modal.classList.remove('hidden');
                fetchNextApplicationNumber();
            }
        }

        function closeAddAdmissionModal() {
            const modal = document.getElementById('add-admission-modal');
            if (modal) {
                modal.classList.add('hidden');
                clearFormErrors();
                resetForm();
            }
        }

        function openApproveModal(admissionId, studentName) {
            const modal = document.getElementById('approve-modal');
            const form = document.getElementById('approve-form');
            if (modal && form) {
                modal.classList.remove('hidden');
                form.action = `/admin/admissions/${admissionId}/approve`;
            }
        }

        function closeApproveModal() {
            const modal = document.getElementById('approve-modal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function openRejectModal(admissionId) {
            const modal = document.getElementById('reject-modal');
            const form = document.getElementById('reject-form');
            if (modal && form) {
            modal.classList.remove('hidden');
            form.action = `/admin/admissions/${admissionId}/reject`;
            }
        }

        function closeRejectModal() {
            const modal = document.getElementById('reject-modal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function submitRejectForm() {
            if (window.currentRejectForm) {
                window.currentRejectForm.submit();
            }
        }

        // ==================== إدارة النماذج ====================
        function resetForm() {
            if (form) {
                form.reset();
            }
            clearAllErrors();
            clearApplicationNumberValidation();
        }

        function clearAllErrors() {
            document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.field-error, .pending-error').forEach(field => {
                field.classList.remove('field-error', 'pending-error');
                field.removeAttribute('data-error-message');
            });

            const appNumberField = document.getElementById('application_number');
            if (appNumberField) {
                appNumberField.classList.remove('valid', 'invalid');
            }
        }

        function clearApplicationNumberValidation() {
            const appNumberField = document.getElementById('application_number');
            if (appNumberField) {
                appNumberField.value = '';
                appNumberField.placeholder = 'جاري التحميل...';
            }
            document.getElementById('app-number-warning')?.classList.add('hidden');
        }

        // ==================== جلب رقم الطلب التالي تلقائياً ====================
        async function fetchNextApplicationNumber() {
            const field = document.getElementById('application_number');
            const loading = document.getElementById('app-number-loading');
            const warningDiv = document.getElementById('app-number-warning');
            const remainingSpan = document.getElementById('app-number-remaining');

            if (!field) return;

            field.value = '';
            field.placeholder = 'جاري التحميل...';
            loading?.classList.remove('hidden');
            warningDiv?.classList.add('hidden');

            try {
                const response = await fetch("{{ route('admin.admissions.next-number') }}", {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    field.value = data.next_number;
                    field.placeholder = '0000';

                    if (data.warning) {
                        if (remainingSpan) remainingSpan.textContent = data.remaining;
                        warningDiv?.classList.remove('hidden');
                    }
                } else {
                    field.placeholder = 'خطأ في التحميل';
                    showNotification(data.message || 'حدث خطأ في توليد رقم الطلب', 'error');
                }
            } catch (error) {
                field.placeholder = 'خطأ في الاتصال';
                console.error('خطأ في جلب رقم الطلب:', error);
            } finally {
                loading?.classList.add('hidden');
            }
        }

        // (دوال التحقق اليدوي أُزيلت - رقم الطلب يُولَّد تلقائياً)

        async function validateApplicationNumber(number) {
            // لم تعد مستخدمة - محتفظ بها للتوافق فقط
            if (isCheckingApplicationNumber) return;

            const appNumberField = document.getElementById('application_number');
            if (!appNumberField) return;

            isCheckingApplicationNumber = true;

            try {
                const isAvailable = await checkApplicationNumberInDatabase(number);
                if (isAvailable) {
                    showApplicationNumberSuccess();
                    appNumberField.classList.remove('invalid');
                    appNumberField.classList.add('valid');
                    console.log('✅ رقم الطلب متاح');
                } else {
                    showApplicationNumberError('رقم الطلب مستخدم مسبقاً، يرجى اختيار رقم آخر');
                    appNumberField.classList.remove('valid');
                    appNumberField.classList.add('invalid');
                    console.log('❌ رقم الطلب غير متاح');
                }
            } catch (error) {
                showApplicationNumberError('خطأ في التحقق من رقم الطلب، يرجى المحاولة مرة أخرى');
                appNumberField.classList.remove('valid');
                appNumberField.classList.add('invalid');
                console.log('❌ خطأ في التحقق من رقم الطلب:', error);
            }

            isCheckingApplicationNumber = false;
        }

        async function checkApplicationNumberInDatabase(number) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch("/admin/admissions/check-application-number", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ application_number: number })
                });

                if (!response.ok) {
                    throw new Error('فشل في الاتصال بالخادم');
                }

                const data = await response.json();
                return data.available;
            } catch (error) {
                console.error('خطأ في التحقق من رقم الطلب:', error);
                return true;
            }
        }

        function hideApplicationNumberMessages() {
            document.getElementById('app-number-warning')?.classList.add('hidden');
        }

        // ==================== معالجة إرسال النموذج ====================
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'جاري الحفظ...';
                submitBtn.disabled = true;

                try {
                    const formData = new FormData(form);
                    savedAdmissionData = {};
                    for (let [key, value] of formData.entries()) {
                        savedAdmissionData[key] = value;
                    }

                    const response = await fetch(form.action || '/admin/admissions', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        clearFormErrors();
                        // تحديث رقم الطلب الفعلي من استجابة الخادم
                        if (result.admission?.application_number) {
                            savedAdmissionData.application_number = result.admission.application_number;
                        }
                        showNotification('تم حفظ البيانات بنجاح!', 'success');
                        closeAddAdmissionModal();
                        showSuccessModal();
                        setTimeout(() => location.reload(), 2000);
                    } else if (response.status === 422) {
                        const errors = result.errors || {};
                        showFormErrors(errors);
                    } else {
                        showNotification(result.message || 'حدث خطأ أثناء حفظ البيانات', 'error');
                    }
                } catch (error) {
                    console.error('خطأ في إرسال النموذج:', error);
                    showNotification('حدث خطأ في الاتصال بالخادم، يرجى المحاولة مرة أخرى', 'error');
                } finally {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            });
        }

        function clearFormErrors() {
            form.querySelectorAll('.field-inline-error').forEach(el => el.remove());
            form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
            const banner = form.querySelector('#form-error-banner');
            if (banner) banner.remove();
        }

        function showFormErrors(errors) {
            clearFormErrors();

            // ملخص الأخطاء في أعلى النموذج
            const messages = Object.values(errors).flat();
            if (messages.length) {
                const banner = document.createElement('div');
                banner.id = 'form-error-banner';
                banner.className = 'bg-red-50 border border-red-400 rounded-lg p-4 mb-4';
                banner.innerHTML = `
                    <p class="font-semibold text-red-700 mb-2">يوجد ${messages.length} خطأ في البيانات، يرجى التصحيح:</p>
                    <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                        ${messages.map(m => `<li>${m}</li>`).join('')}
                    </ul>`;
                form.prepend(banner);
                banner.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            // تلوين الحقول الخاطئة
            Object.keys(errors).forEach(field => {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('border-red-500');
                    const msg = document.createElement('p');
                    msg.className = 'field-inline-error text-xs text-red-600 mt-1';
                    msg.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                    input.parentNode.appendChild(msg);
                }
            });

            showNotification(messages[0] || 'يرجى مراجعة البيانات المدخلة', 'error', 8000);
        }

        function showSuccessModal() {
            if (successModal) {
                successModal.classList.remove('hidden');
                setTimeout(() => {
                    successModal.classList.add('hidden');
                }, 5000);
            }
        }

        // ==================== وظائف التصدير ====================
        window.exportAsImage = async function() {
            try {
                showNotification('جاري إنشاء الصورة...', 'info', 2000);
                const dataElement = createDataDisplay();
                document.body.appendChild(dataElement);

                if (typeof html2canvas !== 'undefined') {
                    const canvas = await html2canvas(dataElement, {
                        allowTaint: true,
                        useCORS: true,
                        backgroundColor: '#ffffff',
                        scale: 2
                    });

                    const link = document.createElement('a');
                    const fileName = `طلب_انتساب_${savedAdmissionData.student_name || 'جديد'}_${Date.now()}.png`;
                    link.download = fileName;
                    link.href = canvas.toDataURL('image/png');
                    link.click();

                    document.body.removeChild(dataElement);
                    showNotification('تم تصدير الصورة بنجاح!', 'success');
                    setTimeout(() => {
                        if (successModal) successModal.classList.add('hidden');
                    }, 1000);
                } else {
                    document.body.removeChild(dataElement);
                    showNotification('مكتبة html2canvas غير متوفرة', 'error');
                }
            } catch (error) {
                console.error('خطأ في تصدير الصورة:', error);
                showNotification('خطأ في تصدير الصورة', 'error');
            }
        };

        window.exportAsPDF = async function() {
            try {
                showNotification('جاري إنشاء ملف PDF...', 'info', 3000);

                if (typeof html2canvas === 'undefined') {
                    showNotification('مكتبة html2canvas غير متوفرة', 'error');
                    return;
                }

                const JsPDFClass = window.jspdf?.jsPDF;
                if (!JsPDFClass) {
                    showNotification('مكتبة jsPDF غير متوفرة', 'error');
                    return;
                }

                const dataElement = createDataDisplay();
                document.body.appendChild(dataElement);

                const canvas = await html2canvas(dataElement, {
                    allowTaint: true,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    scale: 2,
                    logging: false,
                });

                document.body.removeChild(dataElement);
                const imgData   = canvas.toDataURL('image/png');

                // A4 dimensions in mm
                const pageW  = 210;
                const pageH  = 297;
                const imgH   = (canvas.height * pageW) / canvas.width;

                const doc = new JsPDFClass({ orientation: 'portrait', unit: 'mm', format: 'a4' });

                let remaining = imgH;
                let offset    = 0;

                doc.addImage(imgData, 'PNG', 0, offset, pageW, imgH);
                remaining -= pageH;

                while (remaining > 0) {
                    offset -= pageH;
                    doc.addPage();
                    doc.addImage(imgData, 'PNG', 0, offset, pageW, imgH);
                    remaining -= pageH;
                }

                const fileName = `طلب_انتساب_${savedAdmissionData.student_name || 'جديد'}_${Date.now()}.pdf`;
                doc.save(fileName);

                showNotification('تم تصدير ملف PDF بنجاح!', 'success');
                setTimeout(() => {
                    if (successModal) successModal.classList.add('hidden');
                }, 1000);
            } catch (error) {
                console.error('خطأ في تصدير PDF:', error);
                showNotification('خطأ في تصدير ملف PDF', 'error');
            }
        };

        function createDataDisplay() {
            const div = document.createElement('div');
            div.style.cssText = `
                position: absolute;
                top: -9999px;
                background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
                padding: 40px;
                width: 900px;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                direction: rtl;
                text-align: right;
                border: 3px solid #EE8100;
                border-radius: 20px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            `;

            div.innerHTML = `
                <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #EE8100;">
                    <h1 style="color: #2778E5; font-size: 32px; margin: 0; font-weight: bold;">
                        🎓 نموذج انتساب جديد
                    </h1>
                    <p style="color: #666; margin: 10px 0 0 0; font-size: 14px;">
                        تاريخ الإصدار: ${new Date().toLocaleDateString('ar-PS')}
                    </p>
                </div>

                <div style="background: linear-gradient(135deg, #2778E5 0%, #1e40af 100%);
                            color: white; padding: 20px; margin-bottom: 20px; border-radius: 15px;">
                    <h3 style="margin: 0 0 15px 0; font-size: 20px; border-bottom: 2px solid #EE8100;
                            padding-bottom: 8px;">📋 بيانات الطلب</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px;">
                        <p style="margin: 0; text-align: right;"><strong>اليوم:</strong> ${savedAdmissionData.day || 'غير محدد'}</p>
                        <p style="margin: 0; text-align: center;"><strong>تاريخ التقديم:</strong> ${savedAdmissionData.application_date || 'غير محدد'}</p>
                        <p style="margin: 0; text-align: left;"><strong>رقم الطلب:</strong> ${savedAdmissionData.application_number || 'يتم توليده تلقائياً'}</p>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 2px solid #e5e7eb; padding: 20px;
                            margin-bottom: 20px; border-radius: 15px; border-right: 5px solid #2778E5;">
                    <h3 style="color: #2778E5; margin: 0 0 15px 0; font-size: 18px;
                            border-bottom: 2px solid #EE8100; padding-bottom: 8px;">👨‍🎓 بيانات الطالب</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>الاسم:</strong> ${savedAdmissionData.student_name || 'غير محدد'}</p>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>رقم الهوية:</strong> ${savedAdmissionData.student_id || 'غير محدد'}</p>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>تاريخ الميلاد:</strong> ${savedAdmissionData.birth_date || 'غير محدد'}</p>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>المرحلة الدراسية:</strong> ${savedAdmissionData.grade || 'غير محدد'}</p>
                    </div>
                    <p style="margin: 10px 0 0 0; color: #374151;"><strong>المستوى الأكاديمي:</strong>
                    <span style="background: #EE8100; color: white; padding: 8px 12px; border-radius: 8px; font-size: 14px;">
                        ${savedAdmissionData.academic_level || 'غير محدد'}
                    </span>
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div style="background: #f8fafc; border: 2px solid #e5e7eb; padding: 20px;
                                border-radius: 15px; border-right: 5px solid #EE8100;">
                        <h3 style="color: #EE8100; margin: 0 0 15px 0; font-size: 18px;
                                border-bottom: 2px solid #2778E5; padding-bottom: 8px;">👨‍💼 بيانات ولي الأمر</h3>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>الاسم:</strong> ${savedAdmissionData.parent_name || 'غير محدد'}</p>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>رقم الهوية:</strong> ${savedAdmissionData.parent_id || 'غير محدد'}</p>
                        <p style="margin: 0; color: #374151;"><strong>المهنة:</strong> ${savedAdmissionData.parent_job || 'غير محدد'}</p>
                    </div>

                    <div style="background: #f8fafc; border: 2px solid #e5e7eb; padding: 20px;
                                border-radius: 15px; border-right: 5px solid #2778E5;">
                        <h3 style="color: #2778E5; margin: 0 0 15px 0; font-size: 18px;
                                border-bottom: 2px solid #EE8100; padding-bottom: 8px;">📞 بيانات التواصل</h3>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>جوال الأب:</strong> ${savedAdmissionData.father_phone || 'غير محدد'}</p>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>جوال الأم:</strong> ${savedAdmissionData.mother_phone || 'غير متوفر'}</p>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>🏠 عنوان السكن:</strong> ${savedAdmissionData.address || 'غير محدد'}</p>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 2px solid #e5e7eb; padding: 20px;
                            border-radius: 15px; border: 3px solid #2778E5;">
                    <h3 style="color: #2778E5; margin: 0 0 15px 0; font-size: 18px;
                            border-bottom: 2px solid #EE8100; padding-bottom: 8px;">💰 المعلومات المالية</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <p style="margin: 0; color: #374151;"><strong>القسط الشهري:</strong></p>
                        <p style="margin: 0; color: #374151;"><strong>عدد الدفعات:</strong></p>
                        <p style="margin: 0; color: #374151;"><strong>تاريخ بدء الدراسة:</strong></p>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 5px;">
                        <p style="margin: 0; color: #374151;">
                        <span style="color: #EE8100; font-weight: bold;">${savedAdmissionData.monthly_fee || '0'} شيكل</span>
                        </p>
                        <p style="margin: 0; color: #374151;">${savedAdmissionData.num_payments || 'غير محدد'} دفعة</p>
                        <p style="margin: 0; color: #374151;">${savedAdmissionData.study_start_date || 'غير محدد'}</p>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 30px; padding-top: 20px;
                            border-top: 2px solid #EE8100; color: #666; font-size: 12px;">
                    <p style="margin: 0;">تم إنشاء هذا المستند تلقائياً من نظام إدارة طلبات الانتساب</p>
                </div>
            `;

            return div;
        }

        // ==================== الدوال المساعدة ====================
        function getStatusBadgeClass(status) {
            const classes = {
                'pending': 'bg-yellow-100 text-yellow-800',
                'approved': 'bg-green-100 text-green-800',
                'rejected': 'bg-red-100 text-red-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        }

        function getStatusText(status) {
            const statusText = {
                'pending': 'في الانتظار',
                'approved': 'مقبول',
                'rejected': 'مرفوض'
            };
            return statusText[status] || 'غير محدد';
        }

        function showNotification(message, type = 'info', duration = 5000) {
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notif => notif.remove());

            const notification = document.createElement('div');
            notification.className = `notification fixed top-4 right-4 z-[9999] p-4 rounded-lg shadow-lg text-white max-w-sm transition-all duration-300 ${getNotificationClass(type)}`;
            notification.style.transform = 'translateX(100%)';
            notification.style.opacity = '0';

            notification.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-1">${message}</div>
                    <button onclick="this.parentElement.parentElement.remove()"
                        class="ml-2 text-white transition-colors hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
                notification.style.opacity = '1';
            }, 100);

            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.transform = 'translateX(100%)';
                    notification.style.opacity = '0';
                    setTimeout(() => notification.remove(), 300);
                }
            }, duration);
        }

        function getNotificationClass(type) {
            const classes = {
                'success': 'bg-green-500',
                'error': 'bg-red-500',
                'warning': 'bg-yellow-500',
                'info': 'bg-blue-500'
            };
            return classes[type] || classes['info'];
        }

        function viewAdmission(id) {
            console.log('عرض الطلب:', id);
            showNotification(`عرض طلب الانتساب رقم ${id}`, 'info');
        }

        function editAdmission(id) {
            console.log('تعديل الطلب:', id);
            showNotification(`تعديل طلب الانتساب رقم ${id}`, 'info');
        }

        function deleteAdmission(id) {
            if (confirm('هل أنت متأكد من حذف هذا الطلب؟')) {
                console.log('حذف الطلب:', id);
                showNotification(`تم حذف طلب الانتساب رقم ${id}`, 'success');
            }
        }


        const dateFields = [
            'application_date',
            'birth_date',
            'study_start_date'
        ];

        dateFields.forEach(id => {
            const el = document.getElementById(id);
            if (el && typeof flatpickr !== 'undefined') {
                flatpickr(el, {
                    dateFormat: "Y-m-d",
                    locale: "ar",
                    altInput: true,
                    altFormat: "d F Y",
                    disableMobile: true,
                    theme: "light"
                });
            }
        });

        // ==================== ربط الأحداث ====================
        if (statusFilter) {
            statusFilter.addEventListener('change', filterTable);
            statusFilter.addEventListener('change', filterAdmissions);
        }

        if (groupFilter) {
            groupFilter.addEventListener('change', filterAdmissions);
        }

        if (nameSearch) {
            nameSearch.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    searchByName();
                }
            });

            let searchTimeout;
            nameSearch.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const value = this.value.trim();

                if (value.length >= 2) {
                    searchTimeout = setTimeout(() => {
                        searchByName();
                    }, 1000);
                } else if (value.length === 0) {
                    showAllResults();
                }
            });
        }


        const studentIdField = document.getElementById('student_id');
        if (studentIdField) {
            studentIdField.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 9) {
                    this.value = this.value.substring(0, 9);
                }
            });
        }

        const parentIdField = document.getElementById('parent_id');
        if (parentIdField) {
            parentIdField.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 9) {
                    this.value = this.value.substring(0, 9);
                }
            });
        }

        const fatherPhoneField = document.getElementById('father_phone');
        if (fatherPhoneField) {
            fatherPhoneField.addEventListener('input', function(e) {
                let value = this.value.replace(/[^0-9]/g, '');
                if (value.length > 10) {
                    value = value.substring(0, 10);
                }
                this.value = value;
            });
        }

        const motherPhoneField = document.getElementById('mother_phone');
        if (motherPhoneField) {
            motherPhoneField.addEventListener('input', function(e) {
                let value = this.value.replace(/[^0-9]/g, '');
                if (value.length > 10) {
                    value = value.substring(0, 10);
                }
                this.value = value;
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                if (nameSearch) {
                    nameSearch.focus();
                }
            }

            if (e.key === 'Escape') {
                showAllResults();
            }
        });

        // ==================== التهيئة النهائية ====================
        loadGroups();

        // تصدير الدوال للنطاق العام
        window.searchByName = searchByName;
        window.filterAdmissions = filterAdmissions;
        window.openAddAdmissionModal = openAddAdmissionModal;
        window.closeAddAdmissionModal = closeAddAdmissionModal;
        window.openApproveModal = openApproveModal;
        window.closeApproveModal = closeApproveModal;
        window.openRejectModal = openRejectModal;
        window.closeRejectModal = closeRejectModal;
        window.submitRejectForm = submitRejectForm;
        window.viewAdmission = viewAdmission;
        window.editAdmission = editAdmission;
        window.deleteAdmission = deleteAdmission;
        window.showAllResults = showAllResults;

        console.log('%c🎓 نظام إدارة طلبات الانتساب ', 'background: #2778E5; color: #EE8100; font-size: 16px; padding: 8px; border-radius: 4px;');
        console.log('✅ تم تحميل النظام بنجاح! جميع الوظائف متاحة.');
    });

    // ═══════════════════════════════════════════
    //  Credentials Modal — بيانات الدخول
    // ═══════════════════════════════════════════

    function showCredentialsModal(cred) {
        document.getElementById('cred-student-name').textContent =
            cred.student_name + ' — ' + (cred.parent_name ? 'ولي الأمر: ' + cred.parent_name : '');
        document.getElementById('cred-parent-id').textContent    = cred.parent_national_id  || '—';
        document.getElementById('cred-father-phone').textContent = cred.father_phone        || '—';
        document.getElementById('cred-student-id').textContent   = cred.student_national_id || '—';
        document.getElementById('cred-app-number').textContent   = cred.application_number  || '—';

        const modal = document.getElementById('credentials-modal');
        modal.style.display = 'flex';

        // إعادة تحميل الصفحة عند إغلاق الـ modal
        modal._reloadOnClose = true;
    }

    function closeCredentialsModal() {
        const modal = document.getElementById('credentials-modal');
        modal.style.display = 'none';
        if (modal._reloadOnClose) {
            modal._reloadOnClose = false;
            location.reload();
        }
    }

    // فتح الـ modal لطلب مقبول موجود مسبقاً
    window.showAdmissionCredentials = async function(admissionId) {
        try {
            const res = await fetch(`/admin/admissions/${admissionId}/credentials`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById('credentials-modal')._reloadOnClose = false;
                showCredentialsModal(data.credentials);
            } else {
                alert(data.message || 'تعذّر جلب البيانات');
            }
        } catch (e) {
            alert('حدث خطأ في الاتصال');
        }
    };

    window.showCredentialsModal  = showCredentialsModal;
    window.closeCredentialsModal = closeCredentialsModal;

    // طباعة بيانات الدخول
    window.printCredentials = function() {
        const area = document.getElementById('credentials-print-area').innerHTML;
        const win  = window.open('', '_blank', 'width=480,height=600');
        win.document.write(`
            <!DOCTYPE html><html lang="ar" dir="rtl">
            <head>
                <meta charset="UTF-8">
                <title>بيانات الدخول</title>
                <style>
                    body { font-family: Tahoma, Arial, sans-serif; padding: 24px; direction: rtl; }
                    h4  { margin: 0 0 8px; font-size: 14px; }
                    .bg-blue-50  { background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:12px; margin-bottom:12px; }
                    .bg-green-50 { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:12px; margin-bottom:12px; }
                    span.font-mono { font-family: monospace; font-weight:bold; font-size:15px; background:#e0e7ff; padding:2px 6px; border-radius:4px; }
                    .flex { display:flex; justify-content:space-between; margin-bottom:6px; align-items:center; }
                    .text-gray-500 { color:#6b7280; font-size:13px; }
                    p.text-xs { font-size:11px; color:#9ca3af; text-align:center; margin-top:12px; }
                    @media print { body { padding: 8px; } }
                </style>
            </head>
            <body>${area}</body>
            </html>
        `);
        win.document.close();
        win.focus();
        setTimeout(() => { win.print(); win.close(); }, 300);
    };

    // نسخ البيانات للحافظة
    window.copyCredentials = function() {
        const lines = [
            '═══ بيانات دخول ولي الأمر ═══',
            'اسم المستخدم (رقم هوية الأب): ' + document.getElementById('cred-parent-id').textContent,
            'كلمة المرور (رقم الجوال):     ' + document.getElementById('cred-father-phone').textContent,
            '',
            '═══ بيانات دخول الطالب ═══',
            'اسم المستخدم (رقم هوية الطالب): ' + document.getElementById('cred-student-id').textContent,
            'كلمة المرور (رقم الطلب):         ' + document.getElementById('cred-app-number').textContent,
        ].join('\n');

        navigator.clipboard.writeText(lines).then(() => {
            const btn = event.currentTarget;
            const orig = btn.innerHTML;
            btn.textContent = '✅ تم النسخ';
            btn.disabled = true;
            setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; }, 2000);
        }).catch(() => {
            prompt('انسخ البيانات يدوياً:', lines);
        });
    };

    // إغلاق بالنقر خارج الـ modal
    document.getElementById('credentials-modal').addEventListener('click', function(e) {
        if (e.target === this) closeCredentialsModal();
    });
</script>

@endpush
