@extends('layouts.dashboard')

@section('sidebar-menu')
@include('teacher.partials.sidebar')
@endsection

@php
$sidebarTitle    = 'بوابة المدرسين';
$pageTitle       = 'محاضراتي';
$pageDescription = 'إدارة محاضراتك وتسجيل الحضور';
@endphp

@section('content')

<div x-data="teacherLectures()" x-init="init()" class="space-y-5">

    {{-- Notification --}}
    <div x-show="notification.show" x-transition.opacity
        :class="notification.type==='success' ? 'bg-green-50 border-green-300 text-green-800' : 'bg-red-50 border-red-300 text-red-800'"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-[9999] px-5 py-3 rounded-xl border shadow-lg text-sm font-medium flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span x-text="notification.message"></span>
    </div>

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="bg-primary/10 rounded-lg px-3 py-1.5 text-xs text-primary font-medium">
                اليوم: <strong x-text="todayCount"></strong>
            </div>
            <div class="bg-orange-50 rounded-lg px-3 py-1.5 text-xs text-orange-600 font-medium">
                هذا الأسبوع: <strong x-text="weekCount"></strong>
            </div>
        </div>
        <button @click="openCreate()"
            class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            إضافة محاضرة
        </button>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">المجموعة</label>
                <select x-model="filters.group_id" @change="loadLectures()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary min-w-36">
                    <option value="">جميع المجموعات</option>
                    <template x-for="g in groups" :key="g.id">
                        <option :value="String(g.id)" x-text="g.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">النوع</label>
                <select x-model="filters.type" @change="loadLectures()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">جميع الأنواع</option>
                    <option value="lecture">محاضرة</option>
                    <option value="exam">اختبار</option>
                    <option value="review">مراجعة</option>
                    <option value="activity">نشاط</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">الحالة</label>
                <select x-model="filters.status" @change="loadLectures()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">جميع الحالات</option>
                    <option value="scheduled">مجدولة</option>
                    <option value="completed">مكتملة</option>
                    <option value="cancelled">ملغاة</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">الشهر</label>
                <input type="month" x-model="filters.month" @change="loadLectures()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <button @click="filters={group_id:'',type:'',status:'',month:''}; loadLectures()"
                class="px-3 py-2 text-xs text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                مسح الفلاتر
            </button>
        </div>
    </div>

    {{-- Lectures Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">

        <div x-show="loading" class="p-10 text-center text-gray-400">
            <svg class="animate-spin h-7 w-7 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            جار التحميل...
        </div>

        <div x-show="!loading && lectures.length === 0" class="p-10 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-400 text-sm">لا توجد محاضرات</p>
        </div>

        <div x-show="!loading && lectures.length > 0" class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المحاضرة</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المجموعة</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">التاريخ والوقت</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">النوع</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">الحالة</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="l in lectures" :key="l.id">
                        <tr class="hover:bg-gray-50 transition"
                            :class="l.is_today ? 'bg-green-50/30' : ''">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800" x-text="l.title"></p>
                                <p x-show="l.description" class="text-xs text-gray-400 mt-0.5 truncate max-w-48" x-text="l.description"></p>
                            </td>
                            <td class="px-4 py-3 text-gray-600" x-text="l.group_name || '—'"></td>
                            <td class="px-4 py-3 text-gray-600">
                                <p x-text="l.date"></p>
                                <p x-show="l.start_time" class="text-xs text-gray-400"
                                   x-text="l.start_time + (l.end_time ? ' — ' + l.end_time : '')"></p>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="typeClass(l.type)"
                                    class="px-2 py-0.5 rounded-full text-xs font-medium"
                                    x-text="typeLabel(l.type)">
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="statusClass(l.status)"
                                    class="px-2 py-0.5 rounded-full text-xs font-medium"
                                    x-text="statusLabel(l.status)">
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    {{-- View --}}
                                    <button @click="openView(l)" title="تفاصيل"
                                        class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    {{-- Edit --}}
                                    <button x-show="l.status !== 'cancelled'" @click="openEdit(l)" title="تعديل"
                                        class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    {{-- Attendance (only past/today lectures) --}}
                                    <button x-show="(l.is_today || l.is_past) && l.status !== 'cancelled'" @click="openAttendance(l)" title="تسجيل حضور"
                                        class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                        </svg>
                                    </button>
                                    {{-- Reschedule --}}
                                    <button x-show="l.status === 'scheduled' && !l.is_past" @click="openReschedule(l)" title="تأجيل"
                                        class="p-1.5 text-orange-500 hover:bg-orange-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>
                                    {{-- Cancel --}}
                                    <button x-show="l.status === 'scheduled'" @click="openCancel(l)" title="إلغاء"
                                        class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    {{-- Delete --}}
                                    <button @click="deleteLecture(l)" title="حذف"
                                        class="p-1.5 text-red-300 hover:bg-red-50 hover:text-red-500 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== MODAL: إضافة محاضرة ===================== --}}
    <div x-show="showCreate" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="showCreate=false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b sticky top-0 bg-white">
                <h3 class="font-bold text-gray-800">إضافة محاضرة جديدة</h3>
                <button @click="showCreate=false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">عنوان المحاضرة *</label>
                    <input type="text" x-model="form.title" placeholder="عنوان المحاضرة"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">المجموعة *</label>
                        <select x-model="form.group_id" @change="onGroupChange()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">— اختر —</option>
                            <template x-for="g in groups" :key="g.id">
                                <option :value="String(g.id)" x-text="g.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">المادة</label>
                        <select x-model="form.subject_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">— اختر —</option>
                            <template x-for="s in currentSubjects" :key="s.id">
                                <option :value="String(s.id)" x-text="s.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">النوع *</label>
                        <select x-model="form.type"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="lecture">محاضرة</option>
                            <option value="exam">اختبار</option>
                            <option value="review">مراجعة</option>
                            <option value="activity">نشاط</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">التاريخ *</label>
                        <input type="date" x-model="form.date"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">وقت البداية *</label>
                        <input type="time" x-model="form.start_time"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">وقت النهاية *</label>
                        <input type="time" x-model="form.end_time"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">ملاحظات</label>
                    <textarea x-model="form.description" rows="2" placeholder="اختياري"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3 px-5 pb-5">
                <button @click="saveCreate()" :disabled="saving"
                    class="flex-1 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                    <span x-show="!saving">إضافة المحاضرة</span>
                    <span x-show="saving">جار الحفظ...</span>
                </button>
                <button @click="showCreate=false" class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">إلغاء</button>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: تعديل ===================== --}}
    <div x-show="showEdit" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="showEdit=false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b sticky top-0 bg-white">
                <h3 class="font-bold text-gray-800">تعديل المحاضرة</h3>
                <button @click="showEdit=false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">عنوان المحاضرة *</label>
                    <input type="text" x-model="editForm.title"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">المجموعة *</label>
                        <select x-model="editForm.group_id" @change="onEditGroupChange()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <template x-for="g in groups" :key="g.id">
                                <option :value="String(g.id)" x-text="g.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">المادة</label>
                        <select x-model="editForm.subject_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">— اختر —</option>
                            <template x-for="s in editSubjects" :key="s.id">
                                <option :value="String(s.id)" x-text="s.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">النوع *</label>
                        <select x-model="editForm.type"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="lecture">محاضرة</option>
                            <option value="exam">اختبار</option>
                            <option value="review">مراجعة</option>
                            <option value="activity">نشاط</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">التاريخ *</label>
                        <input type="date" x-model="editForm.date"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">وقت البداية *</label>
                        <input type="time" x-model="editForm.start_time"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">وقت النهاية *</label>
                        <input type="time" x-model="editForm.end_time"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">ملاحظات</label>
                    <textarea x-model="editForm.description" rows="2"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3 px-5 pb-5">
                <button @click="saveEdit()" :disabled="saving"
                    class="flex-1 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                    <span x-show="!saving">حفظ التعديلات</span>
                    <span x-show="saving">جار الحفظ...</span>
                </button>
                <button @click="showEdit=false" class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">إلغاء</button>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: تفاصيل ===================== --}}
    <div x-show="showView" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="showView=false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b">
                <h3 class="font-bold text-gray-800">تفاصيل المحاضرة</h3>
                <button @click="showView=false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5 space-y-3" x-show="currentLecture">
                <div class="flex items-start justify-between">
                    <h4 class="font-semibold text-gray-800 text-base" x-text="currentLecture?.title"></h4>
                    <span :class="typeClass(currentLecture?.type)" class="px-2 py-0.5 rounded-full text-xs font-medium shrink-0"
                        x-text="typeLabel(currentLecture?.type)"></span>
                </div>
                <div class="grid grid-cols-2 gap-y-2 text-sm">
                    <span class="text-gray-400">المجموعة</span>
                    <span class="text-gray-700 font-medium" x-text="currentLecture?.group_name || '—'"></span>
                    <span class="text-gray-400">التاريخ</span>
                    <span class="text-gray-700" x-text="currentLecture?.date || '—'"></span>
                    <span class="text-gray-400">الوقت</span>
                    <span class="text-gray-700" x-text="(currentLecture?.start_time || '—') + (currentLecture?.end_time ? ' — ' + currentLecture.end_time : '')"></span>
                    <span class="text-gray-400">الحالة</span>
                    <span :class="statusClass(currentLecture?.status)" class="px-2 py-0.5 rounded-full text-xs font-medium w-fit"
                        x-text="statusLabel(currentLecture?.status)"></span>
                </div>
                <div x-show="currentLecture?.description" class="bg-gray-50 rounded-lg p-3 text-sm text-gray-600" x-text="currentLecture?.description"></div>
            </div>
            <div class="px-5 pb-5">
                <button @click="showView=false" class="w-full py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">إغلاق</button>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: تأجيل ===================== --}}
    <div x-show="showReschedule" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="showReschedule=false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b">
                <h3 class="font-bold text-gray-800">تأجيل المحاضرة</h3>
                <button @click="showReschedule=false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">التاريخ الجديد *</label>
                    <input type="date" x-model="rescheduleForm.new_date"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">وقت البداية</label>
                        <input type="time" x-model="rescheduleForm.new_start_time"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">وقت النهاية</label>
                        <input type="time" x-model="rescheduleForm.new_end_time"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">السبب</label>
                    <input type="text" x-model="rescheduleForm.reason" placeholder="اختياري"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>
            <div class="flex gap-3 px-5 pb-5">
                <button @click="saveReschedule()" :disabled="saving"
                    class="flex-1 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition disabled:opacity-60">
                    <span x-show="!saving">تأجيل</span>
                    <span x-show="saving">جار الحفظ...</span>
                </button>
                <button @click="showReschedule=false" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">إلغاء</button>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: إلغاء ===================== --}}
    <div x-show="showCancel" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="showCancel=false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b">
                <h3 class="font-bold text-gray-800">إلغاء المحاضرة</h3>
                <button @click="showCancel=false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5 space-y-3">
                <p class="text-sm text-gray-600">هل تريد إلغاء المحاضرة "<strong x-text="currentLecture?.title"></strong>"؟</p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">سبب الإلغاء</label>
                    <input type="text" x-model="cancelForm.reason" placeholder="اختياري"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>
            <div class="flex gap-3 px-5 pb-5">
                <button @click="saveCancel()" :disabled="saving"
                    class="flex-1 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition disabled:opacity-60">
                    <span x-show="!saving">تأكيد الإلغاء</span>
                    <span x-show="saving">جار الحفظ...</span>
                </button>
                <button @click="showCancel=false" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">رجوع</button>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL: الحضور ===================== --}}
    <div x-show="showAttendance" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @keydown.escape.window="showAttendance=false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md max-h-[85vh] overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b sticky top-0 bg-white">
                <div>
                    <h3 class="font-bold text-gray-800">تسجيل الحضور</h3>
                    <p class="text-xs text-gray-400" x-text="attendanceLecture?.title + ' — ' + attendanceLecture?.group_name"></p>
                </div>
                <button @click="showAttendance=false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4">
                {{-- Quick actions --}}
                <div class="flex gap-2 mb-3">
                    <button @click="setAllAttendance('present')" class="px-3 py-1.5 bg-green-100 text-green-700 text-xs rounded-lg hover:bg-green-200 transition">الكل حاضر</button>
                    <button @click="setAllAttendance('absent')" class="px-3 py-1.5 bg-red-100 text-red-700 text-xs rounded-lg hover:bg-red-200 transition">الكل غائب</button>
                </div>
                <div class="space-y-2">
                    <template x-for="s in attendanceStudents" :key="s.id">
                        <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                            <span class="text-sm text-gray-800" x-text="s.name"></span>
                            <div class="flex gap-1" dir="ltr">
                                <button @click="attendanceData[s.id]='present'"
                                    :class="attendanceData[s.id]==='present' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-green-50'"
                                    class="px-2.5 py-1 text-xs rounded-lg transition">حاضر</button>
                                <button @click="attendanceData[s.id]='late'"
                                    :class="attendanceData[s.id]==='late' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-yellow-50'"
                                    class="px-2.5 py-1 text-xs rounded-lg transition">متأخر</button>
                                <button @click="attendanceData[s.id]='absent'"
                                    :class="attendanceData[s.id]==='absent' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-red-50'"
                                    class="px-2.5 py-1 text-xs rounded-lg transition">غائب</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="flex gap-3 px-5 pb-5 sticky bottom-0 bg-white pt-3 border-t">
                <button @click="saveAttendance()" :disabled="saving"
                    class="flex-1 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                    <span x-show="!saving">حفظ الحضور</span>
                    <span x-show="saving">جار الحفظ...</span>
                </button>
                <button @click="showAttendance=false" class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">إغلاق</button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
window.teacherLectureRoutes = {
    data:       '{{ route("teacher.lectures.data") }}',
    groups:     '{{ route("teacher.lectures.groups-data") }}',
    store:      '{{ route("teacher.lectures.store") }}',
    update:     '{{ url("/teacher/lectures") }}',
    destroy:    '{{ url("/teacher/lectures") }}',
    reschedule: '{{ url("/teacher/lectures") }}',
    cancel:     '{{ url("/teacher/lectures") }}',
    attendance: '{{ url("/teacher/lectures") }}',
};

function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }

function teacherLectures() {
    return {
        lectures: [], groups: [], allGroupData: [],
        currentSubjects: [], editSubjects: [],
        loading: false,
        todayCount: 0, weekCount: 0,
        filters: { group_id: '', type: '', status: '', month: '' },
        saving: false,
        notification: { show: false, message: '', type: 'success' },

        // Modals
        showCreate: false, showEdit: false, showView: false,
        showReschedule: false, showCancel: false, showAttendance: false,
        currentLecture: null,

        // Forms
        form: { title: '', type: 'lecture', date: '', start_time: '', end_time: '', group_id: '', subject_id: '', description: '' },
        editForm: {},
        rescheduleForm: { new_date: '', new_start_time: '', new_end_time: '', reason: '' },
        cancelForm: { reason: '' },
        attendanceData: {}, attendanceStudents: [], attendanceLecture: null,

        notify(msg, type='success') {
            this.notification = { show: true, message: msg, type };
            setTimeout(() => this.notification.show = false, 3500);
        },

        typeLabel(t) { return { lecture:'محاضرة', exam:'اختبار', review:'مراجعة', activity:'نشاط' }[t] || t; },
        statusLabel(s) { return { scheduled:'مجدولة', completed:'مكتملة', cancelled:'ملغاة', rescheduled:'مؤجلة' }[s] || s; },

        typeClass(t) {
            return { lecture:'bg-blue-100 text-blue-700', exam:'bg-orange-100 text-orange-700',
                     review:'bg-green-100 text-green-700', activity:'bg-purple-100 text-purple-700' }[t] || 'bg-gray-100 text-gray-600';
        },
        statusClass(s) {
            return { scheduled:'bg-blue-100 text-blue-700', completed:'bg-green-100 text-green-700',
                     cancelled:'bg-red-100 text-red-700', rescheduled:'bg-yellow-100 text-yellow-700' }[s] || 'bg-gray-100 text-gray-600';
        },

        async init() { await Promise.all([this.loadGroups(), this.loadLectures()]); },

        async loadGroups() {
            try {
                const r = await fetch(window.teacherLectureRoutes.groups);
                const d = await r.json();
                if (d.success) { this.groups = d.groups; this.allGroupData = d.groups; }
            } catch(_) {}
        },

        async loadLectures() {
            this.loading = true;
            try {
                const p = new URLSearchParams(Object.fromEntries(Object.entries(this.filters).filter(([,v])=>v)));
                const r = await fetch(`${window.teacherLectureRoutes.data}?${p}`);
                const d = await r.json();
                if (d.success) {
                    this.lectures   = d.lectures;
                    this.todayCount = d.today_count ?? 0;
                    this.weekCount  = d.week_count ?? 0;
                }
            } catch(_) {}
            finally { this.loading = false; }
        },

        onGroupChange() {
            const g = this.allGroupData.find(g => String(g.id) === String(this.form.group_id));
            this.currentSubjects = g?.subjects ?? [];
            this.form.subject_id = '';
        },

        onEditGroupChange() {
            const g = this.allGroupData.find(g => String(g.id) === String(this.editForm.group_id));
            this.editSubjects = g?.subjects ?? [];
            this.editForm.subject_id = '';
        },

        openCreate() {
            this.form = { title:'', type:'lecture', date:'', start_time:'', end_time:'', group_id:'', subject_id:'', description:'' };
            this.currentSubjects = [];
            this.showCreate = true;
        },

        async saveCreate() {
            if (!this.form.title || !this.form.group_id || !this.form.date || !this.form.start_time || !this.form.end_time) {
                this.notify('يرجى تعبئة الحقول الإلزامية', 'error'); return;
            }
            this.saving = true;
            try {
                const r = await fetch(window.teacherLectureRoutes.store, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body: JSON.stringify(this.form),
                });
                const d = await r.json();
                if (d.success) { this.showCreate = false; this.notify(d.message); await this.loadLectures(); }
                else this.notify(d.errors ? Object.values(d.errors).flat()[0] : d.message, 'error');
            } catch(_) { this.notify('خطأ في الاتصال', 'error'); }
            finally { this.saving = false; }
        },

        openEdit(lecture) {
            this.currentLecture = lecture;
            this.editForm = {
                title: lecture.title, type: lecture.type, date: lecture.date,
                start_time: lecture.start_time || '', end_time: lecture.end_time || '',
                group_id: String(lecture.group_id), subject_id: String(lecture.subject_id || ''),
                description: lecture.description || '',
            };
            const g = this.allGroupData.find(g => String(g.id) === String(lecture.group_id));
            this.editSubjects = g?.subjects ?? [];
            this.showEdit = true;
        },

        async saveEdit() {
            this.saving = true;
            try {
                const r = await fetch(`${window.teacherLectureRoutes.update}/${this.currentLecture.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body: JSON.stringify(this.editForm),
                });
                const d = await r.json();
                if (d.success) { this.showEdit = false; this.notify(d.message); await this.loadLectures(); }
                else this.notify(d.errors ? Object.values(d.errors).flat()[0] : d.message, 'error');
            } catch(_) { this.notify('خطأ في الاتصال', 'error'); }
            finally { this.saving = false; }
        },

        openView(lecture) { this.currentLecture = lecture; this.showView = true; },

        async deleteLecture(lecture) {
            if (!confirm(`حذف المحاضرة "${lecture.title}"؟`)) return;
            try {
                const r = await fetch(`${window.teacherLectureRoutes.destroy}/${lecture.id}`, {
                    method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf() },
                });
                const d = await r.json();
                if (d.success) { this.notify(d.message); await this.loadLectures(); }
                else this.notify(d.message, 'error');
            } catch(_) { this.notify('خطأ في الاتصال', 'error'); }
        },

        openReschedule(lecture) {
            this.currentLecture = lecture;
            this.rescheduleForm = { new_date: lecture.date, new_start_time: lecture.start_time || '', new_end_time: lecture.end_time || '', reason: '' };
            this.showReschedule = true;
        },

        async saveReschedule() {
            if (!this.rescheduleForm.new_date) { this.notify('يرجى اختيار التاريخ الجديد', 'error'); return; }
            this.saving = true;
            try {
                const r = await fetch(`${window.teacherLectureRoutes.reschedule}/${this.currentLecture.id}/reschedule`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body: JSON.stringify(this.rescheduleForm),
                });
                const d = await r.json();
                if (d.success) { this.showReschedule = false; this.notify(d.message); await this.loadLectures(); }
                else this.notify(d.message, 'error');
            } catch(_) { this.notify('خطأ في الاتصال', 'error'); }
            finally { this.saving = false; }
        },

        openCancel(lecture) {
            this.currentLecture = lecture;
            this.cancelForm = { reason: '' };
            this.showCancel = true;
        },

        async saveCancel() {
            this.saving = true;
            try {
                const r = await fetch(`${window.teacherLectureRoutes.cancel}/${this.currentLecture.id}/cancel`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body: JSON.stringify(this.cancelForm),
                });
                const d = await r.json();
                if (d.success) { this.showCancel = false; this.notify(d.message); await this.loadLectures(); }
                else this.notify(d.message, 'error');
            } catch(_) { this.notify('خطأ في الاتصال', 'error'); }
            finally { this.saving = false; }
        },

        async openAttendance(lecture) {
            this.attendanceLecture = lecture;
            this.attendanceStudents = [];
            this.attendanceData = {};
            this.showAttendance = true;
            try {
                const r = await fetch(`${window.teacherLectureRoutes.attendance}/${lecture.id}/attendance-students`);
                const d = await r.json();
                if (d.success) {
                    this.attendanceStudents = d.students;
                    d.students.forEach(s => {
                        this.attendanceData[s.id] = d.existing?.[s.id] ?? 'present';
                    });
                }
            } catch(_) {}
        },

        setAllAttendance(status) {
            this.attendanceStudents.forEach(s => { this.attendanceData[s.id] = status; });
        },

        async saveAttendance() {
            this.saving = true;
            try {
                const r = await fetch(`${window.teacherLectureRoutes.attendance}/${this.attendanceLecture.id}/attendance`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body: JSON.stringify({ attendance: this.attendanceData }),
                });
                const d = await r.json();
                if (d.success) { this.showAttendance = false; this.notify(d.message); }
                else this.notify(d.message, 'error');
            } catch(_) { this.notify('خطأ في الاتصال', 'error'); }
            finally { this.saving = false; }
        },
    };
}
</script>
@endpush

@endsection
