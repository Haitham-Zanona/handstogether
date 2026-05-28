@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle    = 'البوابة الإدارية';
$pageTitle       = 'الدرجات والتقييمات';
$pageDescription = 'إدارة درجات الطلاب والتقييمات الدورية';
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

    {{-- Groups Manager --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-700">إعدادات الدرجات لكل مجموعة</h3>
        </div>

        <div x-show="groups.length === 0" class="text-center py-4 text-xs text-gray-400">
            لا توجد مجموعات نشطة — أنشئ مجموعات من صفحة المجموعات
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs" x-show="groups.length > 0">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-3 py-2 text-right font-medium text-gray-500">المجموعة</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500">المرحلة</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500">الأوزان (تقييمات/اختبارات/نهائي)</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500">الامتحان النهائي</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="g in groups" :key="g.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-3 py-2 font-medium text-gray-800" x-text="g.name"></td>
                            <td class="px-3 py-2 text-gray-500" x-text="g.grade_level"></td>
                            <td class="px-3 py-2 text-gray-500">
                                <span x-text="g.grade_weights?.evaluations ?? 20" class="text-blue-600 font-medium"></span>/
                                <span x-text="g.grade_weights?.monthly_tests ?? 30" class="text-orange-600 font-medium"></span>/
                                <span x-text="g.grade_weights?.final_exam ?? 50" class="text-purple-600 font-medium"></span>
                            </td>
                            <td class="px-3 py-2">
                                <button @click="toggleFinalExam(g)"
                                    :class="g.final_exam_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                    class="px-2 py-0.5 rounded-full text-xs transition hover:opacity-80">
                                    <span x-text="g.final_exam_active ? 'مفتوح' : 'مغلق'"></span>
                                </button>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-1">
                                    <button @click="openSettings(g)"
                                        class="p-1 text-blue-500 hover:bg-blue-50 rounded transition" title="إعدادات الأوزان">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </button>
                                    <button @click="archiveGroup(g)"
                                        class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded text-xs hover:bg-gray-200 transition" title="أرشفة المجموعة">أرشفة</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">المجموعة</label>
                <select x-model="selectedGroupId"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary min-w-52">
                    <option value="">— اختر المجموعة —</option>
                    <template x-for="g in groups" :key="g.id">
                        <option :value="String(g.id)" x-text="g.name + ' (' + (g.grade_level ?? '') + ')'"></option>
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

        {{-- Tab Nav --}}
        <div class="flex border-b border-gray-200 px-4 overflow-x-auto">
            @foreach([
                ['summary', 'ملخص الدرجات'],
                ['evals', 'إدخال التقييمات'],
                ['tests', 'الاختبارات الشهرية'],
                ['final', 'الامتحان النهائي'],
            ] as [$key, $label])
            <button @click="activeTab = '{{ $key }}'"
                class="px-4 py-3 text-sm font-medium border-b-2 transition whitespace-nowrap"
                :class="activeTab === '{{ $key }}' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700'">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- ──────── TAB: ملخص الدرجات ──────── --}}
        <div x-show="activeTab === 'summary'" class="overflow-x-auto">
            <div class="p-3 bg-blue-50 border-b border-blue-100 text-xs text-blue-700 flex flex-wrap gap-4">
                <span>التقييمات: <strong x-text="w('evaluations')"></strong> درجة</span>
                <span>الاختبارات: <strong x-text="w('monthly_tests')"></strong> درجة</span>
                <span>الامتحان النهائي: <strong x-text="w('final_exam')"></strong> درجة</span>
                <span class="text-blue-500">الفترة الحالية للتقييم: <strong x-text="currentGroup?.current_eval_period ?? '—'"></strong> / 8</span>
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
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-700">الإجمالي / 100</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="s in students" :key="s.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 font-medium text-gray-800" x-text="s.name"></td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex flex-col items-center gap-0.5">
                                    <span class="font-semibold text-blue-700" x-text="s.grades.eval_grade + ' / ' + w('evaluations')"></span>
                                    <span class="text-xs text-gray-400" x-text="s.grades.eval_count + ' / 8 فترات'"></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-sm" x-text="s.monthly_tests[1]?.score ?? '—'"></td>
                            <td class="px-4 py-3 text-center text-sm" x-text="s.monthly_tests[2]?.score ?? '—'"></td>
                            <td class="px-4 py-3 text-center text-sm" x-text="s.monthly_tests[3]?.score ?? '—'"></td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-semibold text-orange-700" x-text="s.grades.test_grade + ' / ' + w('monthly_tests')"></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="font-semibold text-purple-700"
                                    x-text="s.grades.final_grade > 0 ? (s.grades.final_grade + ' / ' + w('final_exam')) : '—'">
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="gradeClass(s.grades.total)"
                                    class="px-3 py-1 rounded-full text-xs font-bold"
                                    x-text="s.grades.total">
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- ──────── TAB: إدخال التقييمات ──────── --}}
        <div x-show="activeTab === 'evals'" class="p-4 space-y-4">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">الفترة التقييمية</label>
                    <select x-model.number="evalPeriod" @change="onEvalPeriodChange()"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        <template x-for="n in [1,2,3,4,5,6,7,8]" :key="n">
                            <option :value="n" x-text="'الفترة ' + n"></option>
                        </template>
                    </select>
                </div>
                <div class="text-xs text-gray-500 pt-5">
                    الفترة الحالية: <strong class="text-primary" x-text="currentGroup?.current_eval_period ?? 1"></strong>
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
                            <th class="px-3 py-2.5 text-center font-medium text-gray-600 min-w-20">المجموع</th>
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
                                    <span x-text="evalInputs[s.id] ? (evalInputs[s.id].activity_participation + evalInputs[s.id].behavior_discipline + evalInputs[s.id].academic_improvement + evalInputs[s.id].homework + evalInputs[s.id].short_tests) : 0"></span>
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
            <div class="flex items-center gap-3">
                <button @click="saveEvals()" :disabled="savingEval"
                    class="px-5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                    <span x-show="!savingEval">حفظ تقييمات الفترة <span x-text="evalPeriod"></span></span>
                    <span x-show="savingEval">جار الحفظ...</span>
                </button>
                <span class="text-xs text-gray-400">المجموع الكلي لجميع الفترات الـ 8 ÷ 10 = درجة من <span x-text="w('evaluations')"></span></span>
            </div>
        </div>

        {{-- ──────── TAB: الاختبارات الشهرية ──────── --}}
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
                                        min="0" max="20" step="0.5" placeholder="0"
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
            <div class="flex items-center gap-3">
                <button @click="saveTests()" :disabled="savingTests"
                    class="px-5 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition disabled:opacity-60">
                    <span x-show="!savingTests">حفظ الاختبار <span x-text="testNumber"></span></span>
                    <span x-show="savingTests">جار الحفظ...</span>
                </button>
                <span class="text-xs text-gray-400">مجموع الثلاثة اختبارات ÷ 2 = درجة من <span x-text="w('monthly_tests')"></span></span>
            </div>
        </div>

        {{-- ──────── TAB: الامتحان النهائي ──────── --}}
        <div x-show="activeTab === 'final'" class="p-4 space-y-4">
            <div x-show="!currentGroup?.final_exam_active"
                class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                الامتحان النهائي غير مفتوح — فعّله من جدول المجموعات بالضغط على زر الحالة
            </div>
            <div x-show="currentGroup?.final_exam_active">
                <div class="mb-3 text-xs text-gray-500 bg-gray-50 rounded-lg p-2">
                    الدرجة القصوى: <strong class="text-purple-700" x-text="w('final_exam')"></strong> درجة
                </div>
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
                                            min="0" :max="w('final_exam')" step="0.5" placeholder="0"
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
                <div class="mt-3">
                    <button @click="saveFinal()" :disabled="savingFinal"
                        class="px-5 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition disabled:opacity-60">
                        <span x-show="!savingFinal">حفظ درجات الامتحان النهائي</span>
                        <span x-show="savingFinal">جار الحفظ...</span>
                    </button>
                </div>
            </div>
        </div>

    </div>{{-- end tabs --}}

    {{-- ===================== MODAL: Group Grade Settings ===================== --}}
    <div x-show="showSettingsModal" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @keydown.escape.window="showSettingsModal = false">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md" @click.stop>
            <div class="flex items-center justify-between px-5 py-4 border-b">
                <h3 class="font-bold text-gray-800 text-base">
                    إعدادات الدرجات — <span x-text="settingsGroup?.name"></span>
                </h3>
                <button @click="showSettingsModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">تاريخ البداية</label>
                        <input type="date" x-model="settingsForm.start_date"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">تاريخ النهاية</label>
                        <input type="date" x-model="settingsForm.end_date"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">توزيع الدرجات (يجب أن يساوي 100)</label>
                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-blue-50 rounded-lg p-2 text-center">
                            <p class="text-xs text-blue-600 mb-1">التقييمات</p>
                            <input type="number" x-model.number="settingsForm.grade_weights.evaluations" min="1" max="98"
                                class="w-full border border-blue-200 rounded px-2 py-1 text-center text-sm font-bold text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div class="bg-orange-50 rounded-lg p-2 text-center">
                            <p class="text-xs text-orange-600 mb-1">الاختبارات</p>
                            <input type="number" x-model.number="settingsForm.grade_weights.monthly_tests" min="1" max="98"
                                class="w-full border border-orange-200 rounded px-2 py-1 text-center text-sm font-bold text-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-400">
                        </div>
                        <div class="bg-purple-50 rounded-lg p-2 text-center">
                            <p class="text-xs text-purple-600 mb-1">النهائي</p>
                            <input type="number" x-model.number="settingsForm.grade_weights.final_exam" min="1" max="98"
                                class="w-full border border-purple-200 rounded px-2 py-1 text-center text-sm font-bold text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-400">
                        </div>
                    </div>
                    <div class="mt-2 text-center text-xs"
                        :class="weightsSum() === 100 ? 'text-green-600' : 'text-red-600'">
                        المجموع: <strong x-text="weightsSum()"></strong> / 100
                        <span x-show="weightsSum() !== 100"> ⚠ يجب أن يساوي 100</span>
                    </div>
                </div>
            </div>
            <div class="flex gap-3 px-5 pb-5">
                <button @click="saveSettings()" :disabled="savingSettings"
                    class="flex-1 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
                    <span x-show="!savingSettings">حفظ الإعدادات</span>
                    <span x-show="savingSettings">جار الحفظ...</span>
                </button>
                <button @click="showSettingsModal = false"
                    class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">
                    إلغاء
                </button>
            </div>
        </div>
    </div>

