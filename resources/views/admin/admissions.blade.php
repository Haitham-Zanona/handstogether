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
                                class="text-green-600 hover:text-green-900">
                                قبول
                            </button>
                            <!-- Reject Button -->
                            <form method="POST" action="{{ route('admin.admissions.reject', $admission) }}"
                                class="inline">
                                @csrf
                                <button type="button" class="text-red-600 hover:text-red-900"
                                    onclick="openRejectModal(this.closest('form'))">
                                    رفض
                                </button>
                            </form>
                        </div>
                        @else
                        <span class="text-gray-400">تم المعالجة</span>
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
    <div class="relative max-w-6xl mx-auto bg-white border rounded-lg shadow-lg top-10 h-full">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-900">إضافة طلب انتساب جديد</h3>
            <button onclick="closeAddAdmissionModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto max-h-96">
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
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- FullCalendar + Datepicker (Pikaday) -->
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById("add-admission-form");
        const searchInput = document.getElementById("searchAdmission");
        const successModal = document.getElementById("success-modal");
        const successMessage = document.getElementById("successMessage");
        const closeModal = document.getElementById("closeModal");

        // Hide all error messages by default
        document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');

        // بيانات محلية كـ fallback
        let savedAdmissionData = {};

        // فتح modal إضافة طلب جديد
        window.openAddAdmissionModal = function () {
            document.getElementById('add-admission-modal').classList.remove('hidden');
        }

        // إغلاق modal إضافة طلب جديد
        window.closeAddAdmissionModal = function () {
            document.getElementById('add-admission-modal').classList.add('hidden');
            resetForm();
        }

        window.openApproveModal = function(admissionId, studentName) {
            document.getElementById('approve-modal').classList.remove('hidden');
            document.getElementById('approve-form').action = `/admin/admissions/${admissionId}/approve`;
        }

        window.closeApproveModal = function() {
            document.getElementById('approve-modal').classList.add('hidden');
        }

        window.openRejectModal = function(form) {
            window.currentRejectForm = form;
            document.getElementById('reject-modal').classList.remove('hidden');
        }

        window.closeRejectModal = function() {
            document.getElementById('reject-modal').classList.add('hidden');
        }

        window.submitRejectForm = function() {
            if (window.currentRejectForm) {
                window.currentRejectForm.submit();
            }
        }

        // إعادة تعيين النموذج
        function resetForm() {
            if(form){
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

        // التحقق من الحقول (نفس السابق...)

        // 🔹 تفعيل التقويم على الحقول التي نوعها date
        const dateFields = [
            'application_date',
            'birth_date',
            'study_start_date',
            'payment_due_from',
            'payment_due_to'
        ];

        dateFields.forEach(id => {
            const el = document.getElementById(id);
            if(el){
                flatpickr(el, {
                    dateFormat: "Y-m-d",
                    locale: "ar",
                    altInput: true,
                    altFormat: "d F Y",
                    disableMobile: true,
                    theme: "light",
                });
            }
        });

        /**
         * إرسال الفورم
         */
        if(form){
            form.addEventListener("submit", async (e) => {
                e.preventDefault();

                if (validateForm()) {
                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData.entries());

                    try {
                        const response = await fetch("{{ route('admin.admissions.store') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(data),
                        });

                        if (!response.ok) throw new Error("فشل الاتصال بالسيرفر");

                        const result = await response.json();
                        if(successMessage){
                            successMessage.textContent = result.message || "تم حفظ البيانات بنجاح!";
                        }
                        if(successModal){
                            successModal.classList.remove("hidden");
                        }
                        showNotification("تم حفظ البيانات على السيرفر بنجاح ✅", "success");

                        savedAdmissionData = data;
                        form.reset();
                        closeAddAdmissionModal();
                        location.reload();

                    } catch (error) {
                        // fallback محلي
                        savedAdmissionData = data;
                        if(successMessage){
                            successMessage.textContent = "تم حفظ البيانات محليًا (بدون سيرفر)";
                        }
                        if(successModal){
                            successModal.classList.remove("hidden");
                        }
                        showNotification("تم الحفظ محليًا لعدم توفر السيرفر ⚠️", "error");
                    }
                }
            });
        }

        /**
         * إغلاق المودال
         */
        if(closeModal){
            closeModal.addEventListener("click", () => {
                successModal.classList.add("hidden");
            });
        }

        /**
         * بحث سريع + تصدير كصورة/PDF (نفس الكود السابق)
         */
    });
</script>
@endpush