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
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">طلبات الانتساب</h3>
            <div class="flex items-center space-x-3 space-x-reverse">
                <!-- Filter -->
                <select class="px-3 py-2 text-sm border border-gray-300 rounded-md">
                    <option value="">جميع الحالات</option>
                    <option value="pending">في الانتظار</option>
                    <option value="approved">مقبول</option>
                    <option value="rejected">مرفوض</option>
                </select>
                <!-- Add Button -->
                <button onclick="openAddAdmissionModal()"
                    class="px-4 py-2 text-sm font-medium text-white rounded-md bg-primary hover:bg-blue-700">
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
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($admissions as $admission)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $admission->student_name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $admission->parent_name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $admission->phone }}</div>
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
                            <form method="POST" action="{{ route('admin.admissions.reject', $admission) }}"
                                class="inline">
                                @csrf
                                <button type="button"
                                    class="font-semibold text-red-600 transition-colors duration-200 hover:text-red-800"
                                    onclick="openRejectModal(this.closest('form'))">
                                    رفض
                                </button>
                            </form>
                        </div>
                        @else
                        <span class="font-medium text-gray-400">تم المعالجة</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
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
        {{-- <div class="p-6 overflow-y-auto max-h-96">
            <form id="add-admission-form" method="POST" action="{{ route('admin.admissions.store') }}">
                @csrf

                <!-- القسم الأول: بيانات الطلب -->
                <div class="form-section">
                    <h4>بيانات الطلب</h4>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">اليوم</label>
                            <select name="day" id="day"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
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
                            <label class="block mb-2 text-sm font-medium text-gray-700">تاريخ تقديم الطلب</label>
                            <div class="custom-datepicker">
                                <input type="date" name="application_date" id="application_date" class="w-full"
                                    required>
                            </div>
                            <div class="error-message">يرجى اختيار تاريخ تقديم الطلب</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">رقم الطلب</label>
                            <input type="text" name="application_number" id="application_number"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <div class="error-message">يرجى إدخال رقم الطلب</div>
                        </div>
                    </div>
                </div>

                <!-- القسم الثاني: بيانات الطالب -->
                <div class="form-section">
                    <h4>بيانات الطالب</h4>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">الاسم الرباعي</label>
                            <input type="text" name="student_name" id="student_name"
                                placeholder="الاسم الأول الثاني الثالث الأخير"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <div class="error-message">يرجى إدخال الاسم الرباعي كاملاً</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">رقم الهوية</label>
                            <input type="text" name="student_id" id="student_id" maxlength="9" placeholder="9 أرقام"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <div class="error-message">يرجى إدخال رقم هوية صحيح (9 أرقام)</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">تاريخ الميلاد</label>
                            <div class="custom-datepicker">
                                <input type="date" name="birth_date" id="birth_date" class="w-full" required>
                            </div>
                            <div class="error-message">يرجى اختيار تاريخ الميلاد</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">المرحلة الدراسية</label>
                            <select name="grade" id="grade"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                            <label class="block mb-2 text-sm font-medium text-gray-700">المستوى الأكاديمي</label>
                            <select name="academic_level" id="academic_level"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
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

                <!-- القسم الثالث: بيانات ولي الأمر -->
                <div class="form-section">
                    <h4>بيانات ولي الأمر</h4>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">الاسم الثلاثي</label>
                            <input type="text" name="parent_name" id="parent_name"
                                placeholder="الاسم الأول الثاني الأخير"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <div class="error-message">يرجى إدخال اسم ولي الأمر ثلاثياً</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">رقم الهوية</label>
                            <input type="text" name="parent_id" id="parent_id" maxlength="9" placeholder="9 أرقام"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <div class="error-message">يرجى إدخال رقم هوية صحيح (9 أرقام)</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">المهنة</label>
                            <input type="text" name="parent_job" id="parent_job"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <div class="error-message">يرجى إدخال مهنة ولي الأمر</div>
                        </div>
                    </div>
                </div>

                <!-- القسم الرابع: بيانات التواصل -->
                <div class="form-section">
                    <h4>بيانات التواصل</h4>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">رقم جوال الأب</label>
                            <input type="tel" name="father_phone" id="father_phone" placeholder="05xxxxxxxx"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <div class="error-message">يرجى إدخال رقم جوال الأب</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">رقم جوال الأم</label>
                            <input type="tel" name="mother_phone" id="mother_phone" placeholder="05xxxxxxxx"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="error-message">يرجى إدخال رقم جوال صحيح</div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-700">عنوان السكن بالتفصيل</label>
                            <textarea name="address" id="address" rows="3" placeholder="اكتب العنوان بالتفصيل"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required></textarea>
                            <div class="error-message">يرجى إدخال عنوان السكن</div>
                        </div>
                    </div>
                </div>

                <!-- القسم الخامس: المعلومات المالية -->
                <div class="form-section">
                    <h4>المعلومات المالية</h4>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">قيمة الرسوم الشهرية</label>
                            <input type="number" name="monthly_fee" id="monthly_fee" placeholder="0.00" step="0.01"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <div class="error-message">يرجى إدخال قيمة الرسوم الشهرية</div>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">تاريخ بدء الدراسة</label>
                            <div class="custom-datepicker">
                                <input type="date" name="study_start_date" id="study_start_date" class="w-full"
                                    required>
                            </div>
                            <div class="error-message">يرجى اختيار تاريخ بدء الدراسة</div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-700">فترة استحقاق الدفعة
                                الشهرية</label>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-1 text-xs text-gray-500">من تاريخ</label>
                                    <div class="custom-datepicker">
                                        <input type="date" name="payment_due_from" id="payment_due_from" class="w-full"
                                            required>
                                    </div>
                                </div>
                                <div>
                                    <label class="block mb-1 text-xs text-gray-500">إلى تاريخ</label>
                                    <div class="custom-datepicker">
                                        <input type="date" name="payment_due_to" id="payment_due_to" class="w-full"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="error-message">يرجى تحديد فترة استحقاق الدفعة</div>
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
        </div> --}}


        <div
            class="p-8 bg-gradient-to-br from-gray-50 to-white rounded-2xl shadow-2xl border-[3px] border-orange-500 max-w-6xl mx-auto font-[Segoe UI] text-right">

            <!-- Header -->
            <div class="pb-5 mb-8 text-center border-b-4 border-orange-500">
                <h1 class="text-3xl font-bold text-blue-600">🎓 نموذج انتساب جديد</h1>
                <p class="mt-2 text-sm text-gray-500">تاريخ الإصدار: {{ now()->format('d/m/Y') }}</p>
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
                            <input type="date" name="application_date
                            " id="application_date"
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
                            <label class="block mb-2 text-sm font-medium">المبلغ المدفوع</label>
                            <input type="number" name="monthly_fee" id="monthly_fee"
                                class="w-full px-3 py-2 text-black border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-600"
                                placeholder="0.00" step="1.00" required>
                            <div class="error-message">يرجى إدخال قيمة الرسوم الشهرية</div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">تاريخ بدء الدراسة</label>
                            <input type="date" name="study_start_date" id="study_start_date" class="w-full"
                                placeholder="YYYY-MM-DD" required>
                            <div class="error-message">يرجى اختيار تاريخ بدء الدراسة</div>
                        </div>
                    </div>
                    <div class="mt-2 md:col-span-2">
                        <label class="block mb-2 text-sm font-medium">فترة استحقاق الدفعة الشهرية</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1 text-xs text-gray-500">من تاريخ</label>
                                <input type="date" name="payment_due_from" id="payment_due_from" class="w-full"
                                    required>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs text-gray-500">إلى تاريخ</label>
                                <input type="date" name="payment_due_to" id="payment_due_to" class="w-full" required>
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

        <!-- Modal Body -->
        {{-- <div
            class="p-8 bg-gradient-to-br from-gray-50 to-white rounded-2xl shadow-2xl border-[3px] border-orange-500 max-w-6xl mx-auto font-[Segoe UI] text-right">

            <!-- Header -->
            <div class="pb-5 mb-8 text-center border-b-4 border-orange-500">
                <h1 class="text-3xl font-bold text-blue-600">🎓 نموذج انتساب جديد</h1>
                <p class="mt-2 text-sm text-gray-500">تاريخ الإصدار: {{ now()->format('d/m/Y') }}</p>
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

                <!-- معلومات الاتصال -->
                <div class="p-6 mb-6 border-t-4 border-blue-600 shadow-sm bg-gray-70 rounded-xl">
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
                            <label class="block mb-2 text-sm font-medium">المبلغ المدفوع</label>
                            <input type="number" name="monthly_fee"
                                class="w-full px-3 py-2 text-black border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-600"
                                placeholder="0.00" step="1.00" required>
                            <div class="error-message">يرجى إدخال قيمة الرسوم الشهرية</div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">تاريخ بدء الدراسة</label>
                            <div class="custom-datepicker">
                                <input type="date" name="study_start_date" id="study_start_date" class="w-full"
                                    placeholder="YYYY-MM-DD" required>
                            </div>
                            <div class="error-message">يرجى اختيار تاريخ بدء الدراسة</div>
                        </div>
                    </div>
                    <div class="mt-2 md:col-span-2">
                        <lab el class="block mb-2 text-sm font-medium">فترة استحقاق الدفعة
                            الشهرية</lab>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1 text-xs text-gray-500">من تاريخ</label>
                                <div class="custom-datepicker">
                                    <input type="date" name="payment_due_from" id="payment_due_from" class="w-full"
                                        placeholder="YYYY-MM-DD" required>
                                </div>
                            </div>
                            <div>
                                <label class="block mb-1 text-xs text-gray-500">إلى تاريخ</label>
                                <div class="custom-datepicker">
                                    <input type="date" name="payment_due_to" id="payment_due_to" class="w-full"
                                        placeholder="YYYY-MM-DD" required>
                                </div>
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
        </div> --}}

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
        <p class="mb-4 text-center">هل أنت متأكد من رفض هذا الطلب؟</p>
        <div class="flex justify-between">
            <button onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 rounded">إلغاء</button>
            <button onclick="submitRejectForm()" class="px-4 py-2 text-white bg-red-500 rounded">رفض</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')


