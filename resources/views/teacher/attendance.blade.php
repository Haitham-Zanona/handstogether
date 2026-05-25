@extends('layouts.dashboard')

@section('sidebar-menu')
@include('teacher.partials.sidebar')
@endsection

@php
$sidebarTitle  = 'بوابة المدرس';
$pageTitle     = 'تسجيل الحضور';
$pageDescription = 'سجّل حضور الطلاب لمحاضراتك اليومية';
@endphp

@section('content')

{{-- Date Picker --}}
<div class="mb-6 p-4 bg-white rounded-lg shadow-sm border border-gray-100">
    <form method="GET" action="{{ route('teacher.attendance') }}" class="flex flex-wrap items-center gap-4">
        <label class="text-sm font-medium text-gray-700">اختر التاريخ:</label>
        <input type="date"
               name="date"
               value="{{ $date }}"
               max="{{ today()->format('Y-m-d') }}"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
               onchange="this.form.submit()">
        <span class="text-sm text-gray-500">
            {{ \Carbon\Carbon::parse($date)->locale('ar')->isoFormat('dddd، D MMMM YYYY') }}
        </span>
    </form>
</div>

{{-- No lectures --}}
@if($lectures->isEmpty())
<div class="bg-white rounded-lg shadow p-12 text-center">
    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
    </svg>
    <p class="text-lg font-medium text-gray-600">لا توجد محاضرات في هذا اليوم</p>
    <p class="mt-1 text-sm text-gray-400">اختر تاريخاً آخر من الأعلى</p>
</div>
@endif

{{-- Lecture Cards --}}
@foreach($lectures as $lecture)
@php
    $existingStatuses = $lecture->attendance->keyBy('student_id')->map(fn($a) => $a->status);
    $students = $lecture->group->students ?? collect();
    $studentIds = $students->pluck('id')->toArray();
    $startTime  = is_string($lecture->start_time) ? substr($lecture->start_time, 0, 5) : $lecture->start_time?->format('H:i');
    $endTime    = is_string($lecture->end_time)   ? substr($lecture->end_time, 0, 5)   : $lecture->end_time?->format('H:i');
@endphp

