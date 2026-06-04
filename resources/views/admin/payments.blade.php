@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle    = 'البوابة الإدارية';
$pageTitle       = 'القسم المالي';
$pageDescription = 'إدارة الدفعات والسجلات المالية';
@endphp

@section('content')

<div x-data="paymentsManager()" x-init="init()" class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-end">
        <button @click="openAddModal()"
            class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            إضافة دفعة مخصصة
        </button>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="flex border-b border-gray-200 px-4 overflow-x-auto">
            <button @click="activeTab = 'monthly'"
                class="px-5 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap"
                :class="activeTab === 'monthly' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'">
                الدفعات الشهرية
            </button>
            <button @click="activeTab = 'history'"
                class="px-5 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap"
                :class="activeTab === 'history' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'">
                السجل المالي
            </button>
            <button @click="activeTab = 'communication'; if (!commLoaded) loadCommPayments()"
                class="px-5 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap"
                :class="activeTab === 'communication' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'">
                التواصل المالي
            </button>
            <button @click="activeTab = 'reports'; if (!reportLoaded) loadReport()"
                class="px-5 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap"
                :class="activeTab === 'reports' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'">
                التقارير اليومية
            </button>
        </div>

        {{-- =============== TAB 1: الدفعات الشهرية =============== --}}
        <div x-show="activeTab === 'monthly'">

            {{-- Filters --}}
            <div class="p-4 bg-gray-50 border-b border-gray-100">
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">الشهر</label>
                        <input type="month" x-model="filters.month" @change="loadPayments()"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">المجموعة</label>
                        <select x-model="filters.groupId" @change="loadPayments()"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary min-w-40">
                            <option value="">جميع المجموعات</option>
                            <template x-for="g in groups" :key="g.id">
                                <option :value="g.id" x-text="g.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">الحالة</label>
                        <select x-model="filters.status" @change="loadPayments()"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="all">جميع الحالات</option>
                            <option value="unpaid">غير مدفوع</option>
                            <option value="paid">مدفوع</option>
                            <option value="pending">في الانتظار</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">النوع</label>
                        <select x-model="filters.type" @change="loadPayments()"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="all">جميع الأنواع</option>
                            <option value="monthly">شهري</option>
                            <option value="admission_fee">رسوم انتساب</option>
                            <option value="educational_bundle">حزمة تعليمية</option>
                        </select>
                    </div>
                    <button @click="loadPayments()" :disabled="loading"
                        class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                        <span x-show="!loading">تحديث</span>
                        <span x-show="loading">...</span>
                    </button>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 p-4 bg-gray-50 border-b border-gray-100">
                <div class="bg-white rounded-lg p-3 text-center border border-gray-100 shadow-sm">
                    <p class="text-xl font-bold text-blue-700" x-text="stats.total_expected ?? '—'"></p>
                    <p class="text-xs text-gray-400 mt-0.5">المتوقع (ش.ج)</p>
                </div>
                <div class="bg-white rounded-lg p-3 text-center border border-gray-100 shadow-sm">
                    <p class="text-xl font-bold text-green-700" x-text="stats.total_paid ?? '—'"></p>
                    <p class="text-xs text-gray-400 mt-0.5">المحصّل (ش.ج)</p>
                </div>
                <div class="bg-white rounded-lg p-3 text-center border border-gray-100 shadow-sm">
                    <p class="text-xl font-bold text-orange-600" x-text="stats.remaining ?? '—'"></p>
                    <p class="text-xs text-gray-400 mt-0.5">المتبقي (ش.ج)</p>
                </div>
                <div class="bg-white rounded-lg p-3 text-center border border-gray-100 shadow-sm">
                    <p class="text-xl font-bold text-green-600" x-text="stats.paid_count ?? '—'"></p>
                    <p class="text-xs text-gray-400 mt-0.5">عدد المدفوعات</p>
                </div>
                <div class="bg-white rounded-lg p-3 text-center border border-gray-100 shadow-sm">
                    <p class="text-xl font-bold text-red-600" x-text="(stats.unpaid_count ?? 0) + (stats.overdue_count ?? 0)"></p>
                    <p class="text-xs text-gray-400 mt-0.5">غير مدفوع</p>
                </div>
            </div>

            {{-- Error --}}
            <div x-show="error" class="mx-4 mt-4 px-4 py-3 text-red-700 bg-red-50 border border-red-200 rounded-lg text-sm" x-text="error"></div>

            {{-- Loading --}}
            <div x-show="loading" class="p-10 text-center text-gray-400">
                <svg class="animate-spin h-8 w-8 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                جار التحميل...
            </div>

            {{-- Empty State --}}
            <div x-show="!loading && payments.length === 0" class="p-10 text-center">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-gray-400 text-sm">لا توجد دفعات بالفلاتر المحددة</p>
            </div>

            {{-- Table --}}
            <div x-show="!loading && payments.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الطالب</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ولي الأمر</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الهاتف</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المبلغ</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النوع</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الاستحقاق</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <template x-for="p in payments" :key="p.id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-900" x-text="p.student_name"></td>
                                <td class="px-4 py-3 text-gray-500 text-xs" x-text="p.parent_name || '—'"></td>
                                <td class="px-4 py-3 text-gray-400 text-xs" x-text="p.phone || '—'"></td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-800" x-text="p.amount"></span>
                                    <span class="text-xs text-gray-400 mr-0.5">ش.ج</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs bg-blue-50 text-blue-700"
                                        x-text="formatType(p.type)"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium"
                                        :class="statusClass(p.status, p.is_overdue)"
                                        x-text="statusLabel(p.status, p.is_overdue)">
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400" x-text="p.due_date || '—'"></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <button x-show="p.status !== 'paid'" @click="openPayModal(p)"
                                            title="تسجيل الدفعة"
                                            class="px-2.5 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600 transition">
                                            تسجيل
                                        </button>
                                        <button @click="openEditModal(p)"
                                            title="تعديل"
                                            class="px-2.5 py-1 bg-blue-100 text-blue-700 text-xs rounded hover:bg-blue-200 transition">
                                            تعديل
                                        </button>
                                        <button x-show="p.status !== 'paid'" @click="deletePayment(p)"
                                            title="حذف"
                                            class="px-2.5 py-1 bg-red-50 text-red-500 text-xs rounded hover:bg-red-100 transition">
                                            حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 text-xs text-gray-400">
                    <span x-text="payments.length + ' نتيجة'"></span>
                </div>
            </div>
        </div>

        {{-- =============== TAB 2: السجل المالي =============== --}}
        <div x-show="activeTab === 'history'">

            {{-- Filters --}}
            <div class="p-4 bg-gray-50 border-b border-gray-100">
                <div class="flex flex-wrap items-end gap-3">

                    {{-- Student Search --}}
                    <div class="relative">
                        <label class="block text-xs font-medium text-gray-600 mb-1">الطالب</label>
                        <input type="text" x-model="histStudentSearch" @input="histSearchStudents()"
                            placeholder="ابحث بالاسم أو الهوية..."
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary w-52">
                        <div x-show="histStudentResults.length > 0"
                            class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-44 overflow-y-auto">
                            <template x-for="s in histStudentResults" :key="s.id">
                                <button @click="selectHistStudent(s)" type="button"
                                    class="w-full px-3 py-2 text-right text-sm hover:bg-gray-50 transition flex justify-between items-center border-b border-gray-50 last:border-0">
                                    <span x-text="s.name" class="font-medium text-gray-800"></span>
                                    <span x-text="s.national_id" class="text-xs text-gray-400"></span>
                                </button>
                            </template>
                        </div>
                        <div x-show="histSelectedStudent" class="mt-1 flex items-center gap-1.5">
                            <span class="text-xs text-green-600 font-medium" x-text="histSelectedStudent?.name"></span>
                            <button @click="clearHistStudent()" class="text-xs text-gray-400 hover:text-red-500">✕</button>
                        </div>
                        <span x-show="histSearchingStudents && !histSelectedStudent" class="text-xs text-gray-400 mt-0.5 block">جار البحث...</span>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">من شهر</label>
                        <input type="month" x-model="histFilters.monthFrom"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">إلى شهر</label>
                        <input type="month" x-model="histFilters.monthTo"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">الحالة</label>
                        <select x-model="histFilters.status"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="all">جميع الحالات</option>
                            <option value="unpaid">غير مدفوع</option>
                            <option value="paid">مدفوع</option>
                            <option value="pending">في الانتظار</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">النوع</label>
                        <select x-model="histFilters.type"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="all">جميع الأنواع</option>
                            <option value="monthly">شهري</option>
                            <option value="admission_fee">رسوم انتساب</option>
                            <option value="educational_bundle">حزمة تعليمية</option>
                        </select>
                    </div>
                    <button @click="loadHistory()" :disabled="histLoading"
                        class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                        <span x-show="!histLoading">بحث</span>
                        <span x-show="histLoading">...</span>
                    </button>
                    <a x-show="histPayments.length > 0" :href="histExportUrl()" target="_blank"
                        class="flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        تصدير CSV
                    </a>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div x-show="histPayments.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-3 p-4 bg-gray-50 border-b border-gray-100">
                <div class="bg-white rounded-lg p-3 text-center border border-gray-100 shadow-sm">
                    <p class="text-xl font-bold text-blue-700" x-text="histStats.total_count"></p>
                    <p class="text-xs text-gray-400 mt-0.5">إجمالي السجلات</p>
                </div>
                <div class="bg-white rounded-lg p-3 text-center border border-gray-100 shadow-sm">
                    <p class="text-xl font-bold text-gray-700" x-text="histStats.total_amount"></p>
                    <p class="text-xs text-gray-400 mt-0.5">الإجمالي (ش.ج)</p>
                </div>
                <div class="bg-white rounded-lg p-3 text-center border border-gray-100 shadow-sm">
                    <p class="text-xl font-bold text-green-700" x-text="histStats.paid_amount"></p>
                    <p class="text-xs text-gray-400 mt-0.5">المحصّل (ش.ج)</p>
                </div>
                <div class="bg-white rounded-lg p-3 text-center border border-gray-100 shadow-sm">
                    <p class="text-xl font-bold text-red-600" x-text="histStats.unpaid_amount"></p>
                    <p class="text-xs text-gray-400 mt-0.5">غير مدفوع (ش.ج)</p>
                </div>
            </div>

            {{-- Error --}}
            <div x-show="histError" class="mx-4 mt-4 px-4 py-3 text-red-700 bg-red-50 border border-red-200 rounded-lg text-sm" x-text="histError"></div>

            {{-- Loading --}}
            <div x-show="histLoading" class="p-10 text-center text-gray-400">
                <svg class="animate-spin h-8 w-8 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                جار التحميل...
            </div>

            {{-- Initial prompt --}}
            <div x-show="!histLoading && !histLoaded" class="p-12 text-center">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-gray-400 text-sm">استخدم الفلاتر أعلاه للبحث في السجل المالي</p>
                <p class="text-xs text-gray-300 mt-1">يمكنك البحث بالطالب أو تحديد نطاق زمني أو الحالة</p>
            </div>

            {{-- Empty --}}
            <div x-show="!histLoading && histLoaded && histPayments.length === 0" class="p-10 text-center">
                <p class="text-gray-400 text-sm">لا توجد سجلات بالفلاتر المحددة</p>
            </div>

            {{-- Table --}}
            <div x-show="!histLoading && histPayments.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الطالب</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الشهر</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">النوع</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المبلغ</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">تاريخ الدفع</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">طريقة الدفع</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <template x-for="p in histPayments" :key="p.id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900" x-text="p.student_name"></div>
                                    <div class="text-xs text-gray-400" x-text="p.parent_name || ''"></div>
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs" x-text="formatMonth(p.month)"></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs bg-blue-50 text-blue-700" x-text="formatType(p.type)"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-800" x-text="p.amount"></span>
                                    <span class="text-xs text-gray-400 mr-0.5">ش.ج</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium"
                                        :class="statusClass(p.status, p.is_overdue)"
                                        x-text="statusLabel(p.status, p.is_overdue)">
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400" x-text="p.paid_date || '—'"></td>
                                <td class="px-4 py-3 text-xs text-gray-400" x-text="formatPaymentMethod(p.payment_method)"></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <button x-show="p.status !== 'paid'" @click="openPayModal(p)"
                                            class="px-2.5 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600 transition">
                                            تسجيل
                                        </button>
                                        <button @click="openEditModal(p)"
                                            class="px-2.5 py-1 bg-blue-100 text-blue-700 text-xs rounded hover:bg-blue-200 transition">
                                            تعديل
                                        </button>
                                        <button x-show="p.status !== 'paid'" @click="deletePayment(p)"
                                            class="px-2.5 py-1 bg-red-50 text-red-500 text-xs rounded hover:bg-red-100 transition">
                                            حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 text-xs text-gray-400">
                    <span x-text="histPayments.length + ' سجل'"></span>
                </div>
            </div>
        </div>

        {{-- =============== TAB 3: التواصل المالي =============== --}}
        <div x-show="activeTab === 'communication'">

            {{-- Filters + Bulk Action --}}
            <div class="p-4 bg-gray-50 border-b border-gray-100">
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">الشهر</label>
                        <input type="month" x-model="commFilters.month"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">المجموعة</label>
                        <select x-model="commFilters.groupId"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary min-w-40">
                            <option value="">جميع المجموعات</option>
                            <template x-for="g in groups" :key="g.id">
                                <option :value="g.id" x-text="g.name"></option>
                            </template>
                        </select>
                    </div>
                    <button @click="loadCommPayments()" :disabled="commLoading"
                        class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                        <span x-show="!commLoading">تحميل</span>
                        <span x-show="commLoading">...</span>
                    </button>
                    <button x-show="commPayments.length > 0" @click="sendBulkReminders()" :disabled="commBulkSending"
                        class="flex items-center gap-1.5 px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition disabled:opacity-60">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span x-show="!commBulkSending">إرسال تذكير للجميع</span>
                        <span x-show="commBulkSending">جار الإرسال...</span>
                    </button>
                </div>
            </div>

            {{-- Success Message --}}
            <div x-show="commSuccess" x-transition
                class="mx-4 mt-4 px-4 py-3 text-green-700 bg-green-50 border border-green-200 rounded-lg text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="commSuccess"></span>
            </div>

            {{-- Error --}}
            <div x-show="commError" class="mx-4 mt-4 px-4 py-3 text-red-700 bg-red-50 border border-red-200 rounded-lg text-sm" x-text="commError"></div>

            {{-- Stats --}}
            <div x-show="commPayments.length > 0" class="grid grid-cols-2 gap-3 p-4 bg-gray-50 border-b border-gray-100">
                <div class="bg-white rounded-lg p-3 text-center border border-gray-100 shadow-sm">
                    <p class="text-xl font-bold text-orange-600" x-text="commPayments.length"></p>
                    <p class="text-xs text-gray-400 mt-0.5">دفعة غير مسددة</p>
                </div>
                <div class="bg-white rounded-lg p-3 text-center border border-gray-100 shadow-sm">
                    <p class="text-xl font-bold text-red-600" x-text="commStats.total_amount"></p>
                    <p class="text-xs text-gray-400 mt-0.5">إجمالي غير مسدد (ش.ج)</p>
                </div>
            </div>

            {{-- Loading --}}
            <div x-show="commLoading" class="p-10 text-center text-gray-400">
                <svg class="animate-spin h-8 w-8 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                جار التحميل...
            </div>

            {{-- Empty --}}
            <div x-show="!commLoading && commLoaded && commPayments.length === 0" class="p-10 text-center">
                <svg class="w-12 h-12 mx-auto mb-3 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-gray-400 text-sm">لا توجد دفعات غير مسددة للشهر المحدد</p>
            </div>

            {{-- Table --}}
            <div x-show="!commLoading && commPayments.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الطالب</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ولي الأمر</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الهاتف</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الشهر</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المبلغ</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الحالة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الدفع خلال / تذكير</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <template x-for="p in commPayments" :key="p.id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-900" x-text="p.student_name"></td>
                                <td class="px-4 py-3 text-gray-500 text-xs" x-text="p.parent_name || '—'"></td>
                                <td class="px-4 py-3 text-gray-400 text-xs" x-text="p.phone || '—'"></td>
                                <td class="px-4 py-3 text-gray-500 text-xs" x-text="formatMonth(p.month)"></td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-800" x-text="p.amount"></span>
                                    <span class="text-xs text-gray-400 mr-0.5">ش.ج</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium"
                                        :class="statusClass(p.status, p.is_overdue)"
                                        x-text="statusLabel(p.status, p.is_overdue)">
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <select x-model.number="p.graceDays"
                                            class="border border-gray-200 rounded px-1.5 py-1 text-xs text-gray-600 focus:outline-none focus:ring-1 focus:ring-orange-400 bg-white">
                                            <option :value="0">فوري</option>
                                            <option :value="1">يوم</option>
                                            <option :value="2">يومين</option>
                                            <option :value="3">3 أيام</option>
                                            <option :value="4">4 أيام</option>
                                        </select>
                                        <button @click="sendReminder(p)"
                                            :disabled="commSendingId === p.id"
                                            class="flex items-center gap-1 px-3 py-1.5 bg-orange-500 text-white text-xs rounded-lg hover:bg-orange-600 transition disabled:opacity-50">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                            </svg>
                                            <span x-show="commSendingId !== p.id">إرسال</span>
                                            <span x-show="commSendingId === p.id">...</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 text-xs text-gray-400">
                    <span x-text="commPayments.length + ' دفعة غير مسددة'"></span>
                </div>
            </div>
        </div>

        {{-- =============== TAB 4: التقارير والمالية =============== --}}
        <div x-show="activeTab === 'reports'" class="space-y-0">

            {{-- Sub-tabs --}}
            <div class="flex border-b border-gray-200 px-4 overflow-x-auto bg-gray-50">
                <button @click="reportSubTab = 'revenue'; if(!reportLoaded) loadReport()"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap"
                    :class="reportSubTab === 'revenue' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'">
                    الإيرادات
                </button>
                <button @click="reportSubTab = 'expenses'; loadExpenses()"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap"
                    :class="reportSubTab === 'expenses' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                    المصروفات
                </button>
                <button @click="reportSubTab = 'salaries'; loadSalaryData()"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap"
                    :class="reportSubTab === 'salaries' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                    الرواتب
                </button>
                <button @click="reportSubTab = 'net'; if(!reportLoaded) loadReport()"
                    class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap"
                    :class="reportSubTab === 'net' ? 'border-teal-500 text-teal-600' : 'border-transparent text-gray-500 hover:text-gray-700'">
                    الصافي
                </button>
            </div>

            {{-- ── SUB-TAB: الإيرادات ── --}}
            <div x-show="reportSubTab === 'revenue'" class="p-4 space-y-5">
                <div class="flex justify-end">
                    <button @click="loadReport()" :disabled="reportLoading"
                        class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-600 text-xs rounded-lg hover:bg-gray-200 transition disabled:opacity-60">
                        <svg class="w-3.5 h-3.5" :class="reportLoading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        تحديث
                    </button>
                </div>
                <div x-show="reportLoading" class="p-10 text-center text-gray-400">
                    <svg class="animate-spin h-8 w-8 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
                <div x-show="reportError" class="px-4 py-3 text-red-700 bg-red-50 border border-red-200 rounded-lg text-sm" x-text="reportError"></div>
                <template x-if="reportData && !reportLoading">
                    <div class="space-y-5">
                        {{-- Cards --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                                <span class="text-xs text-gray-400">محصّل اليوم</span>
                                <p class="text-2xl font-bold text-green-700 mt-1" x-text="formatNum(reportData.today_revenue)"></p>
                                <p class="text-xs text-gray-400">ش.ج</p>
                            </div>
                            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                                <span class="text-xs text-gray-400">محصّل هذا الشهر</span>
                                <p class="text-2xl font-bold text-blue-700 mt-1" x-text="formatNum(reportData.month_revenue)"></p>
                                <p class="text-xs text-gray-400">ش.ج</p>
                            </div>
                            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                                <span class="text-xs text-gray-400">غير مسدد هذا الشهر</span>
                                <p class="text-2xl font-bold text-yellow-700 mt-1" x-text="formatNum(reportData.unpaid_this_month)"></p>
                                <p class="text-xs text-gray-400">ش.ج</p>
                            </div>
                            <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                                <span class="text-xs text-gray-400">ديون متراكمة</span>
                                <p class="text-2xl font-bold text-red-700 mt-1" x-text="formatNum(reportData.overdue_amount)"></p>
                                <p class="text-xs text-gray-400">ش.ج — <span x-text="reportData.overdue_count"></span> دفعة</p>
                            </div>
                        </div>
                        {{-- Trend + By Type --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-4">الإيرادات — آخر 6 أشهر</h3>
                                <div class="space-y-2.5">
                                    <template x-for="(row, i) in reportData.monthly_trend" :key="i">
                                        <div>
                                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                                <span x-text="row.label"></span>
                                                <span class="font-medium text-gray-700" x-text="formatNum(row.amount) + ' ش.ج'"></span>
                                            </div>
                                            <div class="w-full bg-gray-100 rounded-full h-2">
                                                <div class="bg-primary rounded-full h-2 transition-all duration-500"
                                                    :style="'width:' + trendBarWidth(row.amount) + '%'"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-4">توزيع المحصّل هذا الشهر حسب النوع</h3>
                                <div x-show="!reportData.by_type.length" class="text-center py-6 text-gray-300 text-sm">لا توجد مدفوعات</div>
                                <div class="space-y-3">
                                    <template x-for="(t, i) in reportData.by_type" :key="i">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2 h-2 rounded-full bg-primary shrink-0"></div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between text-xs mb-1">
                                                    <span class="text-gray-600" x-text="t.label"></span>
                                                    <span class="font-medium text-gray-700" x-text="t.count + ' دفعة — ' + formatNum(t.total) + ' ش.ج'"></span>
                                                </div>
                                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                                    <div class="bg-blue-500 rounded-full h-1.5" :style="'width:' + typeBarWidth(t.total) + '%'"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        {{-- Monthly History Table --}}
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-700">ملخص الشهور — آخر 12 شهراً</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">الشهر</th>
                                            <th class="px-4 py-2.5 text-right text-xs font-medium text-green-600">محصّل</th>
                                            <th class="px-4 py-2.5 text-right text-xs font-medium text-yellow-600">غير مسدد</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <template x-for="(row, i) in reportData.monthly_history" :key="i">
                                            <tr class="hover:bg-gray-50 transition-colors"
                                                :class="row.month === new Date().toISOString().slice(0,7) ? 'bg-blue-50/50' : ''">
                                                <td class="px-4 py-2.5 font-medium text-gray-700" x-text="row.label"></td>
                                                <td class="px-4 py-2.5 text-green-700 font-medium" x-text="formatNum(row.revenue) + ' ش.ج'"></td>
                                                <td class="px-4 py-2.5 text-yellow-700" x-text="formatNum(row.unpaid) + ' ش.ج'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        {{-- Top Unpaid --}}
                        <div x-show="reportData.top_unpaid.length > 0" class="bg-white rounded-xl border border-gray-100 shadow-sm">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <h3 class="text-sm font-semibold text-gray-700">أعلى الطلاب في المتأخرات</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50"><tr>
                                        <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">#</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">الطالب</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">أشهر</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">إجمالي المتأخرات</th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <template x-for="(s, i) in reportData.top_unpaid" :key="i">
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-2.5 text-xs text-gray-400" x-text="i + 1"></td>
                                                <td class="px-4 py-2.5 font-medium text-gray-800" x-text="s.student_name"></td>
                                                <td class="px-4 py-2.5"><span class="px-2 py-0.5 bg-orange-50 text-orange-700 rounded text-xs" x-text="s.months_count + ' شهر'"></span></td>
                                                <td class="px-4 py-2.5 font-bold text-red-600" x-text="formatNum(s.total_unpaid) + ' ش.ج'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- ── SUB-TAB: المصروفات ── --}}
            <div x-show="reportSubTab === 'expenses'" class="p-4 space-y-4">
                {{-- Header --}}
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <input type="month" x-model="expFilters.month" @change="loadExpenses()"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        <select x-model="expFilters.category" @change="loadExpenses()"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                            <option value="">جميع التصنيفات</option>
                            <template x-for="cat in expCategories" :key="cat">
                                <option :value="cat" x-text="cat"></option>
                            </template>
                        </select>
                    </div>
                    <button @click="openExpenseModal()"
                        class="flex items-center gap-1.5 px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        إضافة مصروف
                    </button>
                </div>
                {{-- Total --}}
                <div class="bg-orange-50 border border-orange-200 rounded-xl p-3 flex items-center justify-between">
                    <span class="text-sm text-orange-700 font-medium">إجمالي المصروفات</span>
                    <span class="text-xl font-bold text-orange-700" x-text="formatNum(expTotal) + ' ش.ج'"></span>
                </div>
                {{-- Loading --}}
                <div x-show="expLoading" class="py-8 text-center text-gray-400">
                    <svg class="animate-spin h-7 w-7 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
                {{-- Expenses Table --}}
                <div x-show="!expLoading" class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-x-auto">
                    <table class="w-full text-sm" x-show="expenses.length > 0">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">التصنيف</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">الوصف</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">المبلغ</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">التاريخ</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <template x-for="exp in expenses" :key="exp.id">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-2.5">
                                        <span class="px-2 py-0.5 bg-orange-50 text-orange-700 text-xs rounded-full" x-text="exp.category"></span>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-700" x-text="exp.description"></td>
                                    <td class="px-4 py-2.5 font-semibold text-gray-800" x-text="formatNum(exp.amount) + ' ش.ج'"></td>
                                    <td class="px-4 py-2.5 text-xs text-gray-500" x-text="exp.expense_date"></td>
                                    <td class="px-4 py-2.5">
                                        <div class="flex items-center gap-1">
                                            <button @click="openExpenseModal(exp)" class="p-1.5 text-blue-500 hover:bg-blue-50 rounded transition" title="تعديل">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button @click="deleteExpense(exp)" class="p-1.5 text-red-500 hover:bg-red-50 rounded transition" title="حذف">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div x-show="!expenses.length && !expLoading" class="py-10 text-center text-gray-400 text-sm">
                        لا توجد مصروفات لهذا الشهر
                    </div>
                </div>
            </div>

            {{-- ── SUB-TAB: الرواتب ── --}}
            <div x-show="reportSubTab === 'salaries'" class="p-4 space-y-4">
                {{-- Date Filter --}}
                <div class="flex flex-wrap items-center gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">احسب حتى تاريخ</label>
                        <input type="date" x-model="salaryUntilDate" @change="loadSalaryData()"
                            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400">
                    </div>
                    <div class="text-xs text-gray-400 mt-4">
                        <span x-show="salaryUntilDate" x-text="'سعر اليوم = الراتب ÷ 31'"></span>
                    </div>
                </div>
                {{-- Loading --}}
                <div x-show="salaryLoading" class="py-8 text-center text-gray-400">
                    <svg class="animate-spin h-7 w-7 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
                {{-- Staff Salary Table --}}
                <div x-show="!salaryLoading" class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-x-auto">
                    <table class="w-full text-sm" x-show="salaryStaff.length > 0">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">الاسم</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">الراتب / شهر</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">سعر اليوم</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">بداية الدورة</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">أيام عمل</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">المستحق</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">إجراء</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <template x-for="s in salaryStaff" :key="s.type + s.id">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-2.5">
                                        <div class="font-medium text-gray-800" x-text="s.name"></div>
                                        <div class="text-xs text-gray-400" x-text="s.type === 'teacher' ? 'مدرس' : 'موظف'"></div>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-600" x-text="formatNum(s.salary) + ' ش.ج'"></td>
                                    <td class="px-4 py-2.5 text-gray-500 text-xs" x-text="s.cycle?.daily_rate ? formatNum(s.cycle.daily_rate) + ' ش.ج' : '—'"></td>
                                    <td class="px-4 py-2.5 text-xs text-gray-500" x-text="s.cycle?.cycle_start ?? '—'"></td>
                                    <td class="px-4 py-2.5">
                                        <span x-show="s.cycle?.days_worked"
                                            :class="s.cycle?.full_cycle_due ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'"
                                            class="px-2 py-0.5 rounded-full text-xs font-medium"
                                            x-text="(s.cycle?.days_worked ?? '—') + ' / 31'">
                                        </span>
                                        <span x-show="!s.cycle?.days_worked" class="text-gray-400 text-xs">—</span>
                                    </td>
                                    <td class="px-4 py-2.5 font-bold text-purple-700" x-text="s.cycle?.prorated_amount ? formatNum(s.cycle.prorated_amount) + ' ش.ج' : '—'"></td>
                                    <td class="px-4 py-2.5">
                                        <button x-show="s.cycle?.prorated_amount > 0"
                                            @click="confirmSalaryPayment(s)"
                                            class="px-3 py-1 bg-purple-100 text-purple-700 text-xs rounded-lg hover:bg-purple-200 transition">
                                            دفع
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <div x-show="!salaryStaff.length && !salaryLoading" class="py-10 text-center text-gray-400 text-sm">
                        لا يوجد عاملون بأجور محددة
                    </div>
                </div>
                {{-- Salary History --}}
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">سجل الرواتب المدفوعة</h3>
                        <div class="flex items-center gap-2">
                            <input type="month" x-model="salaryHistMonth" @change="loadSalaryHistory()"
                                class="border border-gray-300 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-purple-400">
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" x-show="salaryHistory.length > 0">
                            <thead class="bg-gray-50"><tr>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">الاسم</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">الصفة</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">أيام</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">المبلغ</th>
                                <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">تاريخ الدفع</th>
                            </tr></thead>
                            <tbody class="divide-y divide-gray-50">
                                <template x-for="p in salaryHistory" :key="p.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2.5 font-medium text-gray-800" x-text="p.name"></td>
                                        <td class="px-4 py-2.5 text-xs text-gray-500" x-text="p.role"></td>
                                        <td class="px-4 py-2.5 text-xs"><span class="px-2 py-0.5 bg-purple-50 text-purple-700 rounded-full" x-text="p.days_worked + ' يوم'"></span></td>
                                        <td class="px-4 py-2.5 font-semibold text-purple-700" x-text="formatNum(p.amount) + ' ش.ج'"></td>
                                        <td class="px-4 py-2.5 text-xs text-gray-500" x-text="p.payment_date"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <div x-show="!salaryHistory.length" class="py-6 text-center text-gray-400 text-sm">لا توجد مدفوعات</div>
                    </div>
                </div>
            </div>

            {{-- ── SUB-TAB: الصافي ── --}}
            <div x-show="reportSubTab === 'net'" class="p-4 space-y-5">
                <div x-show="reportLoading" class="py-8 text-center text-gray-400">
                    <svg class="animate-spin h-7 w-7 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </div>
                <template x-if="reportData && !reportLoading">
                    <div class="space-y-5">
                        {{-- Summary Cards --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                                <p class="text-xs text-blue-600 mb-1">إيرادات الشهر</p>
                                <p class="text-xl font-bold text-blue-700" x-text="formatNum(reportData.month_revenue) + ' ش.ج'"></p>
                            </div>
                            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 text-center">
                                <p class="text-xs text-orange-600 mb-1">مصاريف الشهر</p>
                                <p class="text-xl font-bold text-orange-700" x-text="formatNum(reportData.expenses_this_month) + ' ش.ج'"></p>
                            </div>
                            <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-center">
                                <p class="text-xs text-purple-600 mb-1">رواتب مدفوعة</p>
                                <p class="text-xl font-bold text-purple-700" x-text="formatNum(reportData.salaries_this_month) + ' ش.ج'"></p>
                            </div>
                            <div :class="reportData.net_this_month >= 0 ? 'bg-teal-50 border-teal-200' : 'bg-red-50 border-red-200'"
                                class="border rounded-xl p-4 text-center">
                                <p class="text-xs mb-1" :class="reportData.net_this_month >= 0 ? 'text-teal-600' : 'text-red-600'">صافي الشهر</p>
                                <p class="text-xl font-bold" :class="reportData.net_this_month >= 0 ? 'text-teal-700' : 'text-red-700'"
                                    x-text="formatNum(reportData.net_this_month) + ' ش.ج'"></p>
                            </div>
                        </div>
                        {{-- Formula --}}
                        <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-500 text-center">
                            الصافي = إيرادات (<span class="text-blue-600 font-medium" x-text="formatNum(reportData.month_revenue)"></span>)
                            − مصاريف (<span class="text-orange-600 font-medium" x-text="formatNum(reportData.expenses_this_month)"></span>)
                            − رواتب (<span class="text-purple-600 font-medium" x-text="formatNum(reportData.salaries_this_month)"></span>)
                            = <span class="font-bold" :class="reportData.net_this_month >= 0 ? 'text-teal-600' : 'text-red-600'" x-text="formatNum(reportData.net_this_month)"></span> ش.ج
                        </div>
                        {{-- 12-Month Net History --}}
                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <h3 class="text-sm font-semibold text-gray-700">مقارنة الشهور — آخر 12 شهراً</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50"><tr>
                                        <th class="px-4 py-2.5 text-right text-xs font-medium text-gray-500">الشهر</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-medium text-blue-600">إيرادات</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-medium text-orange-600">مصاريف</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-medium text-purple-600">رواتب</th>
                                        <th class="px-4 py-2.5 text-right text-xs font-medium text-teal-600">صافي</th>
                                    </tr></thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <template x-for="(row, i) in reportData.monthly_history" :key="i">
                                            <tr class="hover:bg-gray-50"
                                                :class="row.month === new Date().toISOString().slice(0,7) ? 'bg-blue-50/40' : ''">
                                                <td class="px-4 py-2.5 font-medium text-gray-700" x-text="row.label"></td>
                                                <td class="px-4 py-2.5 text-blue-600" x-text="formatNum(row.revenue) + ' ش.ج'"></td>
                                                <td class="px-4 py-2.5 text-orange-600" x-text="formatNum(row.expenses) + ' ش.ج'"></td>
                                                <td class="px-4 py-2.5 text-purple-600" x-text="formatNum(row.salaries) + ' ش.ج'"></td>
                                                <td class="px-4 py-2.5 font-bold"
                                                    :class="row.net >= 0 ? 'text-teal-700' : 'text-red-600'"
                                                    x-text="formatNum(row.net) + ' ش.ج'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

        </div>

    </div>{{-- end tabs --}}


    {{-- ===================== MODAL: تسجيل الدفعة ===================== --}}
    <div x-show="showPayModal" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @keydown.escape.window="showPayModal = false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b">
                <h3 class="text-base font-bold text-gray-800">تسجيل دفعة</h3>
                <button @click="showPayModal = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                {{-- Student Info --}}
                <div class="bg-gray-50 rounded-lg p-3 space-y-1.5 text-sm border border-gray-100">
                    <div class="flex justify-between">
                        <span class="text-gray-500">الطالب</span>
                        <span class="font-medium text-gray-800" x-text="payingPayment?.student_name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">المبلغ</span>
                        <span class="font-bold text-green-700" x-text="(payingPayment?.amount ?? '') + ' ش.ج'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">الشهر</span>
                        <span x-text="formatMonth(payingPayment?.month)"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">النوع</span>
                        <span x-text="formatType(payingPayment?.type)"></span>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">طريقة الدفع *</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" x-model="payForm.payment_method" value="cash">
                            <span class="text-sm">نقدي</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" x-model="payForm.payment_method" value="bank_transfer">
                            <span class="text-sm">تحويل بنكي</span>
                        </label>
                    </div>
                </div>

                {{-- Account Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم الحساب — من دفع المبلغ *</label>
                    <input type="text" x-model="payForm.account_name"
                        placeholder="اسم الشخص الذي سدّد الدفعة"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                {{-- Paid Date --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الدفع</label>
                    <input type="date" x-model="payForm.paid_date"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                    <textarea x-model="payForm.notes" rows="2" placeholder="اختياري"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3 px-5 pb-5">
                <button @click="submitPay()" :disabled="savingPay"
                    class="flex-1 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition disabled:opacity-60">
                    <span x-show="!savingPay">تأكيد الدفع</span>
                    <span x-show="savingPay">جار الحفظ...</span>
                </button>
                <button @click="showPayModal = false"
                    class="px-5 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">
                    إلغاء
                </button>
            </div>
        </div>
    </div>


    {{-- ===================== MODAL: تعديل الدفعة ===================== --}}
    <div x-show="showEditModal" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @keydown.escape.window="showEditModal = false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b">
                <h3 class="text-base font-bold text-gray-800">تعديل بيانات الدفعة</h3>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div class="bg-gray-50 rounded-lg p-3 text-sm border border-gray-100">
                    <span class="text-gray-500">الطالب: </span>
                    <span class="font-medium" x-text="editingPayment?.student_name"></span>
                    <span class="text-gray-400 mr-2" x-text="'— ' + formatMonth(editingPayment?.month)"></span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ (ش.ج)</label>
                    <input type="number" x-model="editForm.amount" step="0.01" min="0"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الاستحقاق</label>
                    <input type="date" x-model="editForm.due_date"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                    <textarea x-model="editForm.notes" rows="2"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3 px-5 pb-5">
                <button @click="submitEdit()" :disabled="savingEdit"
                    class="flex-1 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                    <span x-show="!savingEdit">حفظ التغييرات</span>
                    <span x-show="savingEdit">جار الحفظ...</span>
                </button>
                <button @click="showEditModal = false"
                    class="px-5 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">
                    إلغاء
                </button>
            </div>
        </div>
    </div>


    {{-- ===================== MODAL: إضافة دفعة مخصصة ===================== --}}
    <div x-show="showAddModal" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @keydown.escape.window="showAddModal = false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg" style="max-height:90vh;overflow-y:auto;" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b sticky top-0 bg-white z-10">
                <h3 class="text-base font-bold text-gray-800">إضافة دفعة مخصصة</h3>
                <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-4">

                {{-- Student Search --}}
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-1">اختيار الطالب *</label>
                    <input type="text" x-model="studentSearch" @input="searchStudents()"
                        placeholder="ابحث بالاسم أو رقم الهوية (4 أحرف على الأقل)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    {{-- Dropdown --}}
                    <div x-show="studentResults.length > 0"
                        class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-44 overflow-y-auto">
                        <template x-for="s in studentResults" :key="s.id">
                            <button @click="selectStudent(s)" type="button"
                                class="w-full px-3 py-2 text-right text-sm hover:bg-gray-50 transition flex justify-between items-center border-b border-gray-50 last:border-0">
                                <span x-text="s.name" class="font-medium text-gray-800"></span>
                                <span x-text="s.national_id" class="text-xs text-gray-400"></span>
                            </button>
                        </template>
                    </div>
                    <span x-show="searchingStudents" class="absolute left-3 top-9 text-xs text-gray-400">جار البحث...</span>
                    {{-- Selected --}}
                    <div x-show="selectedStudent" class="mt-2 flex items-center gap-2 bg-green-50 border border-green-200 rounded-lg px-3 py-1.5 text-sm">
                        <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span class="text-green-700 font-medium" x-text="selectedStudent?.name"></span>
                        <span x-show="selectedStudent?.monthly_fee" class="text-xs text-green-500">
                            (رسوم شهرية: <span x-text="selectedStudent?.monthly_fee"></span> ش.ج)
                        </span>
                    </div>
                </div>

                {{-- Amount + Type --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ (ش.ج) *</label>
                        <input type="number" x-model="addForm.amount" step="0.01" min="0.01"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">نوع الدفعة *</label>
                        <select x-model="addForm.type"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="monthly">شهري</option>
                            <option value="admission_fee">رسوم انتساب</option>
                            <option value="educational_bundle">حزمة تعليمية</option>
                        </select>
                    </div>
                </div>

                {{-- Month + Due Date --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">الشهر *</label>
                        <input type="month" x-model="addForm.month"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الاستحقاق</label>
                        <input type="date" x-model="addForm.due_date"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">حالة الدفعة *</label>
                    <div class="flex gap-5">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" x-model="addForm.status" value="unpaid">
                            <span class="text-sm">غير مدفوع</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" x-model="addForm.status" value="paid">
                            <span class="text-sm">مدفوع</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" x-model="addForm.status" value="pending">
                            <span class="text-sm">في الانتظار</span>
                        </label>
                    </div>
                </div>

                {{-- Payment details — only if paid --}}
                <div x-show="addForm.status === 'paid'"
                    x-transition
                    class="bg-green-50 border border-green-200 rounded-lg p-3 space-y-3">
                    <p class="text-xs font-semibold text-green-700">بيانات الدفع</p>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">طريقة الدفع</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" x-model="addForm.payment_method" value="cash">
                                <span class="text-sm">نقدي</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" x-model="addForm.payment_method" value="bank_transfer">
                                <span class="text-sm">تحويل</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">اسم الحساب (من دفع)</label>
                        <input type="text" x-model="addForm.account_name"
                            placeholder="اسم الشخص الذي سدّد المبلغ"
                            class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                    <textarea x-model="addForm.notes" rows="2" placeholder="اختياري"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3 px-5 pb-5 border-t pt-4">
                <button @click="submitAddPayment()" :disabled="savingAdd"
                    class="flex-1 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                    <span x-show="!savingAdd">إضافة الدفعة</span>
                    <span x-show="savingAdd">جار الإضافة...</span>
                </button>
                <button @click="showAddModal = false"
                    class="px-5 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">
                    إلغاء
                </button>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: مصروف ===================== --}}
    <div x-show="showExpenseModal" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @keydown.escape.window="showExpenseModal = false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 class="text-base font-bold text-gray-800" x-text="editingExpense ? 'تعديل مصروف' : 'إضافة مصروف جديد'"></h3>
                <button @click="showExpenseModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">التصنيف *</label>
                    <select x-model="expForm.category" @change="expCustomCategory = expForm.category === 'أخرى'"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        <option value="">— اختر تصنيف —</option>
                        <template x-for="cat in expCategories" :key="cat">
                            <option :value="cat" x-text="cat"></option>
                        </template>
                    </select>
                </div>
                <div x-show="expCustomCategory">
                    <label class="block text-xs font-medium text-gray-600 mb-1">تصنيف مخصص *</label>
                    <input type="text" x-model="expForm.customCategory" placeholder="اكتب التصنيف..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">الوصف *</label>
                    <input type="text" x-model="expForm.description" placeholder="وصف المصروف"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">المبلغ (ش.ج) *</label>
                        <input type="number" x-model="expForm.amount" min="0.01" step="0.01" placeholder="0.00"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">التاريخ *</label>
                        <input type="date" x-model="expForm.expense_date"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">ملاحظات</label>
                    <textarea x-model="expForm.notes" rows="2" placeholder="اختياري"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 resize-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3 px-6 pb-6">
                <button @click="saveExpense()" :disabled="savingExpense"
                    class="flex-1 py-2.5 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition disabled:opacity-60">
                    <span x-show="!savingExpense" x-text="editingExpense ? 'حفظ التعديلات' : 'إضافة'"></span>
                    <span x-show="savingExpense">جار الحفظ...</span>
                </button>
                <button @click="showExpenseModal = false"
                    class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">
                    إلغاء
                </button>
            </div>
        </div>
    </div>

</div>{{-- end x-data --}}

@push('scripts')
<script>
window.routes = {
    paymentsDue:            '{{ url("/admin/payments/due") }}',
    paymentsHistory:        '{{ url("/admin/payments/history") }}',
    paymentsExport:         '{{ url("/admin/payments/export") }}',
    paymentsReportData:     '{{ url("/admin/payments/report-data") }}',
    paymentsRemind:         '{{ url("/admin/payments") }}',
    paymentsSendReminders:  '{{ url("/admin/payments/send-reminders") }}',
    paymentsRecord:         '{{ url("/admin/payments") }}',
    paymentsUpdate:         '{{ url("/admin/payments") }}',
    paymentsDestroy:        '{{ url("/admin/payments") }}',
    paymentsAddCustom:      '{{ url("/admin/payments/add-custom") }}',
    paymentsStudentSearch:  '{{ url("/admin/payments/student-search") }}',
    paymentsGroupsList:     '{{ url("/admin/payments/groups-list") }}',
    financeExpenses:        '{{ url("/admin/finance/expenses") }}',
    financeSalaryData:      '{{ url("/admin/finance/salary-data") }}',
    financeSalaryPay:       '{{ url("/admin/finance/salary-pay") }}',
    financeSalaryHistory:   '{{ url("/admin/finance/salary-history") }}',
};

function paymentsManager() {
    return {
        activeTab: 'monthly',
        loading:   false,
        error:     '',

        filters: {
            month:   new Date().toISOString().slice(0, 7),
            groupId: '',
            status:  'unpaid',
            type:    'monthly',
        },

        payments: [],
        groups:   [],
        stats: {
            total_expected: 0, total_paid: 0, remaining: 0,
            paid_count: 0, unpaid_count: 0, overdue_count: 0,
        },

        // Pay modal
        showPayModal:   false,
        payingPayment:  null,
        payForm: { payment_method: 'cash', account_name: '', paid_date: '', notes: '' },
        savingPay: false,

        // Edit modal
        showEditModal:    false,
        editingPayment:   null,
        editForm: { amount: '', due_date: '', notes: '' },
        savingEdit: false,

        // Add modal
        showAddModal:     false,
        studentSearch:    '',
        studentResults:   [],
        searchingStudents: false,
        selectedStudent:  null,
        addForm: {
            amount: '', type: 'monthly', month: new Date().toISOString().slice(0, 7),
            due_date: '', status: 'unpaid', payment_method: 'cash', account_name: '', notes: '',
        },
        savingAdd: false,
        _searchTimer: null,

        // Communication tab
        commFilters: { month: new Date().toISOString().slice(0, 7), groupId: '' },
        commPayments: [],
        commStats: { total_amount: 0 },
        commLoading: false,
        commLoaded: false,
        commError: '',
        commSuccess: '',
        commSendingId: null,
        commBulkSending: false,

        // Reports tab
        reportSubTab: 'revenue',
        reportData: null,
        reportLoading: false,
        reportLoaded: false,
        reportError: '',

        // Expenses
        expenses: [],
        expTotal: 0,
        expLoading: false,
        expCategories: ['كهرباء', 'ماء', 'إيجار', 'صيانة', 'مواد مكتبية', 'أخرى'],
        expFilters: { month: new Date().toISOString().slice(0, 7), category: '' },
        showExpenseModal: false,
        editingExpense: null,
        savingExpense: false,
        expCustomCategory: false,
        expForm: { category: '', customCategory: '', description: '', amount: '', expense_date: new Date().toISOString().split('T')[0], notes: '' },

        // Salaries
        salaryStaff: [],
        salaryLoading: false,
        salaryUntilDate: '',
        salaryHistory: [],
        salaryHistMonth: new Date().toISOString().slice(0, 7),

        // History tab
        histFilters: { studentId: '', monthFrom: '', monthTo: '', status: 'all', type: 'all' },
        histStudentSearch: '',
        histStudentResults: [],
        histSearchingStudents: false,
        histSelectedStudent: null,
        histPayments: [],
        histStats: { total_count: 0, total_amount: 0, paid_amount: 0, unpaid_amount: 0, paid_count: 0, unpaid_count: 0 },
        histLoading: false,
        histLoaded: false,
        histError: '',
        _histSearchTimer: null,

        async init() {
            await this.loadGroups();
            await this.loadPayments();
        },

        async loadGroups() {
            try {
                const r = await fetch(window.routes.paymentsGroupsList);
                const d = await r.json();
                if (d.success) this.groups = d.groups;
            } catch (_) {}
        },

        async loadPayments() {
            this.loading = true;
            this.error   = '';
            try {
                const p = new URLSearchParams();
                if (this.filters.month)   p.set('month',    this.filters.month);
                if (this.filters.groupId) p.set('group_id', this.filters.groupId);
                if (this.filters.status)  p.set('status',   this.filters.status);
                if (this.filters.type)    p.set('type',     this.filters.type);
                const r = await fetch(`${window.routes.paymentsDue}?${p}`);
                const d = await r.json();
                if (d.success) { this.payments = d.payments; this.stats = d.stats; }
                else this.error = d.message || 'حدث خطأ في تحميل البيانات';
            } catch (_) { this.error = 'حدث خطأ في الاتصال بالخادم'; }
            finally { this.loading = false; }
        },

        openPayModal(payment) {
            this.payingPayment = payment;
            this.payForm = {
                payment_method: 'cash',
                account_name:   '',
                paid_date:      new Date().toISOString().split('T')[0],
                notes:          '',
            };
            this.showPayModal = true;
        },

        async submitPay() {
            if (!this.payForm.account_name.trim()) {
                alert('يرجى إدخال اسم الحساب (من سدّد الدفعة)');
                return;
            }
            this.savingPay = true;
            try {
                const r = await fetch(
                    `${window.routes.paymentsRecord}/${this.payingPayment.id}/record`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(this.payForm),
                    }
                );
                const d = await r.json();
                if (d.success) {
                    this.showPayModal = false;
                    await this.loadPayments();
                    if (this.histLoaded) await this.loadHistory();
                    if (this.commLoaded) await this.loadCommPayments();
                } else alert(d.message || 'حدث خطأ في تسجيل الدفعة');
            } catch (_) { alert('حدث خطأ في الاتصال'); }
            finally { this.savingPay = false; }
        },

        openEditModal(payment) {
            this.editingPayment = payment;
            this.editForm = { amount: payment.amount, due_date: payment.due_date || '', notes: payment.notes || '' };
            this.showEditModal = true;
        },

        async submitEdit() {
            this.savingEdit = true;
            try {
                const r = await fetch(
                    `${window.routes.paymentsUpdate}/${this.editingPayment.id}/update`,
                    {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(this.editForm),
                    }
                );
                const d = await r.json();
                if (d.success) {
                    this.showEditModal = false;
                    await this.loadPayments();
                    if (this.histLoaded) await this.loadHistory();
                    if (this.commLoaded) await this.loadCommPayments();
                } else alert(d.message || 'حدث خطأ في التعديل');
            } catch (_) { alert('حدث خطأ في الاتصال'); }
            finally { this.savingEdit = false; }
        },

        async deletePayment(payment) {
            if (!confirm(`حذف دفعة ${this.formatMonth(payment.month)} للطالب ${payment.student_name}؟`)) return;
            try {
                const r = await fetch(
                    `${window.routes.paymentsDestroy}/${payment.id}`,
                    {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    }
                );
                const d = await r.json();
                if (d.success) {
                    this.payments = this.payments.filter(p => p.id !== payment.id);
                    await this.loadPayments();
                    if (this.histLoaded) await this.loadHistory();
                } else alert(d.message || 'حدث خطأ في الحذف');
            } catch (_) { alert('حدث خطأ في الاتصال'); }
        },

        openAddModal() {
            this.selectedStudent = null;
            this.studentSearch   = '';
            this.studentResults  = [];
            this.addForm = {
                amount: '', type: 'monthly',
                month:  this.filters.month || new Date().toISOString().slice(0, 7),
                due_date: '', status: 'unpaid',
                payment_method: 'cash', account_name: '', notes: '',
            };
            this.showAddModal = true;
        },

        searchStudents() {
            clearTimeout(this._searchTimer);
            if (this.studentSearch.length < 4) { this.studentResults = []; return; }
            this._searchTimer = setTimeout(async () => {
                this.searchingStudents = true;
                try {
                    const r = await fetch(`${window.routes.paymentsStudentSearch}?q=${encodeURIComponent(this.studentSearch)}`);
                    const d = await r.json();
                    if (d.success) this.studentResults = d.students;
                } catch (_) {}
                finally { this.searchingStudents = false; }
            }, 300);
        },

        selectStudent(student) {
            this.selectedStudent   = student;
            this.studentSearch     = student.name;
            this.studentResults    = [];
            if (student.monthly_fee && !this.addForm.amount) {
                this.addForm.amount = student.monthly_fee;
            }
        },

        async submitAddPayment() {
            if (!this.selectedStudent) { alert('يرجى اختيار طالب'); return; }
            if (!this.addForm.amount || Number(this.addForm.amount) <= 0) { alert('يرجى إدخال مبلغ صحيح'); return; }
            this.savingAdd = true;
            try {
                const r = await fetch(window.routes.paymentsAddCustom, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ student_id: this.selectedStudent.id, ...this.addForm }),
                });
                const d = await r.json();
                if (d.success) { this.showAddModal = false; await this.loadPayments(); }
                else alert(d.message || 'حدث خطأ في إضافة الدفعة');
            } catch (_) { alert('حدث خطأ في الاتصال'); }
            finally { this.savingAdd = false; }
        },

        async loadHistory() {
            this.histLoading = true;
            this.histError   = '';
            try {
                const p = new URLSearchParams();
                if (this.histFilters.studentId) p.set('student_id', this.histFilters.studentId);
                if (this.histFilters.monthFrom) p.set('month_from', this.histFilters.monthFrom);
                if (this.histFilters.monthTo)   p.set('month_to',   this.histFilters.monthTo);
                if (this.histFilters.status)    p.set('status',     this.histFilters.status);
                if (this.histFilters.type)      p.set('type',       this.histFilters.type);
                const r = await fetch(`${window.routes.paymentsHistory}?${p}`);
                const d = await r.json();
                if (d.success) { this.histPayments = d.payments; this.histStats = d.stats; this.histLoaded = true; }
                else this.histError = d.message || 'حدث خطأ في تحميل البيانات';
            } catch (_) { this.histError = 'حدث خطأ في الاتصال بالخادم'; }
            finally { this.histLoading = false; }
        },

        histSearchStudents() {
            clearTimeout(this._histSearchTimer);
            if (this.histStudentSearch.length < 4) { this.histStudentResults = []; return; }
            this._histSearchTimer = setTimeout(async () => {
                this.histSearchingStudents = true;
                try {
                    const r = await fetch(`${window.routes.paymentsStudentSearch}?q=${encodeURIComponent(this.histStudentSearch)}`);
                    const d = await r.json();
                    if (d.success) this.histStudentResults = d.students;
                } catch (_) {}
                finally { this.histSearchingStudents = false; }
            }, 300);
        },

        selectHistStudent(student) {
            this.histSelectedStudent   = student;
            this.histFilters.studentId = student.id;
            this.histStudentSearch     = student.name;
            this.histStudentResults    = [];
        },

        clearHistStudent() {
            this.histSelectedStudent   = null;
            this.histFilters.studentId = '';
            this.histStudentSearch     = '';
        },

        histExportUrl() {
            const p = new URLSearchParams();
            if (this.histFilters.studentId) p.set('student_id', this.histFilters.studentId);
            if (this.histFilters.monthFrom) p.set('month_from', this.histFilters.monthFrom);
            if (this.histFilters.monthTo)   p.set('month_to',   this.histFilters.monthTo);
            if (this.histFilters.status)    p.set('status',     this.histFilters.status);
            if (this.histFilters.type)      p.set('type',       this.histFilters.type);
            return `${window.routes.paymentsExport}?${p}`;
        },

        formatPaymentMethod(method) {
            return { cash: 'نقدي', bank_transfer: 'تحويل بنكي' }[method] || (method || '—');
        },

        async loadCommPayments() {
            this.commLoading = true;
            this.commError   = '';
            this.commSuccess = '';
            try {
                const p = new URLSearchParams({ month: this.commFilters.month, status: 'unpaid', type: 'all' });
                if (this.commFilters.groupId) p.set('group_id', this.commFilters.groupId);
                const r = await fetch(`${window.routes.paymentsDue}?${p}`);
                const d = await r.json();
                if (d.success) {
                    this.commPayments = d.payments.map(p => ({ ...p, graceDays: 0 }));
                    this.commStats    = { total_amount: d.stats.remaining ?? 0 };
                    this.commLoaded   = true;
                } else this.commError = d.message || 'حدث خطأ في تحميل البيانات';
            } catch (_) { this.commError = 'حدث خطأ في الاتصال بالخادم'; }
            finally { this.commLoading = false; }
        },

        async sendReminder(payment) {
            this.commSendingId = payment.id;
            this.commSuccess   = '';
            try {
                const r = await fetch(`${window.routes.paymentsRemind}/${payment.id}/remind`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ grace_days: payment.graceDays ?? 0 }),
                });
                const d = await r.json();
                if (d.success) this.commSuccess = d.message;
                else alert(d.message || 'حدث خطأ في إرسال التذكير');
            } catch (_) { alert('حدث خطأ في الاتصال'); }
            finally { this.commSendingId = null; }
        },

        async sendBulkReminders() {
            if (!confirm(`إرسال تذكير لـ ${this.commPayments.length} ولي أمر؟`)) return;
            this.commBulkSending = true;
            this.commSuccess     = '';
            try {
                const r = await fetch(window.routes.paymentsSendReminders, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ month: this.commFilters.month, group_id: this.commFilters.groupId }),
                });
                const d = await r.json();
                if (d.success) this.commSuccess = d.message;
                else alert(d.message || 'حدث خطأ في إرسال التذكيرات');
            } catch (_) { alert('حدث خطأ في الاتصال'); }
            finally { this.commBulkSending = false; }
        },

        async loadReport() {
            this.reportLoading = true;
            this.reportError   = '';
            try {
                const r = await fetch(window.routes.paymentsReportData);
                const d = await r.json();
                if (d.success) { this.reportData = d; this.reportLoaded = true; }
                else this.reportError = d.message || 'حدث خطأ في تحميل التقرير';
            } catch (_) { this.reportError = 'حدث خطأ في الاتصال بالخادم'; }
            finally { this.reportLoading = false; }
        },

        // ── Expenses ──
        async loadExpenses() {
            this.expLoading = true;
            try {
                const p = new URLSearchParams();
                if (this.expFilters.month) {
                    const [y, m] = this.expFilters.month.split('-');
                    p.set('year', y); p.set('month', m);
                }
                if (this.expFilters.category) p.set('category', this.expFilters.category);
                const r = await fetch(`${window.routes.financeExpenses}?${p}`);
                const d = await r.json();
                if (d.success) {
                    this.expenses   = d.expenses;
                    this.expTotal   = d.total;
                    if (d.categories?.length) this.expCategories = d.categories;
                }
            } catch (_) {}
            finally { this.expLoading = false; }
        },

        openExpenseModal(exp = null) {
            this.editingExpense     = exp;
            this.expCustomCategory  = false;
            this.expForm = exp ? {
                category:       exp.category,
                customCategory: '',
                description:    exp.description,
                amount:         exp.amount,
                expense_date:   exp.expense_date,
                notes:          exp.notes ?? '',
            } : {
                category: '', customCategory: '', description: '',
                amount: '', expense_date: new Date().toISOString().split('T')[0], notes: '',
            };
            this.showExpenseModal = true;
        },

        async saveExpense() {
            const cat = this.expForm.category === 'أخرى' && this.expForm.customCategory.trim()
                ? this.expForm.customCategory.trim()
                : this.expForm.category;
            if (!cat || !this.expForm.description.trim() || !this.expForm.amount) {
                alert('يرجى تعبئة التصنيف والوصف والمبلغ');
                return;
            }
            this.savingExpense = true;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                const payload = { category: cat, description: this.expForm.description,
                    amount: this.expForm.amount, expense_date: this.expForm.expense_date, notes: this.expForm.notes };
                const url    = this.editingExpense
                    ? `${window.routes.financeExpenses}/${this.editingExpense.id}`
                    : window.routes.financeExpenses;
                const method = this.editingExpense ? 'PUT' : 'POST';
                const r = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(payload) });
                const d = await r.json();
                if (d.success) { this.showExpenseModal = false; await this.loadExpenses(); }
                else alert(d.message || 'حدث خطأ');
            } catch (_) { alert('حدث خطأ في الاتصال'); }
            finally { this.savingExpense = false; }
        },

        async deleteExpense(exp) {
            if (!confirm(`حذف المصروف "${exp.description}"؟`)) return;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                const r = await fetch(`${window.routes.financeExpenses}/${exp.id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf } });
                const d = await r.json();
                if (d.success) await this.loadExpenses();
                else alert(d.message || 'حدث خطأ في الحذف');
            } catch (_) { alert('حدث خطأ في الاتصال'); }
        },

        // ── Salaries ──
        async loadSalaryData() {
            this.salaryLoading = true;
            try {
                const p = new URLSearchParams();
                if (this.salaryUntilDate) p.set('until_date', this.salaryUntilDate);
                const r = await fetch(`${window.routes.financeSalaryData}?${p}`);
                const d = await r.json();
                if (d.success) {
                    this.salaryStaff = d.staff;
                    if (!this.salaryUntilDate && d.academic_year_end_date)
                        this.salaryUntilDate = d.academic_year_end_date;
                    await this.loadSalaryHistory();
                }
            } catch (_) {}
            finally { this.salaryLoading = false; }
        },

        async loadSalaryHistory() {
            try {
                const p = new URLSearchParams();
                if (this.salaryHistMonth) {
                    const [y, m] = this.salaryHistMonth.split('-');
                    p.set('year', y); p.set('month', m);
                }
                const r = await fetch(`${window.routes.financeSalaryHistory}?${p}`);
                const d = await r.json();
                if (d.success) this.salaryHistory = d.payments;
            } catch (_) {}
        },

        async confirmSalaryPayment(staff) {
            const c = staff.cycle;
            if (!c) return;
            const msg = `تأكيد دفع راتب ${staff.name}:\n` +
                `أيام العمل: ${c.days_worked} / 31\n` +
                `سعر اليوم: ${this.formatNum(c.daily_rate)} ش.ج\n` +
                `المبلغ المستحق: ${this.formatNum(c.prorated_amount)} ش.ج`;
            if (!confirm(msg)) return;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            try {
                const r = await fetch(window.routes.financeSalaryPay, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({
                        payable_type:     staff.type,
                        payable_id:       staff.id,
                        days_worked:      c.days_worked,
                        daily_rate:       c.daily_rate,
                        amount:           c.prorated_amount,
                        cycle_start_date: c.cycle_start,
                        cycle_end_date:   c.cycle_end,
                        payment_date:     new Date().toISOString().split('T')[0],
                    }),
                });
                const d = await r.json();
                if (d.success) { alert(d.message); await this.loadSalaryHistory(); }
                else alert(d.message || 'حدث خطأ في تسجيل الدفع');
            } catch (_) { alert('حدث خطأ في الاتصال'); }
        },

        trendBarWidth(amount) {
            const max = Math.max(...(this.reportData?.monthly_trend?.map(r => r.amount) ?? [1]), 1);
            return Math.round((amount / max) * 100);
        },

        typeBarWidth(amount) {
            const max = Math.max(...(this.reportData?.by_type?.map(t => t.total) ?? [1]), 1);
            return Math.round((amount / max) * 100);
        },

        formatNum(n) {
            return parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },

        formatMonth(month) {
            if (!month) return '';
            const [y, m] = month.split('-');
            const names = ['يناير','فبراير','مارس','أبريل','مايو','يونيو',
                           'يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
            return `${names[parseInt(m) - 1]} ${y}`;
        },

        formatType(type) {
            return { monthly: 'شهري', admission_fee: 'رسوم انتساب', educational_bundle: 'حزمة تعليمية' }[type] || type;
        },

        statusClass(status, isOverdue) {
            if (status === 'paid')                   return 'bg-green-100 text-green-700';
            if (status === 'unpaid' && isOverdue)    return 'bg-red-100 text-red-700';
            if (status === 'unpaid')                 return 'bg-yellow-100 text-yellow-700';
            return 'bg-gray-100 text-gray-600';
        },

        statusLabel(status, isOverdue) {
            if (status === 'paid')                return 'مدفوع';
            if (status === 'unpaid' && isOverdue) return 'متأخر';
            if (status === 'unpaid')              return 'غير مدفوع';
            if (status === 'pending')             return 'في الانتظار';
            return status;
        },
    };
}
</script>
@endpush

@endsection