{{-- <script>
    let savedAdmissionData = {};

            // فتح modal إضافة طلب جديد
            function openAddAdmissionModal() {
                document.getElementById('add-admission-modal').classList.remove('hidden');
            }

            // إغلاق modal إضافة طلب جديد
            function closeAddAdmissionModal() {
                document.getElementById('add-admission-modal').classList.add('hidden');
                resetForm();
            }

            // إعادة تعيين النموذج
            function resetForm() {
                document.getElementById('add-admission-form').reset();
                clearAllErrors();
            }

            // إزالة جميع الأخطاء
            function clearAllErrors() {
                const errorFields = document.querySelectorAll('.field-error');
                errorFields.forEach(field => {
                    field.classList.remove('field-error');
                });
            }

            // التحقق من صحة النموذج
            function validateForm() {
                let isValid = true;
                clearAllErrors();

                // التحقق من بيانات الطلب
                isValid = validateField('day', 'يرجى اختيار اليوم') && isValid;
                isValid = validateField('application_date', 'يرجى اختيار تاريخ تقديم الطلب') && isValid;
                isValid = validateField('application_number', 'يرجى إدخال رقم الطلب') && isValid;

                // التحقق من بيانات الطالب
                isValid = validateStudentName() && isValid;
                isValid = validateStudentId() && isValid;
                isValid = validateField('birth_date', 'يرجى اختيار تاريخ الميلاد') && isValid;
                isValid = validateField('grade', 'يرجى اختيار المرحلة الدراسية') && isValid;
                isValid = validateField('academic_level', 'يرجى اختيار المستوى الأكاديمي') && isValid;

                // التحقق من بيانات ولي الأمر
                isValid = validateParentName() && isValid;
                isValid = validateParentId() && isValid;
                isValid = validateField('parent_job', 'يرجى إدخال مهنة ولي الأمر') && isValid;

                // التحقق من بيانات التواصل
                isValid = validatePhone('father_phone', 'يرجى إدخال رقم جوال الأب صحيح') && isValid;
                isValid = validatePhoneOptional('mother_phone') && isValid;
                isValid = validateField('address', 'يرجى إدخال عنوان السكن') && isValid;

                // التحقق من المعلومات المالية
                isValid = validateField('monthly_fee', 'يرجى إدخال قيمة الرسوم الشهرية') && isValid;
                isValid = validateField('study_start_date', 'يرجى اختيار تاريخ بدء الدراسة') && isValid;
                isValid = validateField('payment_due_from', 'يرجى تحديد تاريخ بداية استحقاق الدفعة') && isValid;
                isValid = validateField('payment_due_to', 'يرجى تحديد تاريخ نهاية استحقاق الدفعة') && isValid;

                return isValid;
            }

            // التحقق من حقل عام
            function validateField(fieldId, errorMessage) {
                const field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    showFieldError(field, errorMessage);
                    return false;
                }
                return true;
            }

            // التحقق من اسم الطالب الرباعي
            function validateStudentName() {
                const field = document.getElementById('student_name');
                const name = field.value.trim();
                const nameParts = name.split(/\s+/);

                if (!name) {
                    showFieldError(field, 'يرجى إدخال اسم الطالب');
                    return false;
                }

                if (nameParts.length < 4) {
                    showFieldError(field, 'يرجى إدخال الاسم الرباعي كاملاً (4 أسماء على الأقل)');
                    return false;
                }

                return true;
            }

            // التحقق من اسم ولي الأمر الثلاثي
            function validateParentName() {
                const field = document.getElementById('parent_name');
                const name = field.value.trim();
                const nameParts = name.split(/\s+/);

                if (!name) {
                    showFieldError(field, 'يرجى إدخال اسم ولي الأمر');
                    return false;
                }

                if (nameParts.length < 3) {
                    showFieldError(field, 'يرجى إدخال الاسم الثلاثي كاملاً (3 أسماء على الأقل)');
                    return false;
                }

                return true;
            }

            // التحقق من رقم هوية الطالب
            function validateStudentId() {
                const field = document.getElementById('student_id');
                const id = field.value.trim();

                if (!id) {
                    showFieldError(field, 'يرجى إدخال رقم الهوية');
                    return false;
                }

                if (!/^\d{9}$/.test(id)) {
                    showFieldError(field, 'رقم الهوية يجب أن يكون 9 أرقام فقط');
                    return false;
                }

                return true;
            }

            // التحقق من رقم هوية ولي الأمر
            function validateParentId() {
                const field = document.getElementById('parent_id');
                const id = field.value.trim();

                if (!id) {
                    showFieldError(field, 'يرجى إدخال رقم هوية ولي الأمر');
                    return false;
                }

                if (!/^\d{9}$/.test(id)) {
                    showFieldError(field, 'رقم الهوية يجب أن يكون 9 أرقام فقط');
                    return false;
                }

                return true;
            }

            // التحقق من رقم الهاتف
            function validatePhone(fieldId, errorMessage) {
                const field = document.getElementById(fieldId);
                const phone = field.value.trim();

                if (!phone) {
                    showFieldError(field, errorMessage);
                    return false;
                }

                if (!/^05\d{8}$/.test(phone)) {
                    showFieldError(field, 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام');
                    return false;
                }

                return true;
            }

            // التحقق من رقم الهاتف الاختياري
            function validatePhoneOptional(fieldId) {
                const field = document.getElementById(fieldId);
                const phone = field.value.trim();

                if (phone && !/^05\d{8}$/.test(phone)) {
                    showFieldError(field, 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام');
                    return false;
                }

                return true;
            }

            // إظهار خطأ الحقل
            function showFieldError(field, message) {
                field.classList.add('field-error');
                const errorDiv = field.nextElementSibling;
                if (errorDiv && errorDiv.classList.contains('error-message')) {
                    errorDiv.textContent = message;
                }
            }

            // معالجة إرسال النموذج
            document.getElementById('add-admission-form').addEventListener('submit', function(e) {
                e.preventDefault();

                if (validateForm()) {
                    // حفظ البيانات
                    const formData = new FormData(this);
                    savedAdmissionData = {};
                    for (let [key, value] of formData.entries()) {
                        savedAdmissionData[key] = value;
                    }

                    // إغلاق نموذج الإضافة
                    closeAddAdmissionModal();

                    // إظهار نموذج النجاح
                    showSuccessModal();

                    // هنا يمكنك إضافة كود إرسال البيانات للخادم
                    // fetch('/admin/admissions', {
                    //     method: 'POST',
                    //     body: formData
                    // });
                }
            });

            // إظهار نموذج النجاح
            function showSuccessModal() {
                const modal = document.getElementById('success-modal');
                modal.classList.remove('hidden');

                // إخفاء النموذج تلقائياً بعد 5 ثوانِ
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 5000);
            }

            // تصدير كصورة
            function exportAsImage() {
                // إنشاء عنصر HTML يحتوي على البيانات
                const dataElement = createDataDisplay();
                document.body.appendChild(dataElement);

                html2canvas(dataElement).then(canvas => {
                    // تحويل إلى صورة وتحميلها
                    const link = document.createElement('a');
                    link.download = `طلب_انتساب_${savedAdmissionData.student_name || 'جديد'}.png`;
                    link.href = canvas.toDataURL();
                    link.click();

                    // إزالة العنصر المؤقت
                    document.body.removeChild(dataElement);
                });
            }

            // تصدير كملف PDF
            function exportAsPDF() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();

                // إضافة النص العربي (يحتاج خط عربي)
                doc.text('طلب انتساب جديد', 105, 20, { align: 'center' });
                doc.text(`اسم الطالب: ${savedAdmissionData.student_name || ''}`, 20, 40);
                doc.text(`رقم الهوية: ${savedAdmissionData.student_id || ''}`, 20, 50);
                doc.text(`اسم ولي الأمر: ${savedAdmissionData.parent_name || ''}`, 20, 60);
                doc.text(`رقم جوال الأب: ${savedAdmissionData.father_phone || ''}`, 20, 70);
                doc.text(`المرحلة الدراسية: ${savedAdmissionData.grade || ''}`, 20, 80);

                doc.save(`طلب_انتساب_${savedAdmissionData.student_name || 'جديد'}.pdf`);
            }

            // إنشاء عرض البيانات للتصدير
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
            <!-- Header مع الشعار -->
            <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #EE8100;">
                <h1 style="color: #2778E5; font-size: 32px; margin: 0; font-weight: bold;">
                    🎓 نموذج انتساب جديد
                </h1>
                <p style="color: #666; margin: 10px 0 0 0; font-size: 14px;">
                    تاريخ الإصدار: ${new Date().toLocaleDateString('ar-PS')}
                </p>
            </div>

            <!-- بيانات الطلب -->
            <div style="background: linear-gradient(135deg, #2778E5 0%, #1e40af 100%);
                                color: white; padding: 20px; margin-bottom: 20px; border-radius: 15px;">
                <h3 style="margin: 0 0 15px 0; font-size: 20px; border-bottom: 2px solid #EE8100;
                                padding-bottom: 8px;">📋 بيانات الطلب</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <p style="margin: 0;"><strong>اليوم:</strong> ${savedAdmissionData.day || 'غير محدد'}</p>
                    <p style="margin: 0;"><strong>تاريخ التقديم:</strong> ${savedAdmissionData.application_date || 'غير محدد'}</p>
                    <p style="margin: 0;"><strong>رقم الطلب:</strong> ${savedAdmissionData.application_number || 'يتم توليده تلقائياً'}</p>
                </div>
            </div>

            <!-- بيانات الطالب -->
            <div style="background: #f8fafc; border: 2px solid #e5e7eb; padding: 20px;
                                margin-bottom: 20px; border-radius: 15px; border-right: 5px solid #2778E5;">
                <h3 style="color: #2778E5; margin: 0 0 15px 0; font-size: 18px;
                                border-bottom: 2px solid #EE8100; padding-bottom: 8px;">👨‍🎓 بيانات الطالب</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <p style="margin: 0 0 10px 0; color: #374151;"><strong>الاسم:</strong> ${savedAdmissionData.student_name || 'غير محدد'}</p>
                    <p style="margin: 0 0 10px 0; color: #374151;"><strong>رقم الهوية:</strong> ${savedAdmissionData.student_id ||
                        'غير محدد'}</p>
                    <p style="margin: 0 0 10px 0; color: #374151;"><strong>تاريخ الميلاد:</strong> ${savedAdmissionData.birth_date
                        || 'غير محدد'}</p>
                    <p style="margin: 0 0 10px 0; color: #374151;"><strong>المرحلة الدراسية:</strong> ${savedAdmissionData.grade ||
                        'غير محدد'}</p>
                </div>
                <p style="margin: 10px 0 0 0; color: #374151;"><strong>المستوى الأكاديمي:</strong>
                    <span style="background: #EE8100; color: white; padding: 8px; margin-top: 4px; border-radius: 8px; font-size: 14px;">
                        ${savedAdmissionData.academic_level || 'غير محدد'}
                    </span>
                </p>
            </div>

            <!-- بيانات ولي الأمر والتواصل -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div style="background: #f8fafc; border: 2px solid #e5e7eb; padding: 20px;
                                border-radius: 15px; border-right: 5px solid #EE8100;">
                <h3 style="color: #EE8100; margin: 0 0 15px 0; font-size: 18px;
                                   border-bottom: 2px solid #2778E5; padding-bottom: 8px;">👨‍💼 بيانات ولي الأمر</h3>
                <p style="margin: 0 0 10px 0; color: #374151;"><strong>الاسم:</strong> ${savedAdmissionData.parent_name || 'غير محدد'}</p>
                <p style="margin: 0 0 10px 0; color: #374151;"><strong>رقم الهوية:</strong> ${savedAdmissionData.parent_id ||
                    'غير محدد'}</p>
                <p style="margin: 0; color: #374151;"><strong>المهنة:</strong> ${savedAdmissionData.parent_job || 'غير محدد'}
                </p>
            </div>

            <div style="background: #f8fafc; border: 2px solid #e5e7eb; padding: 20px;
                                border-radius: 15px; border-right: 5px solid #2778E5;">
                <h3 style="color: #2778E5; margin: 0 0 15px 0; font-size: 18px;
                                   border-bottom: 2px solid #EE8100; padding-bottom: 8px;">📞 بيانات التواصل</h3>
                <p style="margin: 0 0 10px 0; color: #374151;"><strong>جوال الأب:</strong> ${savedAdmissionData.father_phone ||
                    'غير محدد'}</p>
                <p style="margin: 0 0 10px 0; color: #374151;"><strong>جوال الأم:</strong> ${savedAdmissionData.mother_phone ||
                    'غير متوفر'}</p>
            </div>
            </div>

            <!-- العنوان -->
            <div style="background: linear-gradient(135deg, #EE8100 0%, #d67100 100%); color: white; padding: 20px; margin-bottom: 20px; border-radius: 15px;">
                <h3 style="margin: 0 0 10px 0; font-size: 18px;">🏠 عنوان السكن</h3>
                <p style="margin: 0; font-size: 16px; line-height: 1.5;">${savedAdmissionData.address || 'غير محدد'}</p>
            </div>

            <!-- المعلومات المالية -->
            <div style="background: #f8fafc; border: 2px solid #e5e7eb; padding: 20px;
                                border-radius: 15px; border: 3px solid #2778E5;">
                <h3 style="color: #2778E5; margin: 0 0 15px 0; font-size: 18px;
                                border-bottom: 2px solid #EE8100; padding-bottom: 8px;">💰 المعلومات المالية</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                    <p style="margin: 0; color: #374151;"><strong>الرسوم الشهرية:</strong>
                        <span style="color: #EE8100; font-weight: bold;">${savedAdmissionData.monthly_fee || '0'} شيكل</span>
                    </p>
                    <p style="margin: 0; color: #374151;"><strong>تاريخ بدء الدراسة:</strong> ${savedAdmissionData.study_start_date || 'غير محدد'}</p>
                    <p style="margin: 0; color: #374151;"><strong>فترة الاستحقاق:</strong> ${savedAdmissionData.payment_due_from || 'غير محدد'} - ${savedAdmissionData.payment_due_to || 'غير محدد'}</p>
                </div>
            </div>

            <!-- Footer -->
            <div style="text-align: center; margin-top: 30px; padding-top: 20px;
                                border-top: 2px solid #EE8100; color: #666; font-size: 12px;">
                <p style="margin: 0;">تم إنشاء هذا المستند تلقائياً من نظام إدارة طلبات الانتساب</p>
            </div>
            `;

            return div;
            }

            // منع إغلاق النافذة بالنقر خارجها
            document.getElementById('add-admission-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    e.stopPropagation();
                }
            });

            document.getElementById('success-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    e.stopPropagation();
                }
            });

            // الدوال الموجودة مسبقاً
            function openApproveModal(admissionId, studentName) {
                document.getElementById('approve-modal').classList.remove('hidden');
                document.getElementById('approve-form').action = `/admin/admissions/${admissionId}/approve`;
            }

            function closeApproveModal() {
                document.getElementById('approve-modal').classList.add('hidden');
            }

            function openRejectModal(form) {
                window.currentRejectForm = form;
                document.getElementById('reject-modal').classList.remove('hidden');
            }

            function closeRejectModal() {
                document.getElementById('reject-modal').classList.add('hidden');
            }

            function submitRejectForm() {
                if (window.currentRejectForm) {
                    window.currentRejectForm.submit();
                }
            }

            // تقييد إدخال الأرقام فقط لحقول الهوية
            document.getElementById('student_id').addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            document.getElementById('parent_id').addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            // تقييد إدخال أرقام الهاتف
            document.getElementById('father_phone').addEventListener('input', function(e) {
                let value = this.value.replace(/[^0-9]/g, '');
                if (value.length > 10) {
                    value = value.substring(0, 10);
                }
                this.value = value;
            });

            document.getElementById('mother_phone').addEventListener('input', function(e) {
                let value = this.value.replace(/[^0-9]/g, '');
                if (value.length > 10) {
                    value = value.substring(0, 10);
                }
                this.value = value;
            });
</script> --}}

{{-- <script>
    document.addEventListener("DOMContentLoaded", function() {
        // متغيرات عامة
        let savedAdmissionData = {};
        const form = document.getElementById("add-admission-form");
        const successModal = document.getElementById("success-modal");

        // إخفاء رسائل الخطأ افتراضياً
        document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');

        // ==================== وظائف إدارة Modal ====================

        // فتح modal إضافة طلب جديد
        window.openAddAdmissionModal = function() {
            document.getElementById('add-admission-modal').classList.remove('hidden');
        };

        // إغلاق modal إضافة طلب جديد
        window.closeAddAdmissionModal = function() {
            document.getElementById('add-admission-modal').classList.add('hidden');
            resetForm();
        };

        // إعادة تعيين النموذج
        function resetForm() {
            if (form) {
                form.reset();
            }
            clearAllErrors();
        }

        // إزالة جميع الأخطاء
        function clearAllErrors() {
            document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
            const errorFields = document.querySelectorAll('.field-error');
            errorFields.forEach(field => {
                field.classList.remove('field-error');
            });
        }

        // ==================== وظائف التحقق ====================

        // التحقق من صحة النموذج
        function validateForm() {
            let isValid = true;
            clearAllErrors();

            // التحقق من بيانات الطلب
            isValid = validateField('day', 'يرجى اختيار اليوم') && isValid;
            isValid = validateField('application_date', 'يرجى اختيار تاريخ تقديم الطلب') && isValid;
            // رقم الطلب اختياري (يمكن توليده تلقائياً)

            // التحقق من بيانات الطالب
            isValid = validateStudentName() && isValid;
            isValid = validateStudentId() && isValid;
            isValid = validateField('birth_date', 'يرجى اختيار تاريخ الميلاد') && isValid;
            isValid = validateField('grade', 'يرجى اختيار المرحلة الدراسية') && isValid;
            isValid = validateField('academic_level', 'يرجى اختيار المستوى الأكاديمي') && isValid;

            // التحقق من بيانات ولي الأمر
            isValid = validateParentName() && isValid;
            isValid = validateParentId() && isValid;
            isValid = validateField('parent_job', 'يرجى إدخال مهنة ولي الأمر') && isValid;

            // التحقق من بيانات التواصل
            isValid = validatePhone('father_phone', 'يرجى إدخال رقم جوال الأب صحيح') && isValid;
            isValid = validatePhoneOptional('mother_phone') && isValid;
            isValid = validateField('address', 'يرجى إدخال عنوان السكن') && isValid;

            // التحقق من المعلومات المالية
            isValid = validateField('monthly_fee', 'يرجى إدخال قيمة الرسوم الشهرية') && isValid;
            isValid = validateField('study_start_date', 'يرجى اختيار تاريخ بدء الدراسة') && isValid;
            isValid = validateField('payment_due_from', 'يرجى تحديد تاريخ بداية استحقاق الدفعة') && isValid;
            isValid = validateField('payment_due_to', 'يرجى تحديد تاريخ نهاية استحقاق الدفعة') && isValid;

            return isValid;
        }

        // التحقق من حقل عام
        function validateField(fieldId, errorMessage) {
            const field = document.getElementById(fieldId);
            if (!field || !field.value.trim()) {
                if (field) showFieldError(field, errorMessage);
                return false;
            }
            return true;
        }

        // التحقق من اسم الطالب الرباعي
        function validateStudentName() {
            const field = document.getElementById('student_name');
            const name = field.value.trim();
            const nameParts = name.split(/\s+/).filter(part => part.length > 0);

            if (!name) {
                showFieldError(field, 'يرجى إدخال اسم الطالب');
                return false;
            }

            if (nameParts.length < 4) {
                showFieldError(field, 'يرجى إدخال الاسم الرباعي كاملاً (4 أسماء على الأقل)');
                return false;
            }

            return true;
        }

        // التحقق من اسم ولي الأمر الثلاثي
        function validateParentName() {
            const field = document.getElementById('parent_name');
            const name = field.value.trim();
            const nameParts = name.split(/\s+/).filter(part => part.length > 0);

            if (!name) {
                showFieldError(field, 'يرجى إدخال اسم ولي الأمر');
                return false;
            }

            if (nameParts.length < 3) {
                showFieldError(field, 'يرجى إدخال الاسم الثلاثي كاملاً (3 أسماء على الأقل)');
                return false;
            }

            return true;
        }

        // التحقق من رقم هوية الطالب
        function validateStudentId() {
            const field = document.getElementById('student_id');
            const id = field.value.trim();

            if (!id) {
                showFieldError(field, 'يرجى إدخال رقم الهوية');
                return false;
            }

            if (!/^\d{9}$/.test(id)) {
                showFieldError(field, 'رقم الهوية يجب أن يكون 9 أرقام فقط');
                return false;
            }

            return true;
        }

        // التحقق من رقم هوية ولي الأمر
        function validateParentId() {
            const field = document.getElementById('parent_id');
            const id = field.value.trim();

            if (!id) {
                showFieldError(field, 'يرجى إدخال رقم هوية ولي الأمر');
                return false;
            }

            if (!/^\d{9}$/.test(id)) {
                showFieldError(field, 'رقم الهوية يجب أن يكون 9 أرقام فقط');
                return false;
            }

            return true;
        }

        // التحقق من رقم الهاتف
        function validatePhone(fieldId, errorMessage) {
            const field = document.getElementById(fieldId);
            const phone = field.value.trim();

            if (!phone) {
                showFieldError(field, errorMessage);
                return false;
            }

            if (!/^05\d{8}$/.test(phone)) {
                showFieldError(field, 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام');
                return false;
            }

            return true;
        }

        // التحقق من رقم الهاتف الاختياري
        function validatePhoneOptional(fieldId) {
            const field = document.getElementById(fieldId);
            const phone = field.value.trim();

            if (phone && !/^05\d{8}$/.test(phone)) {
                showFieldError(field, 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام');
                return false;
            }

            return true;
        }

        // إظهار خطأ الحقل
        function showFieldError(field, message) {
            field.classList.add('field-error');
            const errorDiv = field.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('error-message')) {
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
            }
        }

        // ==================== تهيئة التقويم ====================

        // تفعيل flatpickr للحقول التي نوعها date
        const dateFields = [
            'application_date',
            'student_dob',
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

        // ==================== معالجة إرسال النموذج ====================

        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!validateForm()) {
                    return;
                }


                // إظهار مؤشر التحميل
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'جاري الحفظ...';
                submitBtn.disabled = true;

                try {
                    // جمع البيانات
                    const formData = new FormData(form);
                    savedAdmissionData = {};
                    for (let [key, value] of formData.entries()) {
                        savedAdmissionData[key] = value;
                    }

                    // محاولة إرسال للخادم
                    const response = await fetch(form.action || '/admin/admissions', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.success) {
                            showNotification('تم حفظ البيانات بنجاح!', 'success');
                            closeAddAdmissionModal();
                            showSuccessModal();

                            // إعادة تحميل الصفحة بعد فترة
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                            return;
                        }
                    }

                    // في حالة فشل الإرسال، استخدم الحفظ المحلي
                    throw new Error('فشل في الإرسال للخادم');

                } catch (error) {
                    console.warn('تم الحفظ محلياً:', error.message);
                    showNotification('تم حفظ البيانات محلياً', 'warning');

                    // حفظ محلي وإظهار النتيجة
                    closeAddAdmissionModal();
                    showSuccessModal();
                } finally {
                    // إعادة تعيين زر الإرسال
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            });
        }

        // ==================== إظهار نتيجة النجاح ====================

        function showSuccessModal() {
            if (successModal) {
                successModal.classList.remove('hidden');

                // إخفاء تلقائي بعد 5 ثوانِ
                setTimeout(() => {
                    successModal.classList.add('hidden');
                }, 5000);
            }
        }

        // ==================== وظائف التصدير ====================

        // تصدير كصورة محسن
        window.exportAsImage = async function() {
            try {
                showNotification('جاري إنشاء الصورة...', 'info', 2000);

                const dataElement = createDataDisplay();
                document.body.appendChild(dataElement);

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

                // إغلاق modal النجاح
                setTimeout(() => {
                    successModal.classList.add('hidden');
                }, 1000);

            } catch (error) {
                console.error('خطأ في تصدير الصورة:', error);
                showNotification('خطأ في تصدير الصورة', 'error');
            }
        };

        // تصدير PDF محسن
        window.exportAsPDF = function() {
            try {
                showNotification('جاري إنشاء ملف PDF...', 'info', 2000);

                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();

                // إعدادات الخط والألوان
                doc.setFont('helvetica');
                doc.setFontSize(16);

                // عنوان الوثيقة
                doc.text('طلب انتساب جديد', 105, 20, { align: 'center' });

                let yPos = 40;
                const lineHeight = 10;

                // بيانات الطالب
                doc.setFontSize(14);
                doc.text('بيانات الطالب:', 20, yPos);
                yPos += lineHeight;

                doc.setFontSize(12);
                doc.text(`الاسم: ${savedAdmissionData.student_name || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight;
                doc.text(`رقم الهوية: ${savedAdmissionData.student_id || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight;
                doc.text(`المرحلة الدراسية: ${savedAdmissionData.grade || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight * 2;

                // بيانات ولي الأمر
                doc.setFontSize(14);
                doc.text('بيانات ولي الأمر:', 20, yPos);
                yPos += lineHeight;

                doc.setFontSize(12);
                doc.text(`الاسم: ${savedAdmissionData.parent_name || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight;
                doc.text(`المهنة: ${savedAdmissionData.parent_job || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight;
                doc.text(`رقم الجوال: ${savedAdmissionData.father_phone || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight * 2;

                // المعلومات المالية
                doc.setFontSize(14);
                doc.text('المعلومات المالية:', 20, yPos);
                yPos += lineHeight;

                doc.setFontSize(12);
                doc.text(`الرسوم الشهرية: ${savedAdmissionData.monthly_fee || '0'} شيكل`, 25, yPos);
                yPos += lineHeight;
                doc.text(`تاريخ بدء الدراسة: ${savedAdmissionData.study_start_date || 'غير محدد'}`, 25, yPos);

                // تاريخ الإنشاء
                doc.setFontSize(10);
                doc.text(`تاريخ الإنشاء: ${new Date().toLocaleDateString('ar-PS')}`, 20, 280);

                const fileName = `طلب_انتساب_${savedAdmissionData.student_name || 'جديد'}_${Date.now()}.pdf`;
                doc.save(fileName);

                showNotification('تم تصدير ملف PDF بنجاح!', 'success');

                // إغلاق modal النجاح
                setTimeout(() => {
                    successModal.classList.add('hidden');
                }, 1000);

            } catch (error) {
                console.error('خطأ في تصدير PDF:', error);
                showNotification('خطأ في تصدير ملف PDF', 'error');
            }
        };

        // إنشاء عرض البيانات للتصدير (محسن)
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
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #EE8100;">
                    <h1 style="color: #2778E5; font-size: 32px; margin: 0; font-weight: bold;">
                        🎓 نموذج انتساب جديد
                    </h1>
                    <p style="color: #666; margin: 10px 0 0 0; font-size: 14px;">
                        تاريخ الإصدار: ${new Date().toLocaleDateString('ar-PS')}
                    </p>
                </div>

                <!-- بيانات الطلب -->
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

                <!-- بيانات الطالب -->
                <div style="background: #f8fafc; border: 2px solid #e5e7eb; padding: 20px;
                            margin-bottom: 20px; border-radius: 15px; border-right: 5px solid #2778E5;">
                    <h3 style="color: #2778E5; margin: 0 0 15px 0; font-size: 18px;
                               border-bottom: 2px solid #EE8100; padding-bottom: 8px;">👨‍🎓 بيانات الطالب</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <p style="margin: 0 0 10px 0; color: #374151; display:block;"><strong>الاسم:</strong> ${savedAdmissionData.student_name || 'غير محدد'}</p>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>رقم الهوية:</strong> ${savedAdmissionData.student_id || 'غير محدد'}</p>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>تاريخ الميلاد:</strong> ${savedAdmissionData.birth_date || 'غير محدد'}</p>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>المرحلة الدراسية:</strong> ${savedAdmissionData.grade || 'غير محدد'}</p>
                    </div>
                    <p style="margin: 10px 0 0 0; color: #374151;"><strong>المستوى الأكاديمي:</strong>
                       <span style="background: #EE8100; color: white; place-items: center; padding: 12px; margin:12px; border-radius: 8px; font-size: 14px;">
                           ${savedAdmissionData.academic_level || 'غير محدد'}
                       </span>
                    </p>
                </div>

                <!-- بيانات ولي الأمر والتواصل -->
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



                <!-- المعلومات المالية -->
                <div style="background: #f8fafc; border: 2px solid #e5e7eb; padding: 20px;
                            border-radius: 15px; border: 3px solid #2778E5;">
                    <h3 style="color: #2778E5; margin: 0 0 15px 0; font-size: 18px;
                               border-bottom: 2px solid #EE8100; padding-bottom: 8px;">💰 المعلومات المالية</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <p style="margin: 0; color: #374151;"><strong>الرسوم الشهرية:</strong></p>
                        <p style="margin: 0; color: #374151;"><strong>تاريخ بدء الدراسة:</strong></p>
                        <p style="margin: 0; color: #374151;"><strong>تاريخ إستحقاق الدفعة الأولى:</strong></p>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 5px;">
                        <p style="margin: 0; color: #374151;">
                           <span style="color: #EE8100; font-weight: bold;">${savedAdmissionData.monthly_fee || '0'} شيكل</span></p>
                        <p style="margin: 0; color: #374151;"> ${savedAdmissionData.study_start_date || 'غير محدد'}</p>
                        <p style="margin: 0; color: #374151;"> ${savedAdmissionData.payment_due_from || 'غير محدد'} - ${savedAdmissionData.payment_due_to || 'غير محدد'}</p>
                    </div>
                </div>

                <!-- Footer -->
                <div style="text-align: center; margin-top: 30px; padding-top: 20px;
                            border-top: 2px solid #EE8100; color: #666; font-size: 12px;">
                    <p style="margin: 0;">تم إنشاء هذا المستند تلقائياً من نظام إدارة طلبات الانتساب</p>
                </div>
            `;

            return div;
        }

        // ==================== دوال الإشعارات ====================

        function showNotification(message, type = 'info', duration = 5000) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white max-w-sm transition-all duration-300 ${getNotificationClass(type)}`;
            notification.style.transform = 'translateX(100%)';
            notification.style.opacity = '0';

            notification.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-1">${message}</div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white transition-colors hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;

            document.body.appendChild(notification);

            // أنيميشن الظهور
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
                notification.style.opacity = '1';
            }, 100);

            // إزالة تلقائية
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

        // ==================== وظائف Approve/Reject ====================

        window.openApproveModal = function(admissionId, studentName) {
            document.getElementById('approve-modal').classList.remove('hidden');
            document.getElementById('approve-form').action = `/admin/admissions/${admissionId}/approve`;
        };

        window.closeApproveModal = function() {
            document.getElementById('approve-modal').classList.add('hidden');
        };

        window.openRejectModal = function(form) {
            window.currentRejectForm = form;
            document.getElementById('reject-modal').classList.remove('hidden');
        };

        window.closeRejectModal = function() {
            document.getElementById('reject-modal').classList.add('hidden');
        };

        window.submitRejectForm = function() {
            if (window.currentRejectForm) {
                window.currentRejectForm.submit();
            }
        };

        // ==================== تقييد الإدخال ====================

        // تقييد إدخال الأرقام فقط لحقول الهوية
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

        // تقييد إدخال أرقام الهاتف
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

        // ==================== منع إغلاق Modal بالنقر خارجها ====================

        const addModal = document.getElementById('add-admission-modal');
        if (addModal) {
            addModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    e.stopPropagation();
                }
            });
        }

        if (successModal) {
            successModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    e.stopPropagation();
                }
            });
        }

        // ==================== تحسينات إضافية ====================

        // البحث السريع (إذا كان موجود)
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    performQuickSearch(this.value);
                }, 300);
            });
        }

        // دالة البحث السريع
        async function performQuickSearch(query) {
            if (query.length < 2) return;

            try {
                const response = await fetch(`/admin/admissions-data/quick-search?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    displaySearchResults(data.results);
                }
            } catch (error) {
                console.warn('خطأ في البحث:', error);
            }
        }

        // عرض نتائج البحث
        function displaySearchResults(results) {
            let searchResults = document.getElementById('search-results');
            if (!searchResults) {
                searchResults = document.createElement('div');
                searchResults.id = 'search-results';
                searchResults.className = 'absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-b-lg shadow-lg max-h-60 overflow-y-auto z-10';
                searchInput.parentElement.appendChild(searchResults);
            }

            if (results.length === 0) {
                searchResults.innerHTML = '<div class="p-3 text-center text-gray-500">لا توجد نتائج</div>';
                return;
            }

            searchResults.innerHTML = results.map(result => `
                <div class="p-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50" onclick="window.location.href='${result.url}'">
                    <div class="font-medium text-gray-900">${result.text}</div>
                    <div class="text-sm text-gray-500">${result.subtitle}</div>
                    <span class="inline-block px-2 py-1 text-xs rounded-full ${getStatusColor(result.status)}">${result.status}</span>
                </div>
            `).join('');
        }

        // ألوان الحالات
        function getStatusColor(status) {
            const colors = {
                'في الانتظار': 'bg-yellow-100 text-yellow-800',
                'مقبول': 'bg-green-100 text-green-800',
                'مرفوض': 'bg-red-100 text-red-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }

        // إخفاء نتائج البحث عند النقر خارجها
        document.addEventListener('click', function(e) {
            const searchResults = document.getElementById('search-results');
            if (searchResults && searchInput && !searchResults.contains(e.target) && !searchInput.contains(e.target)) {
                searchResults.remove();
            }
        });

        // التحقق الفوري من رقم الهوية (إذا كان متاح)
        async function checkIdAvailability(id, fieldName) {
            try {
                const response = await fetch('/admin/admissions-data/check-id-availability', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ id, field: fieldName })
                });

                if (response.ok) {
                    const result = await response.json();
                    const field = document.getElementById(fieldName);

                    if (!result.available) {
                        showFieldError(field, 'رقم الهوية مسجل مسبقاً');
                    } else {
                        field.classList.remove('field-error');
                    }
                }
            } catch (error) {
                console.warn('خطأ في التحقق من رقم الهوية:', error);
            }
        }

        // ربط التحقق الفوري بحقول الهوية
        if (studentIdField) {
            studentIdField.addEventListener('blur', function() {
                const id = this.value.trim();
                if (id.length === 9) {
                    checkIdAvailability(id, 'student_id');
                }
            });
        }

        if (parentIdField) {
            parentIdField.addEventListener('blur', function() {
                const id = this.value.trim();
                if (id.length === 9) {
                    checkIdAvailability(id, 'parent_id');
                }
            });
        }

        // معالجة رسائل النجاح/الخطأ من الخادم
        const successMessage = document.querySelector('[data-success-message]');
        if (successMessage) {
            showNotification(successMessage.dataset.successMessage, 'success');
        }

        const errorMessage = document.querySelector('[data-error-message]');
        if (errorMessage) {
            showNotification(errorMessage.dataset.errorMessage, 'error');
        }

        // تحسين تجربة المستخدم - إظهار معلومات إضافية
        function enhanceUserExperience() {
            // إضافة tooltips للحقول المهمة
            const importantFields = [
                { id: 'student_id', text: 'يجب أن يكون رقم الهوية 9 أرقام' },
                { id: 'father_phone', text: 'يجب أن يبدأ الرقم بـ 05' },
                { id: 'monthly_fee', text: 'أدخل المبلغ بالشيكل' }
            ];

            importantFields.forEach(fieldInfo => {
                const field = document.getElementById(fieldInfo.id);
                if (field) {
                    field.title = fieldInfo.text;
                    field.setAttribute('aria-label', fieldInfo.text);
                }
            });
        }

        // تشغيل تحسينات تجربة المستخدم
        enhanceUserExperience();

        // إعداد اختصارات لوحة المفاتيح
        document.addEventListener('keydown', function(e) {
            // Ctrl + N لفتح نموذج جديد
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                openAddAdmissionModal();
            }

            // Escape لإغلاق النوافذ المنبثقة
            if (e.key === 'Escape') {
                const modals = ['add-admission-modal', 'success-modal', 'approve-modal', 'reject-modal'];
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal && !modal.classList.contains('hidden')) {
                        modal.classList.add('hidden');
                        if (modalId === 'add-admission-modal') {
                            resetForm();
                        }
                    }
                });
            }
        });

        // رسالة ترحيب للمطور (اختياري)
        console.log('%c🎓 نظام إدارة طلبات الانتساب ', 'background: #2778E5; color: #EE8100; font-size: 16px; padding: 8px; border-radius: 4px;');
        console.log('تم تحميل النظام بنجاح! جميع الوظائف متاحة.');

    }); // نهاية DOMContentLoaded