<div class="mb-6 bg-white rounded-lg shadow"
     x-data='lectureAttendance(@json($existingStatuses), @json($studentIds))'>

    {{-- Header --}}
    <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ $lecture->title }}</h3>
            <p class="mt-1 text-sm text-gray-500">
                {{ $lecture->group->name ?? '—' }}
                <span class="mx-1">•</span>
                {{ $startTime }} – {{ $endTime }}
            </p>
        </div>
        <div class="flex items-center gap-5 text-sm">
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-green-500"></span>
                حاضر: <strong x-text="counts.present" class="text-green-700"></strong>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                متأخر: <strong x-text="counts.late" class="text-yellow-700"></strong>
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-500"></span>
                غائب: <strong x-text="counts.absent" class="text-red-700"></strong>
            </span>
        </div>
    </div>

    @if($students->isEmpty())
        <div class="px-6 py-8 text-center text-gray-400">لا يوجد طلاب في هذه المجموعة</div>
    @else

    {{-- Bulk actions --}}
    <div class="px-6 py-3 bg-gray-50 border-b border-gray-100 flex flex-wrap items-center gap-2">
        <span class="text-xs font-medium text-gray-500 ml-1">تحديد الجميع:</span>
        <button type="button" @click="markAll('present')"
            class="px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-700 hover:bg-green-200 transition">
            ✓ حاضر
        </button>
        <button type="button" @click="markAll('late')"
            class="px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700 hover:bg-yellow-200 transition">
            ⏰ متأخر
        </button>
        <button type="button" @click="markAll('absent')"
            class="px-3 py-1.5 rounded-full text-xs font-medium bg-red-100 text-red-700 hover:bg-red-200 transition">
            ✗ غائب
        </button>
        <span class="mr-auto text-xs text-gray-400"
              x-show="unmarkedCount > 0"
              x-text="unmarkedCount + ' طالب لم يُسجَّل بعد'">
        </span>
    </div>

    {{-- Students form --}}
    <form method="POST" action="{{ route('teacher.lectures.mark-attendance', $lecture) }}">
        @csrf

        <div class="divide-y divide-gray-100">
            @foreach($students as $student)
            <div class="px-6 py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex-shrink-0 flex items-center justify-center w-9 h-9 rounded-full bg-primary text-white font-bold text-sm select-none">
                        {{ mb_substr($student->user->name ?? 'ط', 0, 1) }}
                    </div>
                    <span class="font-medium text-gray-900 truncate">{{ $student->user->name ?? 'غير محدد' }}</span>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button type="button"
                        @click="setStatus({{ $student->id }}, 'present')"
                        :class="statuses['{{ $student->id }}'] === 'present'
                            ? 'bg-green-500 text-white ring-2 ring-green-300'
                            : 'bg-gray-100 text-gray-500 hover:bg-green-50 hover:text-green-700'"
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition-all">
                        حاضر
                    </button>
                    <button type="button"
                        @click="setStatus({{ $student->id }}, 'late')"
                        :class="statuses['{{ $student->id }}'] === 'late'
                            ? 'bg-yellow-400 text-white ring-2 ring-yellow-200'
                            : 'bg-gray-100 text-gray-500 hover:bg-yellow-50 hover:text-yellow-700'"
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition-all">
                        متأخر
                    </button>
                    <button type="button"
                        @click="setStatus({{ $student->id }}, 'absent')"
                        :class="statuses['{{ $student->id }}'] === 'absent'
                            ? 'bg-red-500 text-white ring-2 ring-red-300'
                            : 'bg-gray-100 text-gray-500 hover:bg-red-50 hover:text-red-700'"
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition-all">
                        غائب
                    </button>
                    {{-- Value carried by the form --}}
                    <input type="hidden" name="attendance[{{ $student->id }}]"
                           :value="statuses['{{ $student->id }}'] ?? ''">
                </div>
            </div>
            @endforeach
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <p class="text-sm text-gray-500" x-show="unmarkedCount > 0">
                تبقّى <span x-text="unmarkedCount" class="font-medium text-orange-600"></span> طالب بدون تسجيل
            </p>
            <p class="text-sm text-green-600 font-medium" x-show="unmarkedCount === 0">
                جميع الطلاب مسجّلون ✓
            </p>
            <button type="submit"
                :disabled="unmarkedCount > 0"
                :class="unmarkedCount === 0 ? 'bg-primary hover:bg-blue-700 cursor-pointer' : 'bg-gray-300 cursor-not-allowed'"
                class="px-6 py-2 text-white font-medium rounded-lg transition-colors">
                حفظ الحضور
            </button>
        </div>
    </form>
    @endif
</div>
@endforeach

@push('scripts')
<script>
function lectureAttendance(existingStatuses, studentIds) {
    // Normalise keys to strings so statuses['5'] and statuses[5] both work
    const norm = {};
    Object.entries(existingStatuses).forEach(([k, v]) => { norm[String(k)] = v; });

    return {
        statuses: norm,
        studentIds: studentIds.map(String),

        get counts() {
            const vals = Object.values(this.statuses);
            return {
                present: vals.filter(v => v === 'present').length,
                late:    vals.filter(v => v === 'late').length,
                absent:  vals.filter(v => v === 'absent').length,
            };
        },

        get unmarkedCount() {
            const marked = this.studentIds.filter(id => this.statuses[id] !== undefined).length;
            return this.studentIds.length - marked;
        },

        setStatus(id, status) {
            this.statuses[String(id)] = status;
        },

        markAll(status) {
            this.studentIds.forEach(id => { this.statuses[id] = status; });
        },
    };
}
</script>
@endpush

@endsection
