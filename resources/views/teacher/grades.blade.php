@extends('layouts.dashboard')

@section('sidebar-menu')
@include('teacher.partials.sidebar')
@endsection

@php
$sidebarTitle    = 'بوابة المدرسين';
$pageTitle       = 'الدرجات والتقييمات';
$pageDescription = 'إدخال التقييمات ودرجات طلابك';
@endphp

@section('content')

<div x-data="gradesManager()" x-init="init()" class="space-y-5">

    {{-- Notification --}}
    <div x-show="notification.show" x-transition.opacity
        :class="notification.type === 'success' ? 'bg-green-50 border-green-300 text-green-800' : 'bg-red-50 border-red-300 text-red-800'"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-[9999] px-5 py-3 rounded-xl border shadow-lg text-sm font-medium flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span x-text="notification.message"></span>
    </div>

    {{-- Active group info --}}
    <template x-if="currentGroup">
        <div class="bg-primary/5 border border-primary/20 rounded-xl px-4 py-3 flex flex-wrap items-center gap-4 text-sm">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                <span class="font-semibold text-primary" x-text="currentGroup.name"></span>
            </div>
            <span class="text-gray-500">الفترة الحالية للتقييم: <strong x-text="currentGroup.current_eval_period"></strong> / 8</span>
            <span class="text-gray-500">
                الامتحان النهائي:
                <strong :class="currentGroup.final_exam_active ? 'text-green-600' : 'text-gray-400'"
                    x-text="currentGroup.final_exam_active ? 'مفتوح' : 'مغلق'">
                </strong>
            </span>
        </div>
    </template>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">المجموعة</label>
                <select x-model="selectedGroupId"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary min-w-52">
                    <option value="">— اختر المجموعة —</option>
                    <template x-for="g in groups" :key="g.id">
                        <option :value="String(g.id)" x-text="g.name"></option>
                    </template>
                </select>
            </div>
            <button @click="loadGrades()" :disabled="loading || !selectedGroupId"
                class="flex items-center gap-1.5 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                <svg x-show="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span x-show="!loading">عرض الدرجات</span>
                <span x-show="loading">جار التحميل...</span>
            </button>
        </div>
    </div>

    {{-- Grades Tabs --}}
    <div x-show="dataLoaded" class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="flex border-b border-gray-200 px-4 overflow-x-auto">
            @foreach([
                ['summary','ملخص الدرجات'],
                ['evals','إدخال التقييمات'],
                ['tests','الاختبارات الشهرية'],
                ['final','الامتحان النهائي'],
            ] as [$key,$label])
            <button @click="activeTab = '{{ $key }}'"
                class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap"
                :class="activeTab==='{{ $key }}' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- ── ملخص ── --}}
        <div x-show="activeTab === 'summary'" class="overflow-x-auto">
            <div class="p-3 bg-blue-50 border-b border-blue-100 text-xs text-blue-700 flex flex-wrap gap-4">
                <span>التقييمات: <strong x-text="w('evaluations')"></strong> درجة</span>
                <span>الاختبارات: <strong x-text="w('monthly_tests')"></strong> درجة</span>
                <span>النهائي: <strong x-text="w('final_exam')"></strong> درجة</span>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">الطالب</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-blue-600">تقييمات</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">اختبار 1</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">اختبار 2</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500">اختبار 3</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-orange-600">مجموع الاختبارات</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-purple-600">نهائي</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-700">الإجمالي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="s in students" :key="s.id">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800" x-text="s.name"></td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex flex-col items-center gap-0.5">
                                    <span class="font-semibold text-blue-700" x-text="s.grades.eval_grade + ' / ' + w('evaluations')"></span>
                                    <span class="text-xs text-gray-400" x-text="s.grades.eval_count + ' / 8'"></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center" x-text="s.monthly_tests[1]?.score ?? '—'"></td>
                            <td class="px-4 py-3 text-center" x-text="s.monthly_tests[2]?.score ?? '—'"></td>
                            <td class="px-4 py-3 text-center" x-text="s.monthly_tests[3]?.score ?? '—'"></td>
                            <td class="px-4 py-3 text-center font-semibold text-orange-700"
                                x-text="s.grades.test_grade + ' / ' + w('monthly_tests')"></td>
                            <td class="px-4 py-3 text-center font-semibold text-purple-700"
                                x-text="s.grades.final_grade > 0 ? (s.grades.final_grade + ' / ' + w('final_exam')) : '—'">
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="gradeClass(s.grades.total)"
                                    class="px-3 py-1 rounded-full text-xs font-bold"
                                    x-text="s.grades.total + ' / 100'">
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- ── التقييمات ── --}}
        <div x-show="activeTab === 'evals'" class="p-4 space-y-4">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">الفترة التقييمية</label>
                    <select x-model.number="evalPeriod" @change="onEvalPeriodChange()"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        <template x-for="n in [1,2,3,4,5,6,7,8]" :key="n">
                            <option :value="n">
                                <span x-text="'الفترة ' + n + (n == currentGroup?.current_eval_period ? ' ← الحالية' : '')"></span>
                            </option>
                        </template>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto rounded-lg border border-gray-100">
                <table class="w-full text-xs">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-3 py-2.5 text-right font-medium text-gray-600 sticky right-0 bg-gray-50 min-w-36">الطالب</th>
                            <th class="px-3 py-2.5 text-center font-medium text-gray-600 min-w-28">النشاط والمشاركة</th>
                            <th class="px-3 py-2.5 text-center font-medium text-gray-600 min-w-28">السلوك والانضباط</th>
                            <th class="px-3 py-2.5 text-center font-medium text-gray-600 min-w-28">التحسن الأكاديمي</th>
                            <th class="px-3 py-2.5 text-center font-medium text-gray-600 min-w-28">الواجبات المنزلية</th>
                            <th class="px-3 py-2.5 text-center font-medium text-gray-600 min-w-28">الاختبارات القصيرة</th>
                            <th class="px-3 py-2.5 text-center font-medium text-gray-600 min-w-16">المجموع</th>
                            <th class="px-3 py-2.5 text-right font-medium text-gray-600 min-w-40">ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 bg-white">
                        <template x-for="s in students" :key="s.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-medium text-gray-800 sticky right-0 bg-white" x-text="s.name"></td>
                                <template x-for="field in ['activity_participation','behavior_discipline','academic_improvement','homework','short_tests']" :key="field">
                                    <td class="px-2 py-2 text-center">
                                        <div class="flex justify-center gap-0.5" dir="ltr">
                                            <template x-for="n in [1,2,3,4,5]" :key="n">
                                                <button type="button"
                                                    @click="evalInputs[s.id] && (evalInputs[s.id][field] = n)"
                                                    :class="evalInputs[s.id]?.[field] >= n ? 'bg-primary text-white' : 'bg-gray-100 text-gray-400 hover:bg-gray-200'"
                                                    class="w-6 h-6 text-xs rounded transition font-medium">
                                                    <span x-text="n"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </td>
                                </template>
                                <td class="px-2 py-2 text-center font-bold text-primary">
                                    <span x-text="evalInputs[s.id] ? (evalInputs[s.id].activity_participation+evalInputs[s.id].behavior_discipline+evalInputs[s.id].academic_improvement+evalInputs[s.id].homework+evalInputs[s.id].short_tests) : 0"></span>
                                    <span class="text-gray-300">/25</span>
                                </td>
                                <td class="px-2 py-2">
                                    <input type="text" x-model="evalInputs[s.id].notes" placeholder="اختياري"
                                        class="w-full border border-gray-200 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-primary">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <button @click="saveEvals()" :disabled="savingEval"
                class="px-5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                <span x-show="!savingEval">حفظ تقييمات الفترة <span x-text="evalPeriod"></span></span>
                <span x-show="savingEval">جار الحفظ...</span>
            </button>
        </div>

        {{-- ── الاختبارات ── --}}
        <div x-show="activeTab === 'tests'" class="p-4 space-y-4">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">رقم الاختبار</label>
                    <select x-model.number="testNumber" @change="onTestNumberChange()"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        <option :value="1">الاختبار الأول</option>
                        <option :value="2">الاختبار الثاني</option>
                        <option :value="3">الاختبار الثالث</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">الشهر</label>
                    <input type="month" x-model="testMonth"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>
            <div class="overflow-x-auto rounded-lg border border-gray-100">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-600">الطالب</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-600">الدرجة (من 20)</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-600">ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 bg-white">
                        <template x-for="s in students" :key="s.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 font-medium text-gray-800" x-text="s.name"></td>
                                <td class="px-4 py-2.5 text-center">
                                    <input type="number" x-model="testInputs[s.id].score"
                                        min="0" max="20" step="0.5"
                                        class="w-20 border border-gray-200 rounded-lg px-2 py-1 text-center text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                </td>
                                <td class="px-4 py-2.5">
                                    <input type="text" x-model="testInputs[s.id].notes" placeholder="اختياري"
                                        class="w-full border border-gray-200 rounded-lg px-3 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-primary">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <button @click="saveTests()" :disabled="savingTests"
                class="px-5 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition disabled:opacity-60">
                <span x-show="!savingTests">حفظ الاختبار <span x-text="testNumber"></span></span>
                <span x-show="savingTests">جار الحفظ...</span>
            </button>
        </div>

        {{-- ── الامتحان النهائي ── --}}
        <div x-show="activeTab === 'final'" class="p-4 space-y-4">
            <div x-show="!currentGroup?.final_exam_active"
                class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                الامتحان النهائي لم يُفتح بعد — يرجى التواصل مع الإدارة
            </div>
            <template x-if="currentGroup?.final_exam_active">
                <div class="space-y-4">
                    <p class="text-xs text-gray-500 bg-gray-50 rounded-lg p-2">
                        الدرجة القصوى: <strong class="text-purple-700" x-text="w('final_exam')"></strong> درجة
                    </p>
                    <div class="overflow-x-auto rounded-lg border border-gray-100">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-600">الطالب</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-600">الدرجة</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-600">ملاحظات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 bg-white">
                                <template x-for="s in students" :key="s.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2.5 font-medium text-gray-800" x-text="s.name"></td>
                                        <td class="px-4 py-2.5 text-center">
                                            <input type="number" x-model="finalInputs[s.id].score"
                                                min="0" :max="w('final_exam')" step="0.5"
                                                class="w-20 border border-gray-200 rounded-lg px-2 py-1 text-center text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <input type="text" x-model="finalInputs[s.id].notes" placeholder="اختياري"
                                                class="w-full border border-gray-200 rounded-lg px-3 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-primary">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <button @click="saveFinal()" :disabled="savingFinal"
                        class="px-5 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition disabled:opacity-60">
                        <span x-show="!savingFinal">حفظ درجات الامتحان النهائي</span>
                        <span x-show="savingFinal">جار الحفظ...</span>
                    </button>
                </div>
            </template>
        </div>

    </div>