</div>{{-- end x-data --}}

@push('scripts')
<script>
window.gradesRoutes = {
    groups:      '{{ route("admin.grades.groups") }}',
    data:        '{{ route("admin.grades.data") }}',
    saveEvals:   '{{ route("admin.grades.evaluations.save") }}',
    saveTests:   '{{ route("admin.grades.tests.save") }}',
    saveFinal:   '{{ route("admin.grades.final.save") }}',
    groupSettings:  '{{ url("/admin/grades/groups") }}',
    groupFinalExam: '{{ url("/admin/grades/groups") }}',
    groupArchive:   '{{ url("/admin/grades/groups") }}',
};

function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }

function gradesManager() {
    return {
        groups: [],
        selectedGroupId: '',
        loading: false, dataLoaded: false,
        currentGroup: null, students: [],
        activeTab: 'summary',
        notification: { show: false, message: '', type: 'success' },
        evalPeriod: 1, evalInputs: {}, savingEval: false,
        testNumber: 1, testMonth: new Date().toISOString().slice(0,7), testInputs: {}, savingTests: false,
        finalInputs: {}, savingFinal: false,
        showSettingsModal: false, settingsGroup: null, savingSettings: false,
        settingsForm: { start_date: '', end_date: '', grade_weights: { evaluations: 20, monthly_tests: 30, final_exam: 50 } },

        notify(msg, type = 'success') {
            this.notification = { show: true, message: msg, type };
            setTimeout(() => this.notification.show = false, 3500);
        },

        weightsSum() {
            const w = this.settingsForm.grade_weights;
            return parseInt(w.evaluations||0) + parseInt(w.monthly_tests||0) + parseInt(w.final_exam||0);
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

        openSettings(g) {
            this.settingsGroup = g;
            this.settingsForm = {
                start_date: g.start_date || '',
                end_date: g.end_date || '',
                grade_weights: { ...(g.grade_weights || { evaluations: 20, monthly_tests: 30, final_exam: 50 }) }
            };
            this.showSettingsModal = true;
        },

        async saveSettings() {
            if (this.weightsSum() !== 100) { this.notify('مجموع الأوزان يجب أن يساوي 100', 'error'); return; }
            this.savingSettings = true;
            try {
                const r = await fetch(`${window.gradesRoutes.groupSettings}/${this.settingsGroup.id}/settings`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                    body: JSON.stringify(this.settingsForm)
                });
                const d = await r.json();
                if (d.success) {
                    this.showSettingsModal = false;
                    this.notify(d.message);
                    await this.loadGroups();
                } else {
                    this.notify(d.errors ? Object.values(d.errors).flat()[0] : d.message, 'error');
                }
            } catch(_) { this.notify('خطأ في الاتصال', 'error'); }
            finally { this.savingSettings = false; }
        },

        async toggleFinalExam(g) {
            const r = await fetch(`${window.gradesRoutes.groupFinalExam}/${g.id}/final-exam`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf() }
            });
            const d = await r.json();
            if (d.success) { this.notify(d.message); await this.loadGroups(); }
        },

        async archiveGroup(g) {
            if (!confirm(`أرشفة المجموعة "${g.name}"؟ ستُغلق المجموعة ولن تكون نشطة. يمكن عرض بياناتها من صفحة الأرشيف.`)) return;
            const r = await fetch(`${window.gradesRoutes.groupArchive}/${g.id}/archive`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf() }
            });
            const d = await r.json();
            if (d.success) { this.notify(d.message); await this.loadGroups(); }
            else this.notify(d.message, 'error');
        },
    };
}
</script>
@endpush

@endsection
