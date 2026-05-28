@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle    = 'البوابة الإدارية';
$pageTitle       = 'الأرشيف';
$pageDescription = 'سجل المجموعات المنتهية والبيانات التاريخية';
@endphp

@section('content')

<div x-data="archiveManager()" x-init="init()" class="space-y-5">

    {{-- Notification --}}
    <div x-show="notification.show" x-transition.opacity
        :class="notification.type==='success' ? 'bg-green-50 border-green-300 text-green-800' : 'bg-red-50 border-red-300 text-red-800'"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-[9999] px-5 py-3 rounded-xl border shadow-lg text-sm font-medium flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span x-text="notification.message"></span>
    </div>

    {{-- Archived Groups List --}}
    <div>
        <h2 class="text-sm font-semibold text-gray-600 mb-3">المجموعات المؤرشفة</h2>

        <div x-show="archivedGroups.length === 0" class="bg-white rounded-xl border border-gray-100 shadow-sm p-10 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            <p class="text-gray-400 text-sm">لا توجد مجموعات مؤرشفة بعد</p>
            <p class="text-xs text-gray-300 mt-1">يمكن أرشفة المجموعات من صفحة الدرجات والتقييمات</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <template x-for="g in archivedGroups" :key="g.id">
                <div @click="selectGroup(g)"
                    :class="selectedGroup?.id === g.id ? 'border-primary ring-2 ring-primary/20' : 'border-gray-100 hover:border-primary/30'"
                    class="bg-white rounded-xl border shadow-sm p-5 cursor-pointer transition-all">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800" x-text="g.name"></h3>
                            <p class="text-xs text-gray-400 mt-0.5" x-text="g.grade_level"></p>
                        </div>
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs">مؤرشف</span>
                    </div>
                    <div class="mt-3 flex items-center gap-3 text-xs text-gray-400">
                        <span x-text="g.start_date || '—'"></span>
                        <span>←</span>
                        <span x-text="g.end_date || 'مفتوح'"></span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-xs"
                            x-text="'تقييمات: ' + (g.grade_weights?.evaluations ?? 20)"></span>
                        <span class="px-2 py-0.5 bg-orange-50 text-orange-600 rounded text-xs"
                            x-text="'اختبارات: ' + (g.grade_weights?.monthly_tests ?? 30)"></span>
                        <span class="px-2 py-0.5 bg-purple-50 text-purple-600 rounded text-xs"
                            x-text="'نهائي: ' + (g.grade_weights?.final_exam ?? 50)"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Selected Group Detail --}}
    <template x-if="selectedGroup">
        <div class="space-y-5">

            {{-- Group Header --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800" x-text="selectedGroup.name"></h2>
                        <p class="text-sm text-gray-400">
                            من <span x-text="selectedGroup.start_date || '—'"></span>
                            <span x-show="selectedGroup.end_date"> إلى <span x-text="selectedGroup.end_date"></span></span>
                        </p>
                    </div>
                    {{-- Stats --}}
                    <div x-show="stats" class="flex flex-wrap gap-4 text-center">
                        <div class="bg-gray-50 rounded-lg p-3 min-w-20">
                            <p class="text-xl font-bold text-gray-700" x-text="stats?.lecture_count ?? 0"></p>
                            <p class="text-xs text-gray-400">محاضرة</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-3 min-w-20">
                            <p class="text-xl font-bold text-blue-700" x-text="stats?.students_evaluated ?? 0"></p>
                            <p class="text-xs text-gray-400">لهم تقييمات</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-3 min-w-20">
                            <p class="text-xl font-bold text-orange-700" x-text="stats?.students_tested ?? 0"></p>
                            <p class="text-xs text-gray-400">لهم اختبارات</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-3 min-w-20">
                            <p class="text-xl font-bold text-purple-700" x-text="stats?.students_final ?? 0"></p>
                            <p class="text-xs text-gray-400">لهم نهائي</p>
                        </div>
                    </div>
                </div>

                {{-- Grade Distribution --}}
                <div x-show="stats?.grade_distribution" class="mt-4 grid grid-cols-4 gap-2">
                    <div class="bg-green-50 rounded-lg p-2 text-center">
                        <p class="text-lg font-bold text-green-700" x-text="stats?.grade_distribution?.excellent ?? 0"></p>
                        <p class="text-xs text-green-600">ممتاز 90+</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-2 text-center">
                        <p class="text-lg font-bold text-blue-700" x-text="stats?.grade_distribution?.good ?? 0"></p>
                        <p class="text-xs text-blue-600">جيد 75-89</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-2 text-center">
                        <p class="text-lg font-bold text-yellow-700" x-text="stats?.grade_distribution?.pass ?? 0"></p>
                        <p class="text-xs text-yellow-600">مقبول 60-74</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-2 text-center">
                        <p class="text-lg font-bold text-red-700" x-text="stats?.grade_distribution?.fail ?? 0"></p>
                        <p class="text-xs text-red-600">راسب أقل من 60</p>
                    </div>
                </div>
            </div>

            {{-- Grades table (read-only) --}}
            <div x-show="gradesLoaded" class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-x-auto">
                <div class="p-3 bg-blue-50 border-b border-blue-100 text-xs text-blue-700 flex flex-wrap gap-4">
                    <span>التقييمات: <strong x-text="selectedGroup.grade_weights?.evaluations ?? 20"></strong> درجة</span>
                    <span>الاختبارات: <strong x-text="selectedGroup.grade_weights?.monthly_tests ?? 30"></strong> درجة</span>
                    <span>النهائي: <strong x-text="selectedGroup.grade_weights?.final_exam ?? 50"></strong> درجة</span>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">الطالب</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-blue-600">التقييمات</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-400">اختبار 1</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-400">اختبار 2</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-400">اختبار 3</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-orange-600">مجموع الاختبارات</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-purple-600">النهائي</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-700">الإجمالي / 100</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <template x-for="s in students" :key="s.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800" x-text="s.name"></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-blue-700 font-semibold"
                                        x-text="s.grades.eval_grade + ' / ' + (selectedGroup.grade_weights?.evaluations ?? 20)"></span>
                                    <span class="text-xs text-gray-400 block" x-text="'(' + s.grades.eval_count + '/8)'"></span>
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600" x-text="s.monthly_tests[1]?.score ?? '—'"></td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600" x-text="s.monthly_tests[2]?.score ?? '—'"></td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600" x-text="s.monthly_tests[3]?.score ?? '—'"></td>
                                <td class="px-4 py-3 text-center font-semibold text-orange-700"
                                    x-text="s.grades.test_grade + ' / ' + (selectedGroup.grade_weights?.monthly_tests ?? 30)"></td>
                                <td class="px-4 py-3 text-center font-semibold text-purple-700"
                                    x-text="s.grades.final_grade > 0 ? (s.grades.final_grade + ' / ' + (selectedGroup.grade_weights?.final_exam ?? 50)) : '—'">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span :class="gradeClass(s.grades.total)"
                                        class="px-3 py-1 rounded-full text-xs font-bold"
                                        x-text="s.grades.total + ' / 100'">
                                    </span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="students.length === 0">
                            <td colspan="8" class="px-4 py-6 text-center text-gray-400 text-sm">لا يوجد طلاب في هذه المجموعة</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </template>

</div>

@push('scripts')
<script>
window.archiveRoutes = {
    archivedGroups: '{{ route("admin.archive.groups") }}',
    gradesData:     '{{ route("admin.grades.data") }}',
    archiveStats:   '{{ route("admin.archive.stats") }}',
};

function archiveManager() {
    return {
        archivedGroups: [],
        selectedGroup:  null,
        students:       [],
        stats:          null,
        gradesLoaded:   false,
        notification:   { show: false, message: '', type: 'success' },

        notify(msg, type = 'success') {
            this.notification = { show: true, message: msg, type };
            setTimeout(() => this.notification.show = false, 3500);
        },

        gradeClass(total) {
            if (total >= 90) return 'bg-green-100 text-green-800';
            if (total >= 75) return 'bg-blue-100 text-blue-800';
            if (total >= 60) return 'bg-yellow-100 text-yellow-800';
            return 'bg-red-100 text-red-800';
        },

        async init() {
            await this.loadArchivedGroups();
        },

        async loadArchivedGroups() {
            try {
                const r = await fetch(window.archiveRoutes.archivedGroups);
                const d = await r.json();
                if (d.success) this.archivedGroups = d.groups;
            } catch (_) {}
        },

        async selectGroup(g) {
            this.selectedGroup = g;
            this.students      = [];
            this.gradesLoaded  = false;
            this.stats         = null;

            try {
                const r = await fetch(`${window.archiveRoutes.archiveStats}?group_id=${g.id}`);
                const d = await r.json();
                if (d.success) this.stats = d.stats;
            } catch (_) {}

            try {
                const p = new URLSearchParams({ group_id: g.id });
                const r = await fetch(`${window.archiveRoutes.gradesData}?${p}`);
                const d = await r.json();
                if (d.success) {
                    this.students    = d.students;
                    this.gradesLoaded = true;
                }
            } catch (_) {}
        },
    };
}
</script>
@endpush

@endsection