</div>

@push('scripts')
<script>
window.gradesRoutes = {
    groups:    '{{ route("teacher.grades.groups") }}',
    data:      '{{ route("teacher.grades.data") }}',
    saveEvals: '{{ route("teacher.grades.evaluations.save") }}',
    saveTests: '{{ route("teacher.grades.tests.save") }}',
    saveFinal: '{{ route("teacher.grades.final.save") }}',
};

function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }

function gradesManager() {
    return {
        groups: [], currentGroup: null,
        selectedGroupId: '',
        loading: false, dataLoaded: false,
        students: [],
        activeTab: 'summary',
        notification: { show: false, message: '', type: 'success' },
        evalPeriod: 1, evalInputs: {}, savingEval: false,
        testNumber: 1, testMonth: new Date().toISOString().slice(0,7), testInputs: {}, savingTests: false,
        finalInputs: {}, savingFinal: false,

        notify(msg, type = 'success') {
            this.notification = { show: true, message: msg, type };
            setTimeout(() => this.notification.show = false, 3500);
        },

        w(key) {
            return this.currentGroup?.grade_weights?.[key] ?? ({evaluations:20, monthly_tests:30, final_exam:50}[key]);
        },

        gradeClass(total) {
            if (total >= 90) return 'bg-green-100 text-green-800';
            if (total >= 75) return 'bg-blue-100 text-blue-800';
            if (total >= 60) return 'bg-yellow-100 text-yellow-800';
            return 'bg-red-100 text-red-800';
        },

        async init() {
            await this.loadGroups();
        },

        async loadGroups() {
            try {
                const r = await fetch(window.gradesRoutes.groups);
                const d = await r.json();
                if (d.success) this.groups = d.groups;
            } catch(_) {}
        },

        async loadGrades() {
            if (!this.selectedGroupId) return;
            this.loading = true; this.dataLoaded = false;
            try {
                const p = new URLSearchParams({ group_id: this.selectedGroupId });
                const r = await fetch(`${window.gradesRoutes.data}?${p}`);
                const d = await r.json();
                if (d.success) {
                    this.currentGroup = d.group;
                    this.students = d.students;
                    this.dataLoaded = true;
                    this.initInputs();
                    this.activeTab = 'summary';
                } else {
                    this.notify(d.message, 'error');
                }
            } catch(_) { this.notify('خطأ في الاتصال', 'error'); }
            finally { this.loading = false; }
        },

        initInputs() {
            const ev = {}, ts = {}, fn = {};
            this.students.forEach(s => {
                const e = s.evaluations[this.evalPeriod];
                ev[s.id] = e
                    ? { activity_participation: e.activity_participation, behavior_discipline: e.behavior_discipline, academic_improvement: e.academic_improvement, homework: e.homework, short_tests: e.short_tests, notes: e.notes || '' }
                    : { activity_participation: 3, behavior_discipline: 3, academic_improvement: 3, homework: 3, short_tests: 3, notes: '' };
                const t = s.monthly_tests[this.testNumber];
                ts[s.id] = { score: t?.score ?? '', notes: t?.notes ?? '' };
                fn[s.id] = { score: s.final_exam?.score ?? '', notes: s.final_exam?.notes ?? '' };
            });
            this.evalInputs = ev; this.testInputs = ts; this.finalInputs = fn;
        },

        onEvalPeriodChange() {
            const ev = {};
            this.students.forEach(s => {
                const e = s.evaluations[this.evalPeriod];
                ev[s.id] = e
                    ? { activity_participation: e.activity_participation, behavior_discipline: e.behavior_discipline, academic_improvement: e.academic_improvement, homework: e.homework, short_tests: e.short_tests, notes: e.notes || '' }
                    : { activity_participation: 3, behavior_discipline: 3, academic_improvement: 3, homework: 3, short_tests: 3, notes: '' };
            });
            this.evalInputs = ev;
        },

        onTestNumberChange() {
            const ts = {};
            this.students.forEach(s => {
                const t = s.monthly_tests[this.testNumber];
                ts[s.id] = { score: t?.score ?? '', notes: t?.notes ?? '' };
            });
            this.testInputs = ts;
        },

        async saveEvals() {
            this.savingEval = true;
            try {
                const evaluations = this.students.map(s => ({ student_id: s.id, ...this.evalInputs[s.id] }));
                const r = await fetch(window.gradesRoutes.saveEvals, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body: JSON.stringify({ group_id: this.selectedGroupId, eval_number: this.evalPeriod, evaluations })
                });
                const d = await r.json();
                if (d.success) { this.notify(d.message); await this.loadGrades(); }
                else this.notify(d.errors ? Object.values(d.errors).flat()[0] : d.message, 'error');
            } catch(_) { this.notify('خطأ في الاتصال', 'error'); }
            finally { this.savingEval = false; }
        },

        async saveTests() {
            this.savingTests = true;
            try {
                const scores = this.students.filter(s => this.testInputs[s.id]?.score !== '').map(s => ({ student_id: s.id, ...this.testInputs[s.id] }));
                const r = await fetch(window.gradesRoutes.saveTests, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body: JSON.stringify({ group_id: this.selectedGroupId, test_number: this.testNumber, month: this.testMonth, scores })
                });
                const d = await r.json();
                if (d.success) { this.notify(d.message); await this.loadGrades(); }
                else this.notify(d.errors ? Object.values(d.errors).flat()[0] : d.message, 'error');
            } catch(_) { this.notify('خطأ في الاتصال', 'error'); }
            finally { this.savingTests = false; }
        },

        async saveFinal() {
            this.savingFinal = true;
            try {
                const scores = this.students.filter(s => this.finalInputs[s.id]?.score !== '').map(s => ({ student_id: s.id, ...this.finalInputs[s.id] }));
                const r = await fetch(window.gradesRoutes.saveFinal, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body: JSON.stringify({ group_id: this.selectedGroupId, scores })
                });
                const d = await r.json();
                if (d.success) { this.notify(d.message); await this.loadGrades(); }
                else this.notify(d.message, 'error');
            } catch(_) { this.notify('خطأ في الاتصال', 'error'); }
            finally { this.savingFinal = false; }
        },
    };
}
</script>
@endpush

@endsection
