@extends('layouts.dashboard')

@section('sidebar-menu')
@include('teacher.partials.sidebar')
@endsection

@php
$sidebarTitle    = 'بوابة المدرسين';
$pageTitle       = 'تقاريري';
$pageDescription = 'إحصائيات الحضور والمحاضرات لمجموعاتك';
@endphp

@section('content')

<div x-data="reportsManager()" x-init="init()" class="space-y-5">

    {{-- Notification --}}
    <div x-show="notification.show" x-transition.opacity
        :class="notification.type === 'success' ? 'bg-green-50 border-green-300 text-green-800' : 'bg-red-50 border-red-300 text-red-800'"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-[9999] px-5 py-3 rounded-xl border shadow-lg text-sm font-medium flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span x-text="notification.message"></span>
    </div>

    {{-- Month selector --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex flex-wrap items-center gap-3">
        <label class="text-sm font-medium text-gray-600">الشهر:</label>
        <input type="month" x-model="selectedMonth" @change="loadData()"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
        <div x-show="loading" class="flex items-center gap-2 text-xs text-gray-400">
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            جار التحميل...
        </div>
    </div>

    {{-- No data --}}
    <div x-show="!loading && overview && overview.lectures_total === 0"
        class="bg-white rounded-xl shadow-sm border border-gray-100 p-10 text-center">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-gray-400 text-sm">لا توجد محاضرات في هذا الشهر</p>
    </div>

    <template x-if="!loading && overview && overview.lectures_total > 0">
        <div class="space-y-5">

            {{-- Overview cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <p class="text-xs text-gray-400 mb-1">إجمالي المحاضرات</p>
                    <p class="text-2xl font-bold text-gray-800" x-text="overview.lectures_total"></p>
                    <div class="mt-2 flex gap-2 text-xs flex-wrap">
                        <span class="text-green-600" x-text="overview.completed + ' مكتملة'"></span>
                        <span class="text-orange-500" x-text="overview.scheduled + ' مجدولة'"></span>
                        <span x-show="overview.cancelled > 0" class="text-red-400" x-text="overview.cancelled + ' ملغاة'"></span>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <p class="text-xs text-gray-400 mb-1">نسبة الحضور العامة</p>
                    <p class="text-2xl font-bold"
                        :class="overview.attendance_rate >= 75 ? 'text-green-600' : overview.attendance_rate >= 50 ? 'text-orange-500' : 'text-red-600'"
                        x-text="overview.attendance_rate + '%'">
                    </p>
                    <div class="mt-2 w-full bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full transition-all"
                            :class="overview.attendance_rate >= 75 ? 'bg-green-500' : overview.attendance_rate >= 50 ? 'bg-orange-400' : 'bg-red-400'"
                            :style="'width:' + Math.min(overview.attendance_rate, 100) + '%'">
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <p class="text-xs text-gray-400 mb-1">المجموعات</p>
                    <p class="text-2xl font-bold text-primary" x-text="overview.groups_count"></p>
                    <p class="text-xs text-gray-400 mt-2">مجموعة نشطة</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <p class="text-xs text-gray-400 mb-1">إجمالي الطلاب</p>
                    <p class="text-2xl font-bold text-gray-700" x-text="overview.students_count"></p>
                    <p class="text-xs text-gray-400 mt-2">طالب في مجموعاتك</p>
                </div>
            </div>

            {{-- Groups attendance table --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700">الحضور حسب المجموعة</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المجموعة</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">المرحلة</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">الطلاب</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">المحاضرات</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-green-600">حاضر</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-red-500">غائب</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-yellow-600">متأخر</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-700">نسبة الحضور</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <template x-for="g in groupsStats" :key="g.id">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800" x-text="g.name"></td>
                                    <td class="px-4 py-3 text-center text-gray-500 text-xs" x-text="g.grade_level || '—'"></td>
                                    <td class="px-4 py-3 text-center text-gray-600" x-text="g.students_count"></td>
                                    <td class="px-4 py-3 text-center text-gray-600" x-text="g.lectures_count"></td>
                                    <td class="px-4 py-3 text-center font-medium text-green-700" x-text="g.present"></td>
                                    <td class="px-4 py-3 text-center font-medium text-red-500" x-text="g.absent"></td>
                                    <td class="px-4 py-3 text-center font-medium text-yellow-600" x-text="g.late"></td>
                                    <td class="px-4 py-3 text-center">
                                        <template x-if="g.attendance_rate !== null">
                                            <div class="flex items-center justify-center gap-2">
                                                <div class="w-16 bg-gray-100 rounded-full h-1.5">
                                                    <div class="h-1.5 rounded-full"
                                                        :class="g.attendance_rate >= 75 ? 'bg-green-500' : g.attendance_rate >= 50 ? 'bg-orange-400' : 'bg-red-400'"
                                                        :style="'width:' + Math.min(g.attendance_rate, 100) + '%'">
                                                    </div>
                                                </div>
                                                <span class="text-xs font-semibold"
                                                    :class="g.attendance_rate >= 75 ? 'text-green-700' : g.attendance_rate >= 50 ? 'text-orange-600' : 'text-red-600'"
                                                    x-text="g.attendance_rate + '%'">
                                                </span>
                                            </div>
                                        </template>
                                        <span x-if="g.attendance_rate === null" class="text-xs text-gray-300">—</span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Low attendance students --}}
            <div x-show="lowAttendance.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="text-sm font-semibold text-gray-700">طلاب بحضور منخفض
                        <span class="text-xs font-normal text-gray-400">(أقل من 75%)</span>
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">الطالب</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المجموعة</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-red-500">الغيابات</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">من أصل</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-700">نسبة الحضور</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <template x-for="(s, i) in lowAttendance" :key="i">
                                <tr class="hover:bg-red-50/30 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800" x-text="s.name"></td>
                                    <td class="px-4 py-3 text-gray-500 text-xs" x-text="s.group_name"></td>
                                    <td class="px-4 py-3 text-center font-bold text-red-600" x-text="s.absences"></td>
                                    <td class="px-4 py-3 text-center text-gray-400" x-text="s.total + ' محاضرة'"></td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                            :class="s.rate >= 60 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700'"
                                            x-text="s.rate + '%'">
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- No low attendance --}}
            <div x-show="lowAttendance.length === 0"
                class="bg-green-50 border border-green-100 rounded-xl p-4 flex items-center gap-3 text-sm text-green-700">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                لا يوجد طلاب بحضور منخفض هذا الشهر — أداء ممتاز!
            </div>

        </div>
    </template>

</div>

@push('scripts')
<script>
window.reportsRoutes = {
    data: '{{ route("teacher.reports.data") }}',
};

function reportsManager() {
    return {
        selectedMonth: new Date().toISOString().slice(0, 7),
        loading: false,
        overview: null,
        groupsStats: [],
        lowAttendance: [],
        notification: { show: false, message: '', type: 'success' },

        notify(msg, type = 'success') {
            this.notification = { show: true, message: msg, type };
            setTimeout(() => this.notification.show = false, 3500);
        },

        async init() {
            await this.loadData();
        },

        async loadData() {
            this.loading = true;
            try {
                const r = await fetch(`${window.reportsRoutes.data}?month=${this.selectedMonth}`);
                const d = await r.json();
                if (d.success) {
                    this.overview      = d.overview;
                    this.groupsStats   = d.groups_stats;
                    this.lowAttendance = d.low_attendance;
                } else {
                    this.notify('خطأ في تحميل البيانات', 'error');
                }
            } catch(_) {
                this.notify('خطأ في الاتصال', 'error');
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endpush

@endsection
