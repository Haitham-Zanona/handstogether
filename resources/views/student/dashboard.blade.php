@extends('layouts.dashboard')

@section('sidebar-menu')
@include('student.partials.sidebar')
@endsection

@php
$pageTitle = 'لوحة تحكم الطالب';
$pageDescription = 'نظرة عامة على وضعك الأكاديمي';
@endphp

@section('content')
<!-- إحصائيات سريعة -->
<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
    <div class="p-6 text-white rounded-lg shadow bg-gradient-to-r from-blue-500 to-blue-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-blue-100">محاضرات قادمة</p>
                <p class="text-3xl font-bold">{{ $stats['upcoming_lectures'] }}</p>
            </div>
            <div class="p-3 bg-blue-600 rounded-full">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="p-6 text-white rounded-lg shadow bg-gradient-to-r from-green-500 to-green-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-green-100">نسبة الحضور</p>
                <p class="text-3xl font-bold">{{ $stats['attendance_percentage'] }}%</p>
            </div>
            <div class="p-3 bg-green-600 rounded-full">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
        </div>
    </div>

    <div class="p-6 text-white rounded-lg shadow bg-gradient-to-r from-orange-500 to-orange-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-orange-100">حالة الدفعة الحالية</p>
                @php $ps = $stats['payment_status']; @endphp
                <p class="text-xl font-bold mt-1">
                    {{ $ps === 'paid' ? 'مدفوعة' : ($ps === 'pending' ? 'معلقة' : 'غير مدفوعة') }}
                </p>
            </div>
            <div class="p-3 bg-orange-600 rounded-full">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- معلومات الطالب -->
<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">معلوماتي</h2>
        <div class="space-y-3">
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-500">الاسم</span>
                <span class="text-sm font-medium text-gray-900">{{ $student->user->name }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-500">المجموعة</span>
                <span class="text-sm font-medium text-gray-900">{{ $student->group?->name ?? 'غير محدد' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm text-gray-500">المستوى الدراسي</span>
                <span class="text-sm font-medium text-gray-900">{{ $student->group?->grade_level ?? '—' }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">المحاضرات القادمة</h2>
        @forelse($upcomingLectures as $lecture)
        @php
        $typeLabels = ['lecture'=>'محاضرة','exam'=>'امتحان','activity'=>'نشاط','revision'=>'مراجعة'];
        $typeColors = ['lecture'=>'bg-blue-100 text-blue-800','exam'=>'bg-red-100 text-red-800','activity'=>'bg-green-100 text-green-800','revision'=>'bg-yellow-100 text-yellow-800'];
        $type = $lecture->type ?? 'lecture';
        @endphp
        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
            <div>
                <p class="text-sm font-medium text-gray-900">{{ $lecture->title }}</p>
                <p class="text-xs text-gray-500">{{ $lecture->teacher?->user?->name ?? '' }}</p>
            </div>
            <div class="text-left">
                <span class="px-2 py-0.5 text-xs rounded-full {{ $typeColors[$type] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ $typeLabels[$type] ?? $type }}
                </span>
                <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($lecture->date)->format('d/m/Y') }}</p>
            </div>
        </div>
        @empty
        <p class="text-center text-gray-400 py-6">لا توجد محاضرات قادمة</p>
        @endforelse
    </div>
</div>
@endsection
