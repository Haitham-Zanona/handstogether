@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle = 'البوابة الإدارية';
$pageTitle = 'إعدادات النظام';
$pageDescription = 'إدارة إعدادات الأكاديمية والنظام';
@endphp

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">إعدادات النظام</h1>
            <p class="mt-2 text-gray-600">إدارة إعدادات الأكاديمية والتفضيلات العامة</p>
        </div>
    </div>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-8">
    @csrf

    <!-- Academy Information -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="w-5 h-5 ml-2 text-primary" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                معلومات الأكاديمية
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Academy Name -->
                <div>
                    <label for="academy_name" class="block mb-2 text-sm font-medium text-gray-700">
                        اسم الأكاديمية *
                    </label>
                    <input type="text" id="academy_name" name="academy_name"
                        value="{{ old('academy_name', $academySettings['academy_name']) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary @error('academy_name') border-red-500 @enderror">
                    @error('academy_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Academy Email -->
                <div>
                    <label for="academy_email" class="block mb-2 text-sm font-medium text-gray-700">
                        البريد الإلكتروني الرسمي *
                    </label>
                    <input type="email" id="academy_email" name="academy_email"
                        value="{{ old('academy_email', $academySettings['academy_email']) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary @error('academy_email') border-red-500 @enderror">
                    @error('academy_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Academy Phone -->
                <div>
                    <label for="academy_phone" class="block mb-2 text-sm font-medium text-gray-700">
                        رقم الهاتف الرسمي *
                    </label>
                    <input type="tel" id="academy_phone" name="academy_phone"
                        value="{{ old('academy_phone', $academySettings['academy_phone']) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary @error('academy_phone') border-red-500 @enderror">
                    @error('academy_phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Monthly Fee -->
                <div>
                    <label for="monthly_fee" class="block mb-2 text-sm font-medium text-gray-700">
                        الرسوم الشهرية الافتراضية (ش.ج) *
                    </label>
                    <input type="number" id="monthly_fee" name="monthly_fee"
                        value="{{ old('monthly_fee', $academySettings['monthly_fee']) }}" min="0" step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary @error('monthly_fee') border-red-500 @enderror">
                    @error('monthly_fee')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Academy Address -->
                <div class="md:col-span-2">
                    <label for="academy_address" class="block mb-2 text-sm font-medium text-gray-700">
                        عنوان الأكاديمية
                    </label>
                    <textarea id="academy_address" name="academy_address" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary">{{ old('academy_address', $academySettings['academy_address']) }}</textarea>
                </div>

                <!-- Working Hours -->
                <div class="md:col-span-2">
                    <label for="working_hours" class="block mb-2 text-sm font-medium text-gray-700">
                        ساعات العمل
                    </label>
                    <input type="text" id="working_hours" name="working_hours"
                        value="{{ old('working_hours', $academySettings['working_hours']) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary">
                </div>
            </div>
        </div>
    </div>

    <!-- System Settings -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="w-5 h-5 ml-2 text-primary" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                إعدادات النظام
            </h3>
        </div>
        <div class="p-6">
            <div class="space-y-6">
                <!-- Notifications Settings -->
                <div>
                    <h4 class="mb-4 font-medium text-gray-900 text-md">إعدادات الإشعارات</h4>
                    <div class="space-y-4">
                        <!-- Auto Notifications -->
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">الإشعارات التلقائية</label>
                                <p class="text-sm text-gray-500">إرسال إشعارات تلقائية للأحداث المهمة</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="auto_notifications" value="1" {{
                                    $systemSettings['auto_notifications'] ? 'checked' : '' }} class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                </div>
                            </label>
                        </div>

                        <!-- Email Notifications -->
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">إشعارات البريد الإلكتروني</label>
                                <p class="text-sm text-gray-500">إرسال إشعارات عبر البريد الإلكتروني</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="email_notifications" value="1" {{
                                    $systemSettings['email_notifications'] ? 'checked' : '' }} class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                </div>
                            </label>
                        </div>

                        <!-- SMS Notifications -->
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">إشعارات الرسائل النصية</label>
                                <p class="text-sm text-gray-500">إرسال إشعارات عبر الرسائل النصية (SMS)</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="sms_notifications" value="1" {{
                                    $systemSettings['sms_notifications'] ? 'checked' : '' }} class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Reminder Settings -->
                <div class="pt-6 border-t border-gray-200">
                    <h4 class="mb-4 font-medium text-gray-900 text-md">إعدادات التذكيرات</h4>
                    <div class="space-y-4">
                        <!-- Attendance Reminder -->
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">تذكيرات الحضور</label>
                                <p class="text-sm text-gray-500">إرسال تذكيرات للطلاب بالمحاضرات القادمة</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="attendance_reminder" value="1" {{
                                    $systemSettings['attendance_reminder'] ? 'checked' : '' }} class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                </div>
                            </label>
                        </div>

                        <!-- Payment Reminder -->
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">تذكيرات الدفع</label>
                                <p class="text-sm text-gray-500">إرسال تذكيرات بالمدفوعات المستحقة</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="payment_reminder" value="1" {{
                                    $systemSettings['payment_reminder'] ? 'checked' : '' }} class="sr-only peer">
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="w-5 h-5 ml-2 text-primary" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                إعدادات الأمان
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- Session Timeout -->
                <div>
                    <label for="session_timeout" class="block mb-2 text-sm font-medium text-gray-700">
                        مهلة انتهاء الجلسة (بالدقائق)
                    </label>
                    <select id="session_timeout" name="session_timeout"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary">
                        <option value="30">30 دقيقة</option>
                        <option value="60" selected>60 دقيقة</option>
                        <option value="120">120 دقيقة</option>
                        <option value="240">240 دقيقة</option>
                    </select>
                </div>

                <!-- Max Login Attempts -->
                <div>
                    <label for="max_login_attempts" class="block mb-2 text-sm font-medium text-gray-700">
                        عدد محاولات تسجيل الدخول المسموحة
                    </label>
                    <select id="max_login_attempts" name="max_login_attempts"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary">
                        <option value="3">3 محاولات</option>
                        <option value="5" selected>5 محاولات</option>
                        <option value="10">10 محاولات</option>
                    </select>
                </div>

                <!-- Password Policy -->
                <div class="md:col-span-2">
                    <label class="block mb-2 text-sm font-medium text-gray-700">سياسة كلمات المرور</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="checkbox" id="require_uppercase" name="password_policy[]" value="uppercase"
                                checked class="w-4 h-4 border-gray-300 rounded text-primary focus:ring-primary">
                            <label for="require_uppercase" class="mr-2 text-sm text-gray-700">يجب أن تحتوي على حرف
                                كبير</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="require_numbers" name="password_policy[]" value="numbers" checked
                                class="w-4 h-4 border-gray-300 rounded text-primary focus:ring-primary">
                            <label for="require_numbers" class="mr-2 text-sm text-gray-700">يجب أن تحتوي على
                                أرقام</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="require_symbols" name="password_policy[]" value="symbols"
                                class="w-4 h-4 border-gray-300 rounded text-primary focus:ring-primary">
                            <label for="require_symbols" class="mr-2 text-sm text-gray-700">يجب أن تحتوي على رموز
                                خاصة</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Mode -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="flex items-center text-lg font-semibold text-gray-900">
                <svg class="w-5 h-5 ml-2 text-primary" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                وضع الصيانة
            </h3>
        </div>
        <div class="p-6">
            <div class="p-4 mb-4 border border-yellow-200 rounded-lg bg-yellow-50">
                <div class="flex">
                    <svg class="w-5 h-5 ml-2 text-yellow-600" fill="none" stroke="currentColor" stroke-width="2">
                        <path
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <p class="text-sm text-yellow-800">تفعيل وضع الصيانة سيمنع جميع المستخدمين من الوصول للنظام باستثناء
                        الإداريين</p>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <label class="text-sm font-medium text-gray-700">تفعيل وضع الصيانة</label>
                    <p class="text-sm text-gray-500">إيقاف النظام مؤقتاً للصيانة</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer">
                    <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600">
                    </div>
                </label>
            </div>

            <div class="mt-4">
                <label for="maintenance_message" class="block mb-2 text-sm font-medium text-gray-700">
                    رسالة الصيانة
                </label>
                <textarea id="maintenance_message" name="maintenance_message" rows="3"
                    placeholder="النظام متوقف مؤقتاً للصيانة. نعتذر عن الإزعاج."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary"></textarea>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-between pt-6">
        <button type="button"
            class="px-6 py-2 text-gray-700 transition duration-300 border border-gray-300 rounded-md hover:bg-gray-50"
            onclick="history.back()">
            إلغاء
        </button>
        <div class="space-x-3 space-x-reverse">
            <button type="button"
                class="px-6 py-2 text-white transition duration-300 bg-gray-500 rounded-md hover:bg-gray-600">
                استعادة الإعدادات الافتراضية
            </button>
            <button type="submit"
                class="px-6 py-2 text-white transition duration-300 rounded-md bg-primary hover:bg-blue-700">
                <svg class="inline-block w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 13l4 4L19 7" />
                </svg>
                حفظ الإعدادات
            </button>
        </div>
    </div>
</form>

<!-- Danger Zone -->
<div class="p-6 mt-8 border border-red-200 rounded-lg bg-red-50">
    <h3 class="flex items-center mb-4 text-lg font-semibold text-red-800">
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
        المنطقة الخطرة
    </h3>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="font-medium text-red-800">مسح جميع البيانات</p>
                <p class="text-sm text-red-600">حذف جميع الطلاب والمحاضرات والبيانات (لا يمكن التراجع)</p>
            </div>
            <button type="button" onclick="confirmDataClear()"
                class="px-4 py-2 text-white transition duration-300 bg-red-600 rounded-md hover:bg-red-700">
                مسح جميع البيانات
            </button>
        </div>
        <div class="flex items-center justify-between pt-4 border-t border-red-200">
            <div>
                <p class="font-medium text-red-800">إعادة تعيين النظام</p>
                <p class="text-sm text-red-600">إعادة النظام إلى حالته الأولى مع الاحتفاظ بالمستخدمين الأساسيين</p>
            </div>
            <button type="button" onclick="confirmSystemReset()"
                class="px-4 py-2 text-white transition duration-300 bg-red-600 rounded-md hover:bg-red-700">
                إعادة تعيين النظام
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // تأكيد مسح البيانات
function confirmDataClear() {
    if (confirm('هل أنت متأكد من أنك تريد مسح جميع البيانات؟ هذا الإجراء لا يمكن التراجع عنه!')) {
        if (confirm('تأكيد أخير: سيتم حذف جميع الطلاب والمحاضرات والبيانات نهائياً!')) {
            // إرسال طلب مسح البيانات
            fetch('{{ route("admin.settings.clear-data") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تم مسح جميع البيانات بنجاح');
                    location.reload();
                } else {
                    alert('حدث خطأ أثناء مسح البيانات');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء العملية');
            });
        }
    }
}

// تأكيد إعادة تعيين النظام
function confirmSystemReset() {
    if (confirm('هل أنت متأكد من إعادة تعيين النظام؟ سيتم حذف جميع البيانات عدا المستخدمين الأساسيين.')) {
        if (confirm('تأكيد أخير: سيتم إعادة النظام إلى حالته الأولى!')) {
            // إرسال طلب إعادة التعيين
            fetch('{{ route("admin.settings.reset-system") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('تم إعادة تعيين النظام بنجاح');
                    location.reload();
                } else {
                    alert('حدث خطأ أثناء إعادة التعيين');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء العملية');
            });
        }
    }
}

