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
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: left 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-left: 2.5rem;
    }

    #application_number.valid {
        border-color: #10b981;
        background-color: #f0fdf4;
    }

    #application_number.invalid {
        border-color: #ef4444;
        background-color: #fef2f2;
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
                    class="px-4 py-2 text-sm font-medium text-white transition-colors rounded-md bg-primary hover:bg-blue-700">
                    إضافة طلب جديد
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                        اسم الطالب
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                        ولي الأمر
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                        المجموعة
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                        رقم الهاتف
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                        الحالة
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                        تاريخ التقديم
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                        الإجراءات
                    </th>
                </tr>
            </thead>
            <tbody id="admissionsTableBody" class="bg-white divide-y divide-gray-200">
                @forelse($admissions as $admission)
                <tr data-status="{{ $admission->status }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $admission->student_name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $admission->parent_name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($admission->group)
                        <div class="text-sm text-gray-900">{{ $admission->group->name }}</div>
                        @else
                        <div class="text-sm text-gray-500">غير مخصص لمجموعة</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $admission->father_phone }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                        $statusClasses = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'approved' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800'
                        ];
                        @endphp
                        <span
                            class="inline-flex px-2 text-xs font-semibold rounded-full {{ $statusClasses[$admission->status] }}">
                            {{ $admission->status_in_arabic }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                        {{ $admission->created_at->format('Y-m-d') }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                        @if($admission->status === 'pending')
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <!-- Approve Button -->
                            <button onclick="openApproveModal({{ $admission->id }}, '{{ $admission->student_name }}')"
                                class="font-semibold text-green-600 transition-colors duration-200 hover:text-green-800">
                                قبول
                            </button>
                            <!-- Reject Button -->
                            <button onclick="openRejectModal({{ $admission->id }})"
                                class="font-semibold text-red-600 transition-colors duration-200 hover:text-red-800">
                                رفض
                            </button>
                        </div>
                        @else
                        <span class="font-medium text-gray-400">تم المعالجة</span>
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
<div id="add-admission-modal" class="fixed inset-0 z-50 hidden w-full h-full overflow-y-auto bg-gray-600 bg-opacity-50">
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
                                class="w-full px-8 py-2 text-black border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
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
                                class="w-full px-3 py-2 text-black border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                placeholder="YYYY-MM-DD" required>
                            <div class="error-message">يرجى اختيار تاريخ تقديم الطلب</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium">رقم الطلب</label>
                            <input type="text" name="application_number" id="application_number"
                                class="w-full px-3 py-2 text-black border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                placeholder="0000" required>
                            <div id="success-message-app-number" class="mt-1 text-sm text-green-600"
                                style="display: none;">
                                ✓ رقم الطلب متاح ويمكن استخدامه
                            </div>
                            <div id="checking-message-app-number" class="mt-1 text-sm text-yellow-600"
                                style="display: none;">
                                🔄 جاري التحقق من توفر الرقم...
                            </div>
                            <div class="error-message">يرجى إدخال رقم الطلب</div>
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
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium">قيمة القسط الشهري</label>
                            <input type="number" name="monthly_fee" id="monthly_fee"
                                class="w-full px-3 py-2 text-black border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-600"
                                placeholder="0.00" step="1.00" required>
                            <div class="error-message">يرجى إدخال قيمة الرسوم الشهرية</div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">تاريخ بدء الدراسة</label>
                            <input type="date" name="study_start_date" id="study_start_date"
                                class="w-full px-3 py-2 text-black transition-colors duration-200 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-600"
                                placeholder="YYYY-MM-DD" required>
                            <div class="error-message">يرجى اختيار تاريخ بدء الدراسة</div>
                        </div>
                    </div>
                    <div class="mt-2 md:col-span-2">
                        <label class="block mb-2 text-sm font-medium">فترة استحقاق الدفعة الشهرية</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1 text-xs text-gray-500">من تاريخ</label>
                                <input type="date" name="payment_due_from" id="payment_due_from"
                                    class="w-full px-3 py-2 text-black border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-600 transition-all duration-200"
                                    required>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs text-gray-500">إلى تاريخ</label>
                                <input type="date" name="payment_due_to" id="payment_due_to"
                                    class="w-full px-3 py-2 text-black border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-600 transition-all duration-200"
                                    required>
                            </div>
                        </div>
                        <div class="error-message">يرجى تحديد فترة استحقاق الدفعة</div>
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
<div id="success-modal" class="fixed inset-0 hidden w-full h-full bg-gray-600 bg-opacity-50 z-60">
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
<div id="approve-modal" class="fixed inset-0 z-50 hidden w-full h-full overflow-y-auto bg-gray-600 bg-opacity-50">
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
<div id="reject-modal" class="fixed inset-0 z-50 hidden bg-gray-600 bg-opacity-50">
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

@endsection

@push('scripts')


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
        const paymentDueFromInput = document.getElementById('payment_due_from');
        const paymentDueToInput = document.getElementById('payment_due_to');


    // دالة لإضافة أيام لتاريخ معين
    function addDaysToDate(date, days) {
    const result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
    }

    // دالة لتحويل التاريخ إلى صيغة YYYY-MM-DD
    function formatDateForInput(date) {
    return date.toISOString().split('T')[0];
    }

    // دالة تحديث تواريخ الدفعة
    // function updatePaymentDates() {
    // const studyStartDate = studyStartDateInput.value;

    // if (studyStartDate) {
    // console.log('تحديث تواريخ الدفعة لتاريخ:', studyStartDate);

    // // تحويل التاريخ المختار إلى كائن Date
    // const startDate = new Date(studyStartDate);

    // // تعيين "من تاريخ" نفس قيمة تاريخ بدء الدراسة
    // paymentDueFromInput.value = studyStartDate;

    // // تعيين "إلى تاريخ" بإضافة 3 أيام
    // const endDate = addDaysToDate(startDate, 3);
    // paymentDueToInput.value = formatDateForInput(endDate);

    // // إضافة تأثير بصري لإظهار أن القيم تم تحديثها
    // paymentDueFromInput.style.backgroundColor = '#e6f3ff';
    // paymentDueToInput.style.backgroundColor = '#e6f3ff';
    // paymentDueFromInput.style.transition = 'background-color 0.3s ease';
    // paymentDueToInput.style.transition = 'background-color 0.3s ease';

    // // إزالة التأثير البصري بعد ثانيتين
    // setTimeout(function() {
    // paymentDueFromInput.style.backgroundColor = '';
    // paymentDueToInput.style.backgroundColor = '';
    // }, 2000);

    // console.log('تم تحديث التواريخ:', {
    // from: paymentDueFromInput.value,
    // to: paymentDueToInput.value
    // });

    // // إظهار رسالة نجاح
    // showNotification('تم تحديث تواريخ الدفعة تلقائياً', 'success', 2000);
    // } else {
    // // إذا تم مسح تاريخ بدء الدراسة، مسح تواريخ الدفعة أيضاً
    // paymentDueFromInput.value = '';
    // paymentDueToInput.value = '';
    // }
    // }


    // دالة تحديث تواريخ الدفعة
    function updatePaymentDates() {
    const studyStartDate = studyStartDateInput.value;

    if (studyStartDate) {
    console.log('تحديث تواريخ الدفعة لتاريخ:', studyStartDate);

    // تحويل التاريخ المختار إلى كائن Date
    const startDate = new Date(studyStartDate);

    // تعيين "من تاريخ" نفس قيمة تاريخ بدء الدراسة
    paymentDueFromInput.value = studyStartDate;

    // تعيين "إلى تاريخ" بإضافة 3 أيام
    const endDate = addDaysToDate(startDate, 3);
    paymentDueToInput.value = formatDateForInput(endDate);

    // تحديث Flatpickr إذا كان موجود
    if (paymentDueFromInput._flatpickr) {
    paymentDueFromInput._flatpickr.setDate(studyStartDate);
    }

    if (paymentDueToInput._flatpickr) {
    paymentDueToInput._flatpickr.setDate(formatDateForInput(endDate));
    }

    // إضافة تأثير بصري
    const fromDisplay = document.querySelector('input[data-input][readonly]');
    const toDisplay = document.querySelectorAll('input[data-input][readonly]')[1];

    if (fromDisplay) {
    fromDisplay.style.backgroundColor = '#e6f3ff';
    setTimeout(() => fromDisplay.style.backgroundColor = '', 2000);
    }

    if (toDisplay) {
    toDisplay.style.backgroundColor = '#e6f3ff';
    setTimeout(() => toDisplay.style.backgroundColor = '', 2000);
    }

    console.log('تم تحديث التواريخ:', {
    from: paymentDueFromInput.value,
    to: paymentDueToInput.value
    });

    showNotification('تم تحديث تواريخ الدفعة تلقائياً', 'success', 2000);
    } else {
    paymentDueFromInput.value = '';
    paymentDueToInput.value = '';

    if (paymentDueFromInput._flatpickr) {
    paymentDueFromInput._flatpickr.clear();
    }

    if (paymentDueToInput._flatpickr) {
    paymentDueToInput._flatpickr.clear();
    }
    }
    }


    // دالة تحديث "إلى تاريخ" عند تغيير "من تاريخ" يدوياً
    function updatePaymentToDate() {
    const fromDate = paymentDueFromInput.value;

    if (fromDate) {
    const startDate = new Date(fromDate);
    const endDate = addDaysToDate(startDate, 3);
    paymentDueToInput.value = formatDateForInput(endDate);

    // تأثير بصري
    paymentDueToInput.style.backgroundColor = '#e6f3ff';
    paymentDueToInput.style.transition = 'background-color 0.3s ease';

    setTimeout(function() {
    paymentDueToInput.style.backgroundColor = '';
    }, 1500);

    showNotification('تم تحديث تاريخ نهاية الدفعة', 'info', 1500);
    }
    }



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
                        class="font-semibold text-green-600 transition-colors duration-200 hover:text-green-800">
                        قبول
                    </button>
                    <button onclick="openRejectModal(${admission.id})"
                        class="font-semibold text-red-600 transition-colors duration-200 hover:text-red-800">
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
        group_id: groupId
        })
        });

        const result = await response.json();

        if (result.success) {
        showNotification(result.message, 'success');
        closeApproveModal();
        setTimeout(() => location.reload(), 1500);
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
            }
        }

        function closeAddAdmissionModal() {
            const modal = document.getElementById('add-admission-modal');
            if (modal) {
                modal.classList.add('hidden');
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
                appNumberField.classList.remove('valid', 'invalid');
                hideApplicationNumberMessages();
            }
        }

        // ==================== التحقق من رقم الطلب ====================
        function handleApplicationNumberInput(e) {
            let value = e.target.value;
            value = value.replace(/[^0-9]/g, '');
            if (value.length > 4) {
                value = value.substring(0, 4);
            }
            e.target.value = value;
            hideApplicationNumberMessages();
            e.target.classList.remove('valid', 'invalid');
        }

        function handleApplicationNumberBlur(e) {
            let value = e.target.value;
            if (value.length > 0 && value.length < 4) {
                value = value.padStart(4, '0');
                e.target.value = value;
            }
            if (value.length === 4) {
                validateApplicationNumber(value);
            }
        }

        function handleApplicationNumberKeyPress(e) {
            const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'Home', 'End', 'ArrowLeft', 'ArrowRight'];
            if (allowedKeys.includes(e.key)) {
                return true;
            }
            if (!/[0-9]/.test(e.key)) {
                e.preventDefault();
                return false;
            }
        }

        async function validateApplicationNumber(number) {
            if (isCheckingApplicationNumber) return;

            const appNumberField = document.getElementById('application_number');
            if (!appNumberField) return;

            isCheckingApplicationNumber = true;
            hideApplicationNumberMessages();
            showCheckingMessage();

            const numValue = parseInt(number);
            if (numValue > 1000) {
                showApplicationNumberError('رقم الطلب يجب أن يكون بين 0000 و 1000');
                appNumberField.classList.add('invalid');
                appNumberField.classList.remove('valid');
                isCheckingApplicationNumber = false;
                return;
            }

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

        // function showApplicationNumberError(message) {
        //     hideApplicationNumberMessages();
        //     const appNumberField = document.getElementById('application_number');
        //     const errorDiv = appNumberField?.nextElementSibling;
        //     if (errorDiv && errorDiv.classList.contains('error-message')) {
        //         errorDiv.textContent = message;
        //         errorDiv.style.display = 'block';
        //     }
        // }

        function showApplicationNumberError(message) {
            hideApplicationNumberMessages();

            // البحث عن div رسالة الخطأ بطريقة مختلفة
            const appNumberField = document.getElementById('application_number');

            if (appNumberField) {
            // البحث عن div الخطأ اللي جاي بعد الحقل مباشرة
            let errorDiv = appNumberField.nextElementSibling;

            // إذا ما لقاش، دور في كل العناصر اللي بعده
            while (errorDiv && !errorDiv.classList.contains('error-message')) {
            errorDiv = errorDiv.nextElementSibling;
            }

            // إذا لسه ما لقاش، دور في كل الصفحة
            if (!errorDiv) {
            const allErrorDivs = document.querySelectorAll('.error-message');
            allErrorDivs.forEach(div => {
            if (div.closest('div').querySelector('#application_number')) {
            errorDiv = div;
            }
            });
            }

            if (errorDiv && errorDiv.classList.contains('error-message')) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            errorDiv.style.color = '#ef4444';
            console.log('✅ تم إظهار رسالة الخطأ:', message);
            } else {
            console.warn('❌ لم يتم العثور على div رسالة الخطأ');
            // إنشاء رسالة خطأ مؤقتة إذا ما لقاش الـ div
            const tempError = document.createElement('div');
            tempError.style.cssText = 'color: #ef4444; font-size: 12px; margin-top: 4px;';
            tempError.textContent = message;
            appNumberField.parentNode.appendChild(tempError);

            setTimeout(() => tempError.remove(), 5000);
            }
            }
        }

        function showApplicationNumberSuccess() {
            hideApplicationNumberMessages();
            const successDiv = document.getElementById('success-message-app-number');
            if (successDiv) {
                successDiv.style.display = 'block';
            }
        }

        function showCheckingMessage() {
            hideApplicationNumberMessages();
            const checkingDiv = document.getElementById('checking-message-app-number');
            if (checkingDiv) {
                checkingDiv.innerHTML = '<span class="loading-spinner"></span> جاري التحقق من توفر الرقم...';
                checkingDiv.style.display = 'block';
            }
        }

        function hideApplicationNumberMessages() {
            const appNumberField = document.getElementById('application_number');
            const errorDiv = appNumberField?.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('error-message')) {
                errorDiv.style.display = 'none';
            }

            const successDiv = document.getElementById('success-message-app-number');
            if (successDiv) {
                successDiv.style.display = 'none';
            }

            const checkingDiv = document.getElementById('checking-message-app-number');
            if (checkingDiv) {
                checkingDiv.style.display = 'none';
            }
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
                        showNotification('تم حفظ البيانات بنجاح!', 'success');
                        closeAddAdmissionModal();
                        showSuccessModal();
                        setTimeout(() => location.reload(), 2000);
                    } else if (response.status === 422) {
                        const errors = result.errors;
                        if (errors && typeof errors === 'object') {
                            const firstKey = Object.keys(errors)[0];
                            const firstMsg = Array.isArray(errors[firstKey]) ? errors[firstKey][0] : errors[firstKey];
                            showNotification(firstMsg, 'error');
                        } else {
                            showNotification(result.message || 'يرجى مراجعة البيانات المدخلة', 'error');
                        }
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

                if (!window.jspdf && typeof jsPDF === 'undefined') {
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

                const { jsPDF } = window.jspdf || { jsPDF: jsPDF };
                const imgData   = canvas.toDataURL('image/png');

                // A4 dimensions in mm
                const pageW  = 210;
                const pageH  = 297;
                const imgH   = (canvas.height * pageW) / canvas.width;

                const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });

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
                        <p style="margin: 0; color: #374151;"><strong>المبلغ المدفوع:</strong></p>
                        <p style="margin: 0; color: #374151;"><strong>تاريخ بدء الدراسة:</strong></p>
                        <p style="margin: 0; color: #374151;"><strong>تاريخ إستحقاق الدفعة:</strong></p>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 5px;">
                        <p style="margin: 0; color: #374151;">
                        <span style="color: #EE8100; font-weight: bold;">${savedAdmissionData.monthly_fee || '0'} شيكل</span>
                        </p>
                        <p style="margin: 0; color: #374151;">${savedAdmissionData.study_start_date || 'غير محدد'}</p>
                        <p style="margin: 0; color: #374151;">${savedAdmissionData.payment_due_from || 'غير محدد'} - ${savedAdmissionData.payment_due_to || 'غير محدد'}</p>
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
            notification.className = `notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white max-w-sm transition-all duration-300 ${getNotificationClass(type)}`;
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
            'study_start_date',
            'payment_due_from',
            'payment_due_to'
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

        const appNumberField = document.getElementById('application_number');
        if (appNumberField) {
            appNumberField.addEventListener('input', handleApplicationNumberInput);
            appNumberField.addEventListener('blur', handleApplicationNumberBlur);
            appNumberField.addEventListener('keypress', handleApplicationNumberKeyPress);
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

        // ========== ربط أحداث تواريخ الدفعة ==========
        if (studyStartDateInput) {
        studyStartDateInput.addEventListener('change', updatePaymentDates);
        studyStartDateInput.addEventListener('input', updatePaymentDates);
        console.log('✅ تم ربط event listener لتاريخ بدء الدراسة');
        } else {
        console.warn('❌ لم يتم العثور على حقل تاريخ بدء الدراسة');
        }

        if (paymentDueFromInput) {
        paymentDueFromInput.addEventListener('change', updatePaymentToDate);
        paymentDueFromInput.addEventListener('input', updatePaymentToDate);
        console.log('✅ تم ربط event listener لحقل "من تاريخ"');
        } else {
        console.warn('❌ لم يتم العثور على حقل "من تاريخ"');
        }

        // اختبار وجود العناصر
        console.log('فحص عناصر تواريخ الدفعة:', {
        studyStartDate: !!studyStartDateInput,
        paymentFrom: !!paymentDueFromInput,
        paymentTo: !!paymentDueToInput
        });


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

        window.updatePaymentDates = updatePaymentDates;
        window.updatePaymentToDate = updatePaymentToDate;
        window.searchByName = searchByName;

        console.log('%c🎓 نظام إدارة طلبات الانتساب ', 'background: #2778E5; color: #EE8100; font-size: 16px; padding: 8px; border-radius: 4px;');
        console.log('✅ تم تحميل النظام بنجاح! جميع الوظائف متاحة.');
        showNotification('تم تحميل النظام بنجاح', 'success', 3000);
    });
</script>

@endpush