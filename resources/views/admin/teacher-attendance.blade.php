@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle    = 'البوابة الإدارية';
$pageTitle       = 'حضور وغياب المدرسين';
$pageDescription = 'تسجيل ومتابعة حضور وغياب المدرسين';
@endphp

@section('content')

<div x-data="teacherAttendanceManager()" x-init="init()" class="space-y-6">

    {{-- Tabs --}}
    <div class="flex items-center gap-1 bg-gray-100 rounded-xl p-1 w-fit">
        <button @click="activeTab = 'day'"
            :class="activeTab === 'day' ? 'bg-white shadow text-primary font-semibold' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-2 rounded-lg text-sm transition">
            تسجيل يومي
        </button>
        <button @click="activeTab = 'month'; loadMonthData()"
            :class="activeTab === 'month' ? 'bg-white shadow text-primary font-semibold' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-2 rounded-lg text-sm transition">
            تقرير شهري
        </button>
    </div>

    {{-- ==================== TAB: اليومي ==================== --}}
    <div x-show="activeTab === 'day'" x-transition>

        {{-- Controls --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <input type="date" x-model="selectedDate" @change="loadDayData()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                <button @click="selectedDate = today; loadDayData()"
                    class="px-3 py-2 text-xs border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                    اليوم
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button @click="markAll('present')"
                    class="px-3 py-1.5 text-xs bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition">
                    حاضر الكل
                </button>
                <button @click="markAll('absent')"
                    class="px-3 py-1.5 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">
                    غائب الكل
                </button>
                <button @click="saveAll()" :disabled="saving"
                    class="px-4 py-2 bg-primary text-white text-sm rounded-lg hover:bg-blue-700 transition disabled:opacity-60 flex items-center gap-2">
                    <svg x-show="saving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span x-text="saving ? 'جار الحفظ...' : 'حفظ الكل'"></span>
                </button>
            </div>
        </div>

        {{-- Stats --}}
        <div x-show="stats" class="grid grid-cols-3 md:grid-cols-6 gap-3 mt-4">
            <div class="bg-white rounded-xl border border-gray-100 p-3 text-center">
                <p class="text-xl font-bold text-gray-700" x-text="stats?.total ?? '—'"></p>
                <p class="text-xs text-gray-400 mt-0.5">الكل</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3 text-center">
                <p class="text-xl font-bold text-green-600" x-text="stats?.present ?? 0"></p>
                <p class="text-xs text-gray-400 mt-0.5">حاضر</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3 text-center">
                <p class="text-xl font-bold text-red-500" x-text="stats?.absent ?? 0"></p>
                <p class="text-xs text-gray-400 mt-0.5">غائب</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3 text-center">
                <p class="text-xl font-bold text-orange-500" x-text="stats?.late ?? 0"></p>
                <p class="text-xs text-gray-400 mt-0.5">متأخر</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3 text-center">
                <p class="text-xl font-bold text-blue-500" x-text="stats?.excuse ?? 0"></p>
                <p class="text-xs text-gray-400 mt-0.5">إجازة</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 p-3 text-center">
                <p class="text-xl font-bold text-gray-400" x-text="stats?.unmarked ?? 0"></p>
                <p class="text-xs text-gray-400 mt-0.5">غير مسجل</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mt-4">

            <div x-show="loadingDay" class="p-10 text-center text-gray-400">
                <svg class="animate-spin h-7 w-7 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                جار التحميل...
            </div>

            <div x-show="!loadingDay && dayRecords.length === 0" class="p-10 text-center">
                <p class="text-gray-400 text-sm">لا يوجد مدرسون نشطون</p>
            </div>

            <div x-show="!loadingDay && dayRecords.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المدرس</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">الحالة</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">وقت الدخول</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">ملاحظات</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">حفظ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <template x-for="(rec, idx) in dayRecords" :key="rec.id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                            <span class="text-primary font-bold text-xs" x-text="rec.name.charAt(0)"></span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm" x-text="rec.name"></p>
                                            <p class="text-xs text-gray-400 font-mono" x-text="rec.national_id"></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-1 flex-wrap">
                                        <template x-for="s in statusOptions" :key="s.value">
                                            <button @click="rec.status = s.value"
                                                :class="rec.status === s.value ? s.activeClass : 'bg-gray-100 text-gray-500 hover:bg-gray-200'"
                                                class="px-2.5 py-1 rounded-full text-xs font-medium transition"
                                                x-text="s.label">
                                            </button>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="time" x-model="rec.check_in_time"
                                        class="border border-gray-200 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-primary w-28">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text" x-model="rec.notes" placeholder="ملاحظة..."
                                        class="border border-gray-200 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-primary w-36">
                                </td>
                                <td class="px-4 py-3">
                                    <button @click="saveOne(rec)" :disabled="rec.saving"
                                        class="p-1.5 text-primary hover:bg-primary/10 rounded-lg transition disabled:opacity-50" title="حفظ">
                                        <svg x-show="!rec.saving" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <svg x-show="rec.saving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Toast --}}
        <div x-show="toast.show" x-transition
            :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
            class="fixed bottom-6 left-1/2 -translate-x-1/2 text-white text-sm px-5 py-3 rounded-xl shadow-lg z-50"
            x-text="toast.message">
        </div>

    </div>

    {{-- ==================== TAB: الشهري ==================== --}}
    <div x-show="activeTab === 'month'" x-transition>

        <div class="flex items-center gap-3">
            <input type="month" x-model="selectedMonth" @change="loadMonthData()"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            <span class="text-xs text-gray-500">أيام العمل: <strong x-text="workDays"></strong></span>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 mt-4">

            <div x-show="loadingMonth" class="p-10 text-center text-gray-400">
                <svg class="animate-spin h-7 w-7 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                جار التحميل...
            </div>

            <div x-show="!loadingMonth && monthRecords.length === 0" class="p-10 text-center">
                <p class="text-gray-400 text-sm">لا توجد بيانات لهذا الشهر</p>
            </div>

            <div x-show="!loadingMonth && monthRecords.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المدرس</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">حاضر</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">غائب</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">متأخر</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">إجازة</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">نسبة الحضور</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <template x-for="rec in monthRecords" :key="rec.teacher_id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                                            <span class="text-primary font-bold text-xs" x-text="rec.name.charAt(0)"></span>
                                        </div>
                                        <span class="font-medium text-gray-800 text-sm" x-text="rec.name"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium" x-text="rec.present"></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-medium" x-text="rec.absent"></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full text-xs font-medium" x-text="rec.late"></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium" x-text="rec.excuse"></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full transition-all"
                                                :class="rec.rate >= 80 ? 'bg-green-500' : rec.rate >= 60 ? 'bg-orange-500' : 'bg-red-500'"
                                                :style="`width:${rec.rate}%`">
                                            </div>
                                        </div>
                                        <span class="text-xs font-medium" :class="rec.rate >= 80 ? 'text-green-600' : rec.rate >= 60 ? 'text-orange-600' : 'text-red-600'"
                                            x-text="rec.rate + '%'">
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>{{-- end x-data --}}

@push('scripts')
<script>
window.teacherAttendanceRoutes = {
    data:    '{{ route("admin.teacher-attendance.data") }}',
    save:    '{{ route("admin.teacher-attendance.save") }}',
    saveOne: '{{ route("admin.teacher-attendance.save-one") }}',
};

function teacherAttendanceManager() {
    return {
        activeTab:    'day',
        selectedDate: new Date().toISOString().slice(0, 10),
        selectedMonth: new Date().toISOString().slice(0, 7),
        today:        new Date().toISOString().slice(0, 10),

        dayRecords:   [],
        monthRecords: [],
        stats:        null,
        workDays:     0,
        loadingDay:   false,
        loadingMonth: false,
        saving:       false,

        toast: { show: false, type: 'success', message: '' },

        statusOptions: [
            { value: 'present', label: 'حاضر',  activeClass: 'bg-green-100 text-green-700' },
            { value: 'absent',  label: 'غائب',   activeClass: 'bg-red-100 text-red-700' },
            { value: 'late',    label: 'متأخر',   activeClass: 'bg-orange-100 text-orange-700' },
            { value: 'excuse',  label: 'إجازة',  activeClass: 'bg-blue-100 text-blue-700' },
        ],

        init() {
            this.loadDayData();
        },

        async loadDayData() {
            this.loadingDay = true;
            try {
                const url = window.teacherAttendanceRoutes.data + `?date=${this.selectedDate}`;
                const r = await fetch(url);
                const d = await r.json();
                if (d.success) {
                    this.dayRecords = d.data.map(rec => ({ ...rec, saving: false }));
                    this.stats = d.stats;
                }
            } catch (_) {}
            finally { this.loadingDay = false; }
        },

        async loadMonthData() {
            this.loadingMonth = true;
            try {
                const url = window.teacherAttendanceRoutes.data + `?month=${this.selectedMonth}`;
                const r = await fetch(url);
                const d = await r.json();
                if (d.success) {
                    this.monthRecords = d.data;
                    this.workDays = d.data[0]?.work_days ?? 0;
                }
            } catch (_) {}
            finally { this.loadingMonth = false; }
        },

        markAll(status) {
            this.dayRecords.forEach(r => r.status = status);
        },

        async saveAll() {
            const toSave = this.dayRecords.filter(r => r.status !== null);
            if (toSave.length === 0) {
                this.showToast('لا توجد سجلات للحفظ', 'error');
                return;
            }
            this.saving = true;
            try {
                const r = await fetch(window.teacherAttendanceRoutes.save, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        date: this.selectedDate,
                        records: toSave.map(rec => ({
                            teacher_id:    rec.id,
                            status:        rec.status,
                            check_in_time: rec.check_in_time || null,
                            notes:         rec.notes || null,
                        })),
                    }),
                });
                const d = await r.json();
                if (d.success) {
                    this.showToast(d.message, 'success');
                    await this.loadDayData();
                } else {
                    this.showToast(d.message || 'حدث خطأ', 'error');
                }
            } catch (_) { this.showToast('حدث خطأ في الاتصال', 'error'); }
            finally { this.saving = false; }
        },

        async saveOne(rec) {
            if (!rec.status) {
                this.showToast('يرجى تحديد الحالة أولاً', 'error');
                return;
            }
            rec.saving = true;
            try {
                const r = await fetch(window.teacherAttendanceRoutes.saveOne, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        teacher_id:    rec.id,
                        date:          this.selectedDate,
                        status:        rec.status,
                        check_in_time: rec.check_in_time || null,
                        notes:         rec.notes || null,
                    }),
                });
                const d = await r.json();
                if (d.success) {
                    rec.attendance_id = d.attendance_id;
                    this.showToast('تم الحفظ', 'success');
                    this.recalcStats();
                } else {
                    this.showToast(d.message || 'حدث خطأ', 'error');
                }
            } catch (_) { this.showToast('حدث خطأ في الاتصال', 'error'); }
            finally { rec.saving = false; }
        },

        recalcStats() {
            const marked = this.dayRecords.filter(r => r.status !== null);
            this.stats = {
                total:   this.dayRecords.length,
                present: marked.filter(r => r.status === 'present').length,
                absent:  marked.filter(r => r.status === 'absent').length,
                late:    marked.filter(r => r.status === 'late').length,
                excuse:  marked.filter(r => r.status === 'excuse').length,
                unmarked: this.dayRecords.filter(r => r.status === null).length,
            };
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, type, message };
            setTimeout(() => this.toast.show = false, 3000);
        },
    };
}
</script>
@endpush

@endsection