</script> --}}

<script>
    document.addEventListener("DOMContentLoaded", function() {

        // متغيرات عامة
        let savedAdmissionData = {};
        const form = document.getElementById("add-admission-form");
        const successModal = document.getElementById("success-modal");

        // متغيرات خاصة بـ validation رقم الطلب
        let applicationValidationTimeout;
        let isCheckingApplicationNumber = false;

        // إخفاء رسائل الخطأ افتراضياً
        document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');

        // ==================== وظائف إدارة Modal ====================

        // فتح modal إضافة طلب جديد
        window.openAddAdmissionModal = function() {
            document.getElementById('add-admission-modal').classList.remove('hidden');
        };

        // إغلاق modal إضافة طلب جديد
        window.closeAddAdmissionModal = function() {
            document.getElementById('add-admission-modal').classList.add('hidden');
            resetForm();
        };

        // إعادة تعيين النموذج
        function resetForm() {
            if (form) {
                form.reset();
            }
            clearAllErrors();
            clearApplicationNumberValidation();
        }

        // إزالة جميع الأخطاء
        function clearAllErrors() {
            document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.field-error, .pending-error').forEach(field => {
                field.classList.remove('field-error', 'pending-error');
                field.removeAttribute('data-error-message');
            });
            
            // إزالة أنماط validation رقم الطلب
            const appNumberField = document.getElementById('application_number');
            if (appNumberField) {
                appNumberField.classList.remove('valid', 'invalid');
            }
        }

        // ==================== وظائف validation رقم الطلب ====================

        function clearApplicationNumberValidation() {
            const appNumberField = document.getElementById('application_number');
            if (appNumberField) {
                appNumberField.classList.remove('valid', 'invalid');
                hideApplicationNumberMessages();
            }
        }

        function handleApplicationNumberInput(e) {
            let value = e.target.value;

            // السماح بالأرقام فقط
            value = value.replace(/[^0-9]/g, '');

            // تحديد الطول بـ 4 أرقام
            if (value.length > 4) {
                value = value.substring(0, 4);
            }

            e.target.value = value;

            // إخفاء جميع الرسائل أثناء الكتابة
            hideApplicationNumberMessages();

            // إزالة التنسيق السابق
            e.target.classList.remove('valid', 'invalid');

            // إلغاء التحقق السابق والبدء بتحقق جديد
            clearTimeout(applicationValidationTimeout);

            if (value.length === 4) {
                applicationValidationTimeout = setTimeout(() => {
                    validateApplicationNumber(value);
                }, 800); // انتظار 0.8 ثانية بعد التوقف عن الكتابة
            }
        }

        function handleApplicationNumberBlur(e) {
            let value = e.target.value;

            if (value.length > 0 && value.length < 4) {
                // تعبئة بالأصفار من البداية عند فقدان التركيز
                value = value.padStart(4, '0');
                e.target.value = value;
            }

            if (value.length === 4) {
                validateApplicationNumber(value);
            }
        }

        function handleApplicationNumberKeyPress(e) {
            // السماح بالأرقام والمفاتيح الخاصة فقط
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
            isCheckingApplicationNumber = true;
            hideApplicationNumberMessages();
            showCheckingMessage();

            // التحقق من النطاق المسموح (0000-1000)
            const numValue = parseInt(number);
            if (numValue > 1000) {
                showApplicationNumberError('رقم الطلب يجب أن يكون بين 0000 و 1000');
                appNumberField.classList.add('invalid');
                appNumberField.classList.remove('valid');
                isCheckingApplicationNumber = false;
                return;
            }

            // التحقق من التكرار في قاعدة البيانات
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
        // دالة التحقق من قاعدة البيانات
        async function checkApplicationNumberInDatabase(number) {
            try {
                const response = await fetch("{{ route('admin.admissions.check-application-number') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
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
                throw error;
            }
        }

        function showApplicationNumberError(message) {
            hideApplicationNumberMessages();
            const appNumberField = document.getElementById('application_number');
            const errorDiv = appNumberField?.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('error-message')) {
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
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

        function validateApplicationNumberField() {
            const field = document.getElementById('application_number');
            const value = field ? field.value.trim() : '';
            
            console.log(`✅ رقم الطلب: "${value}" - تم تجاهل التحقق`);
            return true; // دائماً يرجع true
        }

        async function validateStudentNameWithDuplication() {
            const field = document.getElementById('student_name');
            const name = field.value.trim();
            const nameParts = name.split(/\s+/).filter(part => part.length > 0);
        
            if (!name) {
                showFieldError(field, 'يرجى إدخال اسم الطالب');
                return false;
            }
        
            if (nameParts.length < 4) { 
                showFieldError(field, 'يرجى إدخال الاسم الرباعي كاملاً (4 أسماء على الأقل)' ); 
                return false;
            } 
            // التحقق من تكرار الاسم في قاعدة البيانات 
            try { 
                const parentName=document.getElementById('parent_name').value.trim(); 
                if (parentName) { 
                    const duplicationCheck=await checkNameDuplication(name, parentName); 
                    if (!duplicationCheck.available) {
                        showFieldError(field, 'يوجد طالب بنفس الاسم واسم ولي الأمر مسجل مسبقاً' , true); 
                        return false; 
                    } 
                } 
            } catch (error) {
                console.warn('لم يتم التحقق من تكرار الاسم:', error); 
            // نتابع بدون توقف في حالة الخطأ 
            } 
            return true; 
        }


        // 3. التحقق من تكرار الأسماء في قاعدة البيانات
        async function checkNameDuplication(studentName, parentName) {
            try {
                const response = await fetch('/admin/admissions/check-name-duplication', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        student_name: studentName,
                        parent_name: parentName 
                    })
                });

                if (!response.ok) {
                    throw new Error('فشل في الاتصال بالخادم');
                }

                const data = await response.json();
                return data;
            } catch (error) {
                console.error('خطأ في التحقق من تكرار الأسماء:', error);
                throw error;
            }
        }



        // ==================== وظائف التحقق الأساسية ====================

        // التحقق من صحة النموذج
        async function validateForm() {
            let isValid = true;
            clearAllErrors();
            
            console.log('🔍 بدء عملية التحقق من النموذج...');
            
            // دالة مساعدة للتحقق من الحقول العادية (بدون إظهار فوري للأخطاء)
            function checkField(fieldId, errorMessage) {
            const field = document.getElementById(fieldId);
            if (!field) {
            console.error(`❌ الحقل غير موجود: ${fieldId}`);
            return false;
            }
            
            if (!field.value.trim()) {
            console.log(`❌ الحقل فارغ: ${fieldId}`);
            showFieldError(field, errorMessage); // لا نظهر الخطأ فوراً
            return false;
            }
            
            console.log(`✅ الحقل صحيح: ${fieldId} = "${field.value}"`);
            return true;
            }
            
            // التحقق من الحقول الأساسية
            isValid = checkField('day', 'يرجى اختيار اليوم') && isValid;
            isValid = checkField('application_date', 'يرجى اختيار تاريخ تقديم الطلب') && isValid;
            
            // التحقق من رقم الطلب
            const appNumberValid = validateApplicationNumberField();
            isValid = appNumberValid && isValid;
            
            // التحقق من اسم الطالب مع فحص التكرار
            const studentNameValid = await validateStudentNameWithDuplication();
            isValid = studentNameValid && isValid;
            
            // باقي عمليات التحقق (بدون تغيير)
            const studentIdValid = validateStudentId();
            isValid = studentIdValid && isValid;
            
            isValid = checkField('birth_date', 'يرجى اختيار تاريخ الميلاد') && isValid;
            isValid = checkField('grade', 'يرجى اختيار المرحلة الدراسية') && isValid;
            isValid = checkField('academic_level', 'يرجى اختيار المستوى الأكاديمي') && isValid;
            
            const parentNameValid = validateParentName();
            isValid = parentNameValid && isValid;
            
            const parentIdValid = validateParentId();
            isValid = parentIdValid && isValid;
            
            isValid = checkField('parent_job', 'يرجى إدخال مهنة ولي الأمر') && isValid;
            
            const fatherPhoneValid = validatePhone('father_phone', 'يرجى إدخال رقم جوال الأب صحيح');
            isValid = fatherPhoneValid && isValid;
            
            const motherPhoneValid = validatePhoneOptional('mother_phone');
            isValid = motherPhoneValid && isValid;
            
            isValid = checkField('address', 'يرجى إدخال عنوان السكن') && isValid;
            isValid = checkField('monthly_fee', 'يرجى إدخال المبلغ المدفوع') && isValid;
            isValid = checkField('study_start_date', 'يرجى اختيار تاريخ بدء الدراسة') && isValid;
            isValid = checkField('payment_due_from', 'يرجى تحديد تاريخ بداية استحقاق الدفعة') && isValid;
            isValid = checkField('payment_due_to', 'يرجى تحديد تاريخ نهاية استحقاق الدفعة') && isValid;
            
            // إظهار جميع الأخطاء إذا فشل التحقق
            if (!isValid) {
            showAllPendingErrors();
            }
            
            console.log(`🏁 النتيجة النهائية للتحقق: ${isValid ? '✅ نجح' : '❌ فشل'}`);
            return isValid;
        }

        // التحقق من حقل عام
        function validateField(fieldId, errorMessage) {
            const field = document.getElementById(fieldId);
            if (!field || !field.value.trim()) {
                if (field) showFieldError(field, errorMessage);
                return false;
            }
            return true;
        }

        // التحقق من اسم الطالب الرباعي
        function validateStudentName() {
            const field = document.getElementById('student_name');
            const name = field.value.trim();
            const nameParts = name.split(/\s+/).filter(part => part.length > 0);

            if (!name) {
                showFieldError(field, 'يرجى إدخال اسم الطالب');
                return false;
            }

            if (nameParts.length < 4) {
                showFieldError(field, 'يرجى إدخال الاسم الرباعي كاملاً (4 أسماء على الأقل)');
                return false;
            }

            return true;
        }

        // التحقق من اسم ولي الأمر الثلاثي
        function validateParentName() {
            const field = document.getElementById('parent_name');
            const name = field.value.trim();
            const nameParts = name.split(/\s+/).filter(part => part.length > 0);

            if (!name) {
                showFieldError(field, 'يرجى إدخال اسم ولي الأمر');
                return false;
            }

            if (nameParts.length < 3) {
                showFieldError(field, 'يرجى إدخال الاسم الثلاثي كاملاً (3 أسماء على الأقل)');
                return false;
            }

            return true;
        }

        // التحقق من رقم هوية الطالب
        function validateStudentId() {
            const field = document.getElementById('student_id');
            const id = field.value.trim();

            if (!id) {
                showFieldError(field, 'يرجى إدخال رقم الهوية');
                return false;
            }

            if (!/^\d{9}$/.test(id)) {
                showFieldError(field, 'رقم الهوية يجب أن يكون 9 أرقام فقط');
                return false;
            }

            return true;
        }

        // التحقق من رقم هوية ولي الأمر
        function validateParentId() {
            const field = document.getElementById('parent_id');
            const id = field.value.trim();

            if (!id) {
                showFieldError(field, 'يرجى إدخال رقم هوية ولي الأمر');
                return false;
            }

            if (!/^\d{9}$/.test(id)) {
                showFieldError(field, 'رقم الهوية يجب أن يكون 9 أرقام فقط');
                return false;
            }

            return true;
        }

        // التحقق من رقم الهاتف
        function validatePhone(fieldId, errorMessage) {
            const field = document.getElementById(fieldId);
            const phone = field.value.trim();

            if (!phone) {
                showFieldError(field, errorMessage);
                return false;
            }

            if (!/^05\d{8}$/.test(phone)) {
                showFieldError(field, 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام');
                return false;
            }

            return true;
        }

        // التحقق من رقم الهاتف الاختياري
        function validatePhoneOptional(fieldId) {
            const field = document.getElementById(fieldId);
            if (!field) {
                console.warn(`⚠️ الحقل غير موجود: ${fieldId}`);
                return true; // إذا لم يكن الحقل موجود، اعتبره صحيح
            }

            const phone = field.value.trim();

            // إذا كان فارغ، فهو مقبول (لأنه اختياري)
            if (!phone) {
                return true;
            }

            if (!/^05\d{8}$/.test(phone)) {
                showFieldError(field, 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام');
                return false;
            }

            return true;
        }

        // 1. تحسين إظهار رسائل الخطأ عند الضغط على Submit فقط
        function showFieldError(field, message, showImmediately = false) {
            if (showImmediately) {
                field.classList.add('field-error');
                const errorDiv = field.nextElementSibling;
                if (errorDiv && errorDiv.classList.contains('error-message')) {
                    errorDiv.textContent = message;
                    errorDiv.style.display = 'block';
                }
            } else {
                // تخزين رسالة الخطأ لإظهارها لاحقاً
                field.setAttribute('data-error-message', message);
                field.classList.add('pending-error');
            }
        }

        function showAllPendingErrors() {
            const fieldsWithErrors = document.querySelectorAll('.pending-error');
            fieldsWithErrors.forEach(field => {
                const errorMessage = field.getAttribute('data-error-message');
                if (errorMessage) {
                    field.classList.add('field-error');
                    field.classList.remove('pending-error');
                    const errorDiv = field.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('error-message')) {
                        errorDiv.textContent = errorMessage;
                        errorDiv.style.display = 'block';
                    }
                }
            });
        }

        // ==================== تهيئة التقويم ====================

        // تفعيل flatpickr للحقول التي نوعها date
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

        // ==================== معالجة إرسال النموذج ====================

        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // التحقق من النموذج (الآن async)
                const isValid = await validateForm();
                
                if (!isValid) {
                    showNotification('يرجى تصحيح الأخطاء في النموذج قبل الإرسال', 'error');
                    return;
                }
                
                // باقي كود الإرسال يبقى كما هو...
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'جاري الحفظ...';
                submitBtn.disabled = true;
                
                try {
                    const formData = new FormData();
                    const allFields = [
                        'day', 'application_date', 'application_number', 'student_name',
                        'student_id', 'birth_date', 'grade', 'academic_level',
                        'parent_name', 'parent_id', 'parent_job', 'father_phone',
                        'mother_phone', 'address', 'monthly_fee', 'study_start_date',
                        'payment_due_from', 'payment_due_to'
                    ];
                
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content);
                
                savedAdmissionData = {};
                allFields.forEach(fieldName => {
                    const element = document.getElementById(fieldName);
                    if (element) {
                        formData.append(fieldName, element.value || '');
                        savedAdmissionData[fieldName] = element.value || '';
                    }
                });
                
                const response = await fetch('/admin/admissions', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    showNotification('تم حفظ البيانات بنجاح!', 'success');
                    closeAddAdmissionModal();
                    showSuccessModal();
                    setTimeout(() => location.reload(), 2000);
                } else {
                    throw new Error('فشل في الإرسال');
                }
                
                } catch (error) {
                    showNotification('خطأ في حفظ البيانات', 'error');
                } finally {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            });
        }

        // ==================== إظهار نتيجة النجاح ====================

        function showSuccessModal() {
            if (successModal) {
                successModal.classList.remove('hidden');

                // إخفاء تلقائي بعد 5 ثوانِ
                setTimeout(() => {
                    successModal.classList.add('hidden');
                }, 5000);
            }
        }

        // ==================== وظائف التصدير ====================

        // تصدير كصورة محسن
        window.exportAsImage = async function() {
            try {
                showNotification('جاري إنشاء الصورة...', 'info', 2000);

                const dataElement = createDataDisplay();
                document.body.appendChild(dataElement);

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

                // إغلاق modal النجاح
                setTimeout(() => {
                    successModal.classList.add('hidden');
                }, 1000);

            } catch (error) {
                console.error('خطأ في تصدير الصورة:', error);
                showNotification('خطأ في تصدير الصورة', 'error');
            }
        };

        // تصدير PDF محسن
        window.exportAsPDF = function() {
            try {
                showNotification('جاري إنشاء ملف PDF...', 'info', 2000);

                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();

                // إعدادات الخط والألوان
                doc.setFont('helvetica');
                doc.setFontSize(16);

                // عنوان الوثيقة
                doc.text('طلب انتساب جديد', 105, 20, { align: 'center' });

                let yPos = 40;
                const lineHeight = 10;

                // بيانات الطالب
                doc.setFontSize(14);
                doc.text('بيانات الطالب:', 20, yPos);
                yPos += lineHeight;

                doc.setFontSize(12);
                doc.text(`الاسم: ${savedAdmissionData.student_name || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight;
                doc.text(`رقم الهوية: ${savedAdmissionData.student_id || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight;
                doc.text(`المرحلة الدراسية: ${savedAdmissionData.grade || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight * 2;

                // بيانات ولي الأمر
                doc.setFontSize(14);
                doc.text('بيانات ولي الأمر:', 20, yPos);
                yPos += lineHeight;

                doc.setFontSize(12);
                doc.text(`الاسم: ${savedAdmissionData.parent_name || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight;
                doc.text(`المهنة: ${savedAdmissionData.parent_job || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight;
                doc.text(`رقم الجوال: ${savedAdmissionData.father_phone || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight * 2;

                // المعلومات المالية
                doc.setFontSize(14);
                doc.text('المعلومات المالية:', 20, yPos);
                yPos += lineHeight;

                doc.setFontSize(12);
                doc.text(`المبلغ المدفوع: ${savedAdmissionData.monthly_fee || '0'} شيكل`, 25, yPos);
                yPos += lineHeight;
                doc.text(`تاريخ بدء الدراسة: ${savedAdmissionData.study_start_date || 'غير محدد'}`, 25, yPos);

                // تاريخ الإنشاء
                doc.setFontSize(10);
                doc.text(`تاريخ الإنشاء: ${new Date().toLocaleDateString('ar-PS')}`, 20, 280);

                const fileName = `طلب_انتساب_${savedAdmissionData.student_name || 'جديد'}_${Date.now()}.pdf`;
                doc.save(fileName);

                showNotification('تم تصدير ملف PDF بنجاح!', 'success');

                // إغلاق modal النجاح
                setTimeout(() => {
                    successModal.classList.add('hidden');
                }, 1000);

            } catch (error) {
                console.error('خطأ في تصدير PDF:', error);
                showNotification('خطأ في تصدير ملف PDF', 'error');
            }
        };

        // إنشاء عرض البيانات للتصدير (محسن)
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
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #EE8100;">
                    <h1 style="color: #2778E5; font-size: 32px; margin: 0; font-weight: bold;">
                        🎓 نموذج انتساب جديد
                    </h1>
                    <p style="color: #666; margin: 10px 0 0 0; font-size: 14px;">
                        تاريخ الإصدار: ${new Date().toLocaleDateString('ar-PS')}
                    </p>
                </div>

                <!-- بيانات الطلب -->
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

                <!-- بيانات الطالب -->
                <div style="background: #f8fafc; border: 2px solid #e5e7eb; padding: 20px;
                            margin-bottom: 20px; border-radius: 15px; border-right: 5px solid #2778E5;">
                    <h3 style="color: #2778E5; margin: 0 0 15px 0; font-size: 18px;
                               border-bottom: 2px solid #EE8100; padding-bottom: 8px;">👨‍🎓 بيانات الطالب</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <p style="margin: 0 0 10px 0; color: #374151; display:block;"><strong>الاسم:</strong> ${savedAdmissionData.student_name || 'غير محدد'}</p>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>رقم الهوية:</strong> ${savedAdmissionData.student_id || 'غير محدد'}</p>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>تاريخ الميلاد:</strong> ${savedAdmissionData.birth_date || 'غير محدد'}</p>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>المرحلة الدراسية:</strong> ${savedAdmissionData.grade || 'غير محدد'}</p>
                    </div>
                    <p style="margin: 10px 0 0 0; color: #374151;"><strong>المستوى الأكاديمي:</strong>
                       <span style="background: #EE8100; color: white; place-items: center; padding: 12px; margin:12px; border-radius: 8px; font-size: 14px;">
                           ${savedAdmissionData.academic_level || 'غير محدد'}
                       </span>
                    </p>
                </div>

                <!-- بيانات ولي الأمر والتواصل -->
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

                <!-- المعلومات المالية -->
                <div style="background: #f8fafc; border: 2px solid #e5e7eb; padding: 20px;
                            border-radius: 15px; border: 3px solid #2778E5;">
                    <h3 style="color: #2778E5; margin: 0 0 15px 0; font-size: 18px;
                               border-bottom: 2px solid #EE8100; padding-bottom: 8px;">💰 المعلومات المالية</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <p style="margin: 0; color: #374151;"><strong>المبلغ المدفوع:</strong></p>
                        <p style="margin: 0; color: #374151;"><strong>تاريخ بدء الدراسة:</strong></p>
                        <p style="margin: 0; color: #374151;"><strong>تاريخ إستحقاق الدفعة الأولى:</strong></p>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 5px;">
                        <p style="margin: 0; color: #374151;">
                           <span style="color: #EE8100; font-weight: bold;">${savedAdmissionData.monthly_fee || '0'} شيكل</span></p>
                        <p style="margin: 0; color: #374151;"> ${savedAdmissionData.study_start_date || 'غير محدد'}</p>
                        <p style="margin: 0; color: #374151;"> ${savedAdmissionData.payment_due_from || 'غير محدد'} - ${savedAdmissionData.payment_due_to || 'غير محدد'}</p>
                    </div>
                </div>

                <!-- Footer -->
                <div style="text-align: center; margin-top: 30px; padding-top: 20px;
                            border-top: 2px solid #EE8100; color: #666; font-size: 12px;">
                    <p style="margin: 0;">تم إنشاء هذا المستند تلقائياً من نظام إدارة طلبات الانتساب</p>
                </div>
            `;

            return div;
        }

        // ==================== دوال الإشعارات ====================

        function showNotification(message, type = 'info', duration = 5000) {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white max-w-sm transition-all duration-300 ${getNotificationClass(type)}`;
            notification.style.transform = 'translateX(100%)';
            notification.style.opacity = '0';

            notification.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-1">${message}</div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white transition-colors hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;

            document.body.appendChild(notification);

            // أنيميشن الظهور
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
                notification.style.opacity = '1';
            }, 100);

            // إزالة تلقائية
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

        // ==================== وظائف Approve/Reject ====================

        window.openApproveModal = function(admissionId, studentName) {
            document.getElementById('approve-modal').classList.remove('hidden');
            document.getElementById('approve-form').action = `/admin/admissions/${admissionId}/approve`;
        };

        window.closeApproveModal = function() {
            document.getElementById('approve-modal').classList.add('hidden');
        };

        window.openRejectModal = function(form) {
            window.currentRejectForm = form;
            document.getElementById('reject-modal').classList.remove('hidden');
        };

        window.closeRejectModal = function() {
            document.getElementById('reject-modal').classList.add('hidden');
        };

        window.submitRejectForm = function() {
            if (window.currentRejectForm) {
                window.currentRejectForm.submit();
            }
        };

        // ==================== تقييد الإدخال ====================

        // تهيئة حقل رقم الطلب
        const appNumberField = document.getElementById('application_number');
        if (appNumberField) {
            appNumberField.addEventListener('input', handleApplicationNumberInput);
            appNumberField.addEventListener('blur', handleApplicationNumberBlur);
            appNumberField.addEventListener('keypress', handleApplicationNumberKeyPress);
        }

        // تقييد إدخال الأرقام فقط لحقول الهوية
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

        // تقييد إدخال أرقام الهاتف
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

        // ==================== منع إغلاق Modal بالنقر خارجها ====================

        const addModal = document.getElementById('add-admission-modal');
        if (addModal) {
            addModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    e.stopPropagation();
                }
            });
        }

        if (successModal) {
            successModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    e.stopPropagation();
                }
            });
        }

        // ==================== تحسينات إضافية ====================

        // البحث السريع (إذا كان موجود)
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    performQuickSearch(this.value);
                }, 300);
            });
        }

        // دالة البحث السريع
        async function performQuickSearch(query) {
            if (query.length < 2) return;

            try {
                const response = await fetch(`/admin/admissions-data/quick-search?q=${encodeURIComponent(query)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    displaySearchResults(data.results);
                }
            } catch (error) {
                console.warn('خطأ في البحث:', error);
            }
        }

        // عرض نتائج البحث
        function displaySearchResults(results) {
            let searchResults = document.getElementById('search-results');
            if (!searchResults) {
                searchResults = document.createElement('div');
                searchResults.id = 'search-results';
                searchResults.className = 'absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-b-lg shadow-lg max-h-60 overflow-y-auto z-10';
                searchInput.parentElement.appendChild(searchResults);
            }

            if (results.length === 0) {
                searchResults.innerHTML = '<div class="p-3 text-center text-gray-500">لا توجد نتائج</div>';
                return;
            }

            searchResults.innerHTML = results.map(result => `
                <div class="p-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50" onclick="window.location.href='${result.url}'">
                    <div class="font-medium text-gray-900">${result.text}</div>
                    <div class="text-sm text-gray-500">${result.subtitle}</div>
                    <span class="inline-block px-2 py-1 text-xs rounded-full ${getStatusColor(result.status)}">${result.status}</span>
                </div>
            `).join('');
        }

        // ألوان الحالات
        function getStatusColor(status) {
            const colors = {
                'في الانتظار': 'bg-yellow-100 text-yellow-800',
                'مقبول': 'bg-green-100 text-green-800',
                'مرفوض': 'bg-red-100 text-red-800'
            };
            return colors[status] || 'bg-gray-100 text-gray-800';
        }

        // إخفاء نتائج البحث عند النقر خارجها
        document.addEventListener('click', function(e) {
            const searchResults = document.getElementById('search-results');
            if (searchResults && searchInput && !searchResults.contains(e.target) && !searchInput.contains(e.target)) {
                searchResults.remove();
            }
        });

        // التحقق الفوري من رقم الهوية (إذا كان متاح)
        async function checkIdAvailability(id, fieldName) {
            try {
                const response = await fetch('/admin/admissions-data/check-id-availability', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ id, field: fieldName })
                });

                if (response.ok) {
                    const result = await response.json();
                    const field = document.getElementById(fieldName);

                    if (!result.available) {
                        showFieldError(field, 'رقم الهوية مسجل مسبقاً');
                    } else {
                        field.classList.remove('field-error');
                    }
                }
            } catch (error) {
                console.warn('خطأ في التحقق من رقم الهوية:', error);
            }
        }

        // ربط التحقق الفوري بحقول الهوية
        if (studentIdField) {
            studentIdField.addEventListener('blur', function() {
                const id = this.value.trim();
                if (id.length === 9) {
                    checkIdAvailability(id, 'student_id');
                }
            });
        }

        if (parentIdField) {
            parentIdField.addEventListener('blur', function() {
                const id = this.value.trim();
                if (id.length === 9) {
                    checkIdAvailability(id, 'parent_id');
                }
            });
        }

        // معالجة رسائل النجاح/الخطأ من الخادم
        const successMessage = document.querySelector('[data-success-message]');
        if (successMessage) {
            showNotification(successMessage.dataset.successMessage, 'success');
        }

        const errorMessage = document.querySelector('[data-error-message]');
        if (errorMessage) {
            showNotification(errorMessage.dataset.errorMessage, 'error');
        }

        // تحسين تجربة المستخدم - إظهار معلومات إضافية
        function enhanceUserExperience() {
            // إضافة tooltips للحقول المهمة
            const importantFields = [
                { id: 'student_id', text: 'يجب أن يكون رقم الهوية 9 أرقام' },
                { id: 'father_phone', text: 'يجب أن يبدأ الرقم بـ 05' },
                { id: 'application_number', text: 'رقم الطلب: 4 أرقام من 0000 إلى 1000' },
                { id: 'monthly_fee', text: 'أدخل المبلغ بالشيكل' }
            ];

            importantFields.forEach(fieldInfo => {
                const field = document.getElementById(fieldInfo.id);
                if (field) {
                    field.title = fieldInfo.text;
                    field.setAttribute('aria-label', fieldInfo.text);
                }
            });
        }

        // تشغيل تحسينات تجربة المستخدم
        enhanceUserExperience();

        // إعداد اختصارات لوحة المفاتيح
        document.addEventListener('keydown', function(e) {
            // Ctrl + N لفتح نموذج جديد
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                openAddAdmissionModal();
            }

            // Escape لإغلاق النوافذ المنبثقة
            if (e.key === 'Escape') {
                const modals = ['add-admission-modal', 'success-modal', 'approve-modal', 'reject-modal'];
                modals.forEach(modalId => {
                    const modal = document.getElementById(modalId);
                    if (modal && !modal.classList.contains('hidden')) {
                        modal.classList.add('hidden');
                        if (modalId === 'add-admission-modal') {
                            resetForm();
                        }
                    }
                });
            }
        });

        // رسالة ترحيب للمطور (اختياري)
        console.log('%c🎓 نظام إدارة طلبات الانتساب ', 'background: #2778E5; color: #EE8100; font-size: 16px; padding: 8px; border-radius: 4px;');
        console.log('تم تحميل النظام بنجاح! جميع الوظائف متاحة.');

    }); // نهاية DOMContentLoaded
</script>

{{-- <script>
    document.addEventListener("DOMContentLoaded", function() {
        // متغيرات عامة
        let savedAdmissionData = {};
        const form = document.getElementById("add-admission-form");
        const successModal = document.getElementById("success-modal");

        // متغيرات خاصة بـ validation رقم الطلب
        let applicationValidationTimeout;
        let isCheckingApplicationNumber = false;

        // إخفاء رسائل الخطأ افتراضياً
        document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');

        // ==================== وظائف إدارة Modal ====================

        // فتح modal إضافة طلب جديد
        window.openAddAdmissionModal = function() {
            document.getElementById('add-admission-modal').classList.remove('hidden');
        };

        // إغلاق modal إضافة طلب جديد
        window.closeAddAdmissionModal = function() {
            document.getElementById('add-admission-modal').classList.add('hidden');
            resetForm();
        };

        // إعادة تعيين النموذج
        function resetForm() {
            if (form) {
                form.reset();
            }
            clearAllErrors();
            clearApplicationNumberValidation();
        }

        // إزالة جميع الأخطاء
        function clearAllErrors() {
            document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
            const errorFields = document.querySelectorAll('.field-error');
            errorFields.forEach(field => {
                field.classList.remove('field-error');
            });

            // إزالة أنماط validation رقم الطلب
            const appNumberField = document.getElementById('application_number');
            if (appNumberField) {
                appNumberField.classList.remove('valid', 'invalid');
            }
        }

        // ==================== وظائف validation رقم الطلب ====================

        function clearApplicationNumberValidation() {
            const appNumberField = document.getElementById('application_number');
            if (appNumberField) {
                appNumberField.classList.remove('valid', 'invalid');
                hideApplicationNumberMessages();
            }
        }

        function handleApplicationNumberInput(e) {
            let value = e.target.value;

            // السماح بالأرقام فقط
            value = value.replace(/[^0-9]/g, '');

            // تحديد الطول بـ 4 أرقام
            if (value.length > 4) {
                value = value.substring(0, 4);
            }

            e.target.value = value;

            // إخفاء جميع الرسائل أثناء الكتابة
            hideApplicationNumberMessages();

            // إزالة التنسيق السابق
            e.target.classList.remove('valid', 'invalid');

            // إلغاء التحقق السابق والبدء بتحقق جديد
            clearTimeout(applicationValidationTimeout);

            if (value.length === 4) {
                applicationValidationTimeout = setTimeout(() => {
                    validateApplicationNumber(value);
                }, 800); // انتظار 0.8 ثانية بعد التوقف عن الكتابة
            }
        }

        function handleApplicationNumberBlur(e) {
            let value = e.target.value;

            if (value.length > 0 && value.length < 4) {
                // تعبئة بالأصفار من البداية عند فقدان التركيز
                value = value.padStart(4, '0');
                e.target.value = value;
            }

            if (value.length === 4) {
                validateApplicationNumber(value);
            }
        }

        function handleApplicationNumberKeyPress(e) {
            // السماح بالأرقام والمفاتيح الخاصة فقط
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
            isCheckingApplicationNumber = true;
            hideApplicationNumberMessages();
            showCheckingMessage();

            // التحقق من النطاق المسموح (0000-1000)
            const numValue = parseInt(number);
            if (numValue > 1000) {
                showApplicationNumberError('رقم الطلب يجب أن يكون بين 0000 و 1000');
                appNumberField.classList.add('invalid');
                isCheckingApplicationNumber = false;
                return;
            }

            // التحقق من التكرار في قاعدة البيانات
            try {
                const isAvailable = await checkApplicationNumberInDatabase(number);

                if (isAvailable) {
                    showApplicationNumberSuccess();
                    appNumberField.classList.remove('invalid');
                    appNumberField.classList.add('valid');
                } else {
                    showApplicationNumberError('رقم الطلب مستخدم مسبقاً، يرجى اختيار رقم آخر');
                    appNumberField.classList.remove('valid');
                    appNumberField.classList.add('invalid');
                }
            } catch (error) {
                showApplicationNumberError('خطأ في التحقق من رقم الطلب، يرجى المحاولة مرة أخرى');
                appNumberField.classList.remove('valid');
                appNumberField.classList.add('invalid');
            }

            isCheckingApplicationNumber = false;
        }

        // دالة التحقق من قاعدة البيانات
        async function checkApplicationNumberInDatabase(number) {
            try {
                // هذا مثال فقط - يجب استبداله بالمسار الصحيح في تطبيقك
                const response = await fetch("/admin/admissions/check-application-number", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
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
                throw error;
            }
        }

        function showApplicationNumberError(message) {
            hideApplicationNumberMessages();
            const appNumberField = document.getElementById('application_number');
            const errorDiv = appNumberField?.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('error-message')) {
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
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
            // إخفاء رسالة الخطأ عند التحقق
            const errorDiv = document.getElementById('application_number')?.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('error-message')) {
                errorDiv.style.display = 'none';
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

    function validateApplicationNumberField() {
        const field = document.getElementById('application_number');
        if (!field) return true;
        const value = field?.value?.trim();
        if (!value) return true;
        // إذا لم يكن هناك قيمة، نتركه فارغ (سيتم توليده تلقائياً)
        if (!value) {
        // إخفاء أي رسائل خطأ قد تظهر
        hideApplicationNumberMessages();
        return true;
        }

        if (value.length !== 4) {
        showFieldError(field, 'رقم الطلب يجب أن يكون 4 أرقام');
        return false;
        }

        const numValue = parseInt(value);
        if (numValue > 1000) {
        showFieldError(field, 'رقم الطلب يجب أن يكون بين 0000 و 1000');
        return false;
        }

        // التحقق من وجود class valid (يعني تم التحقق مسبقاً)
        if (value && !field.classList.contains('valid')) {
        showFieldError(field, 'يرجى التحقق من صحة رقم الطلب أو اتركه فارغاً للتوليد التلقائي');
        return false;
        }

        return true;
    }


        // ==================== وظائف التحقق الأساسية ====================

        // التحقق من صحة النموذج
       function validateForm() {
            let isValid = true;
            clearAllErrors();

            // قائمة الحقول المطلوبة والتحقق منها
            const validations = [
            { field: 'day', message: 'يرجى اختيار اليوم' },
            { field: 'application_date', message: 'يرجى اختيار تاريخ تقديم الطلب' },
            { field: 'application_number', validator: validateApplicationNumberField },
            { field: 'student_name', validator: validateStudentName },
            { field: 'student_id', validator: validateStudentId },
            { field: 'birth_date', message: 'يرجى اختيار تاريخ الميلاد' },
            { field: 'grade', message: 'يرجى اختيار المرحلة الدراسية' },
            { field: 'academic_level', message: 'يرجى اختيار المستوى الأكاديمي' },
            { field: 'parent_name', validator: validateParentName },
            { field: 'parent_id', validator: validateParentId },
            { field: 'parent_job', message: 'يرجى إدخال مهنة ولي الأمر' },
            { field: 'father_phone', validator: () => validatePhone('father_phone', 'يرجى إدخال رقم جوال الأب صحيح') },
            { field: 'mother_phone', validator: validatePhoneOptional },
            { field: 'address', message: 'يرجى إدخال عنوان السكن' },
            { field: 'monthly_fee', message: 'يرجى إدخال المبلغ المدفوع' },
            { field: 'study_start_date', message: 'يرجى اختيار تاريخ بدء الدراسة' },
            { field: 'payment_due_from', message: 'يرجى تحديد تاريخ بداية استحقاق الدفعة' },
            { field: 'payment_due_to', message: 'يرجى تحديد تاريخ نهاية استحقاق الدفعة' }
            ];

            validations.forEach(validation => {
            let result;
            if (validation.validator) {
            result = validation.validator();
            } else {
            result = validateField(validation.field, validation.message);
            }
            if (!result) {
            console.error(`Validation failed for field: ${validation.field}`);
            }
            isValid = result && isValid;
            });

            return isValid;
        }
        // التحقق من حقل عام
        function validateField(fieldId, errorMessage) {
            const field = document.getElementById(fieldId);
            if (!field || !field.value.trim()) {
                if (field) showFieldError(field, errorMessage);
                return false;
            }
            return true;
        }

        // التحقق من اسم الطالب الرباعي
        function validateStudentName() {
            const field = document.getElementById('student_name');
            const name = field.value.trim();
            const nameParts = name.split(/\s+/).filter(part => part.length > 0);

            if (!name) {
                showFieldError(field, 'يرجى إدخال اسم الطالب');
                return false;
            }

            if (nameParts.length < 4) {
                showFieldError(field, 'يرجى إدخال الاسم الرباعي كاملاً (4 أسماء على الأقل)');
                return false;
            }

            return true;
        }

        // التحقق من اسم ولي الأمر الثلاثي
        function validateParentName() {
            const field = document.getElementById('parent_name');
            const name = field.value.trim();
            const nameParts = name.split(/\s+/).filter(part => part.length > 0);

            if (!name) {
                showFieldError(field, 'يرجى إدخال اسم ولي الأمر');
                return false;
            }

            if (nameParts.length < 3) {
                showFieldError(field, 'يرجى إدخال الاسم الثلاثي كاملاً (3 أسماء على الأقل)');
                return false;
            }

            return true;
        }

        // التحقق من رقم هوية الطالب
        function validateStudentId() {
            const field = document.getElementById('student_id');
            const id = field.value.trim();

            if (!id) {
                showFieldError(field, 'يرجى إدخال رقم الهوية');
                return false;
            }

            if (!/^\d{9}$/.test(id)) {
                showFieldError(field, 'رقم الهوية يجب أن يكون 9 أرقام فقط');
                return false;
            }

            return true;
        }

        // التحقق من رقم هوية ولي الأمر
        function validateParentId() {
            const field = document.getElementById('parent_id');
            const id = field.value.trim();

            if (!id) {
                showFieldError(field, 'يرجى إدخال رقم هوية ولي الأمر');
                return false;
            }

            if (!/^\d{9}$/.test(id)) {
                showFieldError(field, 'رقم الهوية يجب أن يكون 9 أرقام فقط');
                return false;
            }

            return true;
        }

        // التحقق من رقم الهاتف
        function validatePhone(fieldId, errorMessage) {
            const field = document.getElementById(fieldId);
            const phone = field.value.trim();

            if (!phone) {
                showFieldError(field, errorMessage);
                return false;
            }

            if (!/^05\d{8}$/.test(phone)) {
                showFieldError(field, 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام');
                return false;
            }

            return true;
        }

        // التحقق من رقم الهاتف الاختياري
        function validatePhoneOptional(fieldId) {
            const field = document.getElementById(fieldId);
            if (!field) return true;
            const phone = field.value.trim();

            if (phone && !/^05\d{8}$/.test(phone)) {
                showFieldError(field, 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام');
                return false;
            }

            return true;
        }

        // إظهار خطأ الحقل
        function showFieldError(field, message) {
            field.classList.add('field-error');
            const errorDiv = field.nextElementSibling;
            if (errorDiv && errorDiv.classList.contains('error-message')) {
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
            }
        }

        // ==================== تهيئة التقويم ====================

        // تفعيل flatpickr للحقول التي نوعها date
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

        // ==================== معالجة إرسال النموذج ====================

        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!validateForm()) {
                    showNotification('يرجى تصحيح الأخطاء في النموذج قبل الإرسال', 'error');
                    return;
                }

                // إظهار مؤشر التحميل
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'جاري الحفظ...';
                submitBtn.disabled = true;

                try {
                    // جمع البيانات
                    const formData = new FormData(form);
                    savedAdmissionData = {};
                    for (let [key, value] of formData.entries()) {
                        savedAdmissionData[key] = value;
                    }

                    // محاولة إرسال للخادم
                    const response = await fetch(form.action || '/admin/admissions', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.success) {
                            showNotification('تم حفظ البيانات بنجاح!', 'success');
                            closeAddAdmissionModal();
                            showSuccessModal();

                            // إعادة تحميل الصفحة بعد فترة
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                            return;
                        } else {
                            throw new Error(result.message || 'فشل في حفظ البيانات');
                        }
                    } else {
                        throw new Error('فشل في الاتصال بالخادم');
                    }

                } catch (error) {
                    console.warn('تم الحفظ محلياً:', error.message);
                    showNotification('تم حفظ البيانات محلياً: ' + error.message, 'warning');

                    // حفظ محلي وإظهار النتيجة
                    closeAddAdmissionModal();
                    showSuccessModal();
                } finally {
                    // إعادة تعيين زر الإرسال
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            });
        }

        // ==================== إظهار نتيجة النجاح ====================

        function showSuccessModal() {
            if (successModal) {
                successModal.classList.remove('hidden');

                // إخفاء تلقائي بعد 5 ثوانِ
                setTimeout(() => {
                    successModal.classList.add('hidden');
                }, 5000);
            }
        }

        // ==================== وظائف التصدير ====================

        // تصدير كصورة محسن
        window.exportAsImage = async function() {
            try {
                showNotification('جاري إنشاء الصورة...', 'info', 2000);

                const dataElement = createDataDisplay();
                document.body.appendChild(dataElement);

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

                // إغلاق modal النجاح
                setTimeout(() => {
                    if (successModal) successModal.classList.add('hidden');
                }, 1000);

            } catch (error) {
                console.error('خطأ في تصدير الصورة:', error);
                showNotification('خطأ في تصدير الصورة', 'error');
            }
        };

        // تصدير PDF محسن
        window.exportAsPDF = function() {
            try {
                showNotification('جاري إنشاء ملف PDF...', 'info', 2000);

                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();

                // إعدادات الخط والألوان
                doc.setFont('helvetica');
                doc.setFontSize(16);

                // عنوان الوثيقة
                doc.text('طلب انتساب جديد', 105, 20, { align: 'center' });

                let yPos = 40;
                const lineHeight = 10;

                // بيانات الطالب
                doc.setFontSize(14);
                doc.text('بيانات الطالب:', 20, yPos);
                yPos += lineHeight;

                doc.setFontSize(12);
                doc.text(`الاسم: ${savedAdmissionData.student_name || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight;
                doc.text(`رقم الهوية: ${savedAdmissionData.student_id || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight;
                doc.text(`المرحلة الدراسية: ${savedAdmissionData.grade || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight * 2;

                // بيانات ولي الأمر
                doc.setFontSize(14);
                doc.text('بيانات ولي الأمر:', 20, yPos);
                yPos += lineHeight;

                doc.setFontSize(12);
                doc.text(`الاسم: ${savedAdmissionData.parent_name || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight;
                doc.text(`المهنة: ${savedAdmissionData.parent_job || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight;
                doc.text(`رقم الجوال: ${savedAdmissionData.father_phone || 'غير محدد'}`, 25, yPos);
                yPos += lineHeight * 2;

                // المعلومات المالية
                doc.setFontSize(14);
                doc.text('المعلومات المالية:', 20, yPos);
                yPos += lineHeight;

                doc.setFontSize(12);
                doc.text(`المبلغ المدفوع: ${savedAdmissionData.monthly_fee || '0'} شيكل`, 25, yPos);
                yPos += lineHeight;
                doc.text(`تاريخ بدء الدراسة: ${savedAdmissionData.study_start_date || 'غير محدد'}`, 25, yPos);

                // تاريخ الإنشاء
                doc.setFontSize(10);
                doc.text(`تاريخ الإنشاء: ${new Date().toLocaleDateString('ar-PS')}`, 20, 280);

                const fileName = `طلب_انتساب_${savedAdmissionData.student_name || 'جديد'}_${Date.now()}.pdf`;
                doc.save(fileName);

                showNotification('تم تصدير ملف PDF بنجاح!', 'success');

                // إغلاق modal النجاح
                setTimeout(() => {
                    if (successModal) successModal.classList.add('hidden');
                }, 1000);

            } catch (error) {
                console.error('خطأ في تصدير PDF:', error);
                showNotification('خطأ في تصدير ملف PDF', 'error');
            }
        };

        // إنشاء عرض البيانات للتصدير (محسن)
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
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #EE8100;">
                    <h1 style="color: #2778E5; font-size: 32px; margin: 0; font-weight: bold;">
                        🎓 نموذج انتساب جديد
                    </h1>
                    <p style="color: #666; margin: 10px 0 0 0; font-size: 14px;">
                        تاريخ الإصدار: ${new Date().toLocaleDateString('ar-PS')}
                    </p>
                </div>

                <!-- بيانات الطلب -->
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

                <!-- بيانات الطالب -->
                <div style="background: #f8fafc; border: 2px solid #e5e7eb; padding: 20px;
                            margin-bottom: 20px; border-radius: 15px; border-right: 5px solid #2778E5;">
                    <h3 style="color: #2778E5; margin: 0 0 15px 0; font-size: 18px;
                               border-bottom: 2px solid #EE8100; padding-bottom: 8px;">👨‍🎓 بيانات الطالب</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <p style="margin: 0 0 10px 0; color: #374151; display:block;"><strong>الاسم:</strong> ${savedAdmissionData.student_name || 'غير محدد'}</p>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>رقم الهوية:</strong> ${savedAdmissionData.student_id || 'غير محدد'}</p>
                        <p style="margin: 0 0 10px 0; color: 'غير محدد'"><strong>تاريخ الميلاد:</strong> ${savedAdmissionData.birth_date || 'غير محدد'}</p>
                        <p style="margin: 0 0 10px 0; color: #374151;"><strong>المرحلة الدراسية:</strong> ${savedAdmissionData.grade || 'غير محدد'}</p>
                    </div>
                    <p style="margin: 10px 0 0 0; color: #374151;"><strong>المستوى الأكاديمي:</strong>
                       <span style="background: #EE8100; color: white; place-items: center; padding: 12px; margin:12px; border-radius: 8px; font-size: 14px;">
                           ${savedAdmissionData.academic_level || 'غير محدد'}
                       </span>
                    </p>
                </div>

                <!-- بيانات ولي الأمر والتواصل -->
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

                <!-- المعلومات المالية -->
                <div style="background: #f8fafc; border: 2px solid 'غير محدد'; padding: 20px;
                            border-radius: 15px; border: 3px solid #2778E5;">
                    <h3 style="color: #2778E5; margin: 0 0 15px 0; font-size: 18px;
                               border-bottom: 2px solid #EE8100; padding-bottom: 8px;">💰 المعلومات المالية</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        <p style="margin: 0; color: #374151;"><strong>المبلغ المدفوع:</strong></p>
                        <p style="margin: 0; color: #374151;"><strong>تاريخ بدء الدراسة:</strong></p>
                        <p style="margin: 0; color: #374151;"><strong>تاريخ إستحقاق الدفعة الأولى:</strong></p>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 5px;">
                        <p style="margin: 0; color: #374151;">
                           <span style="color: #EE8100; font-weight: bold;">${savedAdmissionData.monthly_fee || '0'} شيكل</span></p>
                        <p style="margin: 0; color: #374151;"> ${savedAdmissionData.study_start_date || 'غير محدد'}</p>
                        <p style="margin: 0; color: #374151;"> ${savedAdmissionData.payment_due_from || 'غير محدد'} - ${savedAdmissionData.payment_due_to || 'غير محدد'}</p>
                    </div>
                </div>

                <!-- Footer -->
                <div style="text-align: center; margin-top: 30px; padding-top: 20px;
                            border-top: 2px solid #EE8100; color: #666; font-size: 12px;">
                    <p style="margin: 0;">تم إنشاء هذا مستند تلقائياً من نظام إدارة طلبات الانتساب</p>
                </div>
            `;

            return div;
        }

        // ==================== دوال الإشعارات ====================

        function showNotification(message, type = 'info', duration = 5000) {
            // إنشاء عنصر الإشعار إذا لم يكن موجوداً
            let notification = document.getElementById('global-notification');
            if (!notification) {
                notification = document.createElement('div');
                notification.id = 'global-notification';
                notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white max-w-sm transition-all duration-300 ${getNotificationClass(type)}`;
                notification.style.transform = 'translateX(100%)';
                notification.style.opacity = '0';
                document.body.appendChild(notification);
            }

            notification.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-1">${message}</div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white transition-colors hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;

            // تحديث الفئة حسب نوع الإشعار
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white max-w-sm transition-all duration-300 ${getNotificationClass(type)}`;

            // إظهار الإشعار
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';

            // إزالة تلقائية
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.transform = 'translateX(100%)';
                    notification.style.opacity = '0';
                    setTimeout(() => {
                        if (notification.parentElement) notification.remove();
                    }, 300);
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

        // ==================== تقييد الإدخال ====================

        // تهيئة حقل رقم الطلب
        const appNumberField = document.getElementById('application_number');
        if (appNumberField) {
            appNumberField.addEventListener('input', handleApplicationNumberInput);
            appNumberField.addEventListener('blur', handleApplicationNumberBlur);
            appNumberField.addEventListener('keypress', handleApplicationNumberKeyPress);
        }

        // تقييد إدخال الأرقام فقط لحقول الهوية
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

        // تقييد إدخال أرقام الهاتف
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

        // ==================== تحسينات إضافية ====================

        // رسالة ترحيب للمطور (اختياري)
        console.log('%c🎓 نظام إدارة طلبات الانتساب ', 'background: #2778E5; color: #EE8100; font-size: 16px; padding: 8px; border-radius: 4px;');
        console.log('تم تحميل النظام بنجاح! جميع الوظائف متاحة.');

    }); // نهاية DOMContentLoaded
</script> --}}





@endpush