// التحقق من صحة النموذج قبل الإرسال
document.querySelector('form').addEventListener('submit', function(e) {
    const academyName = document.getElementById('academy_name').value.trim();
    const academyEmail = document.getElementById('academy_email').value.trim();
    const academyPhone = document.getElementById('academy_phone').value.trim();
    const monthlyFee = document.getElementById('monthly_fee').value;

    if (!academyName) {
        alert('يرجى إدخال اسم الأكاديمية');
        e.preventDefault();
        return;
    }

    if (!academyEmail) {
        alert('يرجى إدخال البريد الإلكتروني الرسمي');
        e.preventDefault();
        return;
    }

    if (!academyPhone) {
        alert('يرجى إدخال رقم الهاتف الرسمي');
        e.preventDefault();
        return;
    }

    if (!monthlyFee || monthlyFee < 0) {
        alert('يرجى إدخال قيمة صحيحة للرسوم الشهرية');
        e.preventDefault();
        return;
    }
});

// إظهار رسالة تأكيد عند تفعيل وضع الصيانة
document.querySelector('input[name="maintenance_mode"]').addEventListener('change', function() {
    if (this.checked) {
        if (!confirm('هل أنت متأكد من تفعيل وضع الصيانة؟ سيتم منع جميع المستخدمين من الدخول باستثناء الإداريين.')) {
            this.checked = false;
        }
    }
});

// استعادة الإعدادات الافتراضية
document.querySelector('.bg-gray-500').addEventListener('click', function() {
    if (confirm('هل أنت متأكد من استعادة الإعدادات الافتراضية؟ سيتم فقدان جميع التغييرات الحالية.')) {
        // إعادة تعيين القيم الافتراضية
        document.getElementById('session_timeout').value = '60';
        document.getElementById('max_login_attempts').value = '5';

        // إعادة تعيين checkboxes للإعدادات الافتراضية
        document.querySelector('input[name="auto_notifications"]').checked = true;
        document.querySelector('input[name="email_notifications"]').checked = true;
        document.querySelector('input[name="sms_notifications"]').checked = false;
        document.querySelector('input[name="attendance_reminder"]').checked = true;
        document.querySelector('input[name="payment_reminder"]').checked = true;
        document.querySelector('input[name="maintenance_mode"]').checked = false;

        // إعادة تعيين سياسة كلمات المرور
        document.getElementById('require_uppercase').checked = true;
        document.getElementById('require_numbers').checked = true;
        document.getElementById('require_symbols').checked = false;

        // مسح رسالة الصيانة
        document.getElementById('maintenance_message').value = 'النظام متوقف مؤقتاً للصيانة. نعتذر عن الإزعاج.';

        alert('تم استعادة الإعدادات الافتراضية. لا تنس حفظ التغييرات.');
    }
});

// حفظ المسودة تلقائياً كل دقيقتين
setInterval(function() {
    const formData = new FormData(document.querySelector('form'));
    const data = Object.fromEntries(formData);

    localStorage.setItem('settings_draft', JSON.stringify(data));
}, 120000); // كل دقيقتين

// استعادة المسودة عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    const draft = localStorage.getItem('settings_draft');
    if (draft && confirm('تم العثور على مسودة محفوظة. هل تريد استعادتها؟')) {
        const data = JSON.parse(draft);
        for (const [key, value] of Object.entries(data)) {
            const element = document.querySelector(`[name="${key}"]`);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = value === '1';
                } else {
                    element.value = value;
                }
            }
        }
    }
});
</script>
@endpush

@push('styles')
<style>
    /* تحسينات إضافية للتصميم */
    .toggle-switch {
        transition: all 0.3s ease;
    }

    .toggle-switch:hover {
        transform: scale(1.05);
    }

    /* تأثيرات الأزرار */
    button {
        transition: all 0.3s ease;
    }

    button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* تحسين مظهر الحقول المطلوبة */
    input:required:invalid {
        border-color: #ef4444;
    }

    input:required:valid {
        border-color: #10b981;
    }

    /* تأثيرات التحويم للبطاقات */
    .bg-white:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    /* تحسين مظهر المنطقة الخطرة */
    .bg-red-50 {
        border: 2px dashed #ef4444;
    }

    /* تأثيرات الانتباه للإعدادات المهمة */
    @keyframes pulse-red {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.8;
        }
    }

    .maintenance-mode-active {
        animation: pulse-red 2s infinite;
        background-color: #fef2f2;
        border-color: #ef4444;
    }

    /* تحسين الاستجابة للشاشات الصغيرة */
    @media (max-width: 768px) {
        .md\:col-span-2 {
            grid-column: span 1;
        }

        .flex.items-center.justify-between {
            flex-direction: column;
            align-items: stretch;
            gap: 1rem;
        }

        .space-x-3.space-x-reverse {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
    }
</style>
@endpush
