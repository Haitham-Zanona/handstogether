@extends('layouts.dashboard')

@section('sidebar-menu')
@include('teacher.partials.sidebar')
@endsection

@php
$sidebarTitle    = 'بوابة المدرسين';
$pageTitle       = $group->name;
$pageDescription = 'تفاصيل المجموعة — ' . $group->grade_level;
@endphp

@section('content')

<div class="space-y-6">

    {{-- Back button --}}
    <div>
        <a href="{{ route('teacher.dashboard') }}"
            class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-primary transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            العودة إلى لوحة التحكم
        </a>
    </div>

    {{-- Group header --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center">
                    <svg class="w-7 h-7 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">{{ $group->name }}</h1>
                    <p class="text-sm text-gray-500">{{ $group->grade_level }}
                        @if($group->section) — شعبة {{ $group->section }} @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-6 text-center">
                <div>
                    <p class="text-2xl font-bold text-primary">{{ $students->count() }}</p>
                    <p class="text-xs text-gray-400">طالب</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-green-600">{{ $upcomingLectures->count() }}</p>
                    <p class="text-xs text-gray-400">محاضرة قادمة</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Students List --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-800 text-sm">قائمة الطلاب ({{ $students->count() }})</h2>
                </div>
                @if($students->isEmpty())
                <div class="p-8 text-center text-gray-400 text-sm">لا يوجد طلاب في هذه المجموعة</div>
                @else
                <div class="divide-y divide-gray-50">
                    @foreach($students as $i => $student)
                    <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition">
                        <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-500 text-xs flex items-center justify-center shrink-0 font-medium">
                            {{ $i + 1 }}
                        </span>
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <span class="text-primary text-xs font-bold">
                                {{ mb_substr($student->user?->name ?? '؟', 0, 1) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $student->user?->name ?? 'غير محدد' }}</p>
                            @if($student->user?->national_id)
                            <p class="text-xs text-gray-400 font-mono">{{ $student->user->national_id }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Upcoming Lectures --}}
        <div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-800 text-sm">المحاضرات القادمة</h2>
                </div>
                @if($upcomingLectures->isEmpty())
                <div class="p-6 text-center text-gray-400 text-sm">لا توجد محاضرات قادمة</div>
                @else
                <div class="divide-y divide-gray-50">
                    @foreach($upcomingLectures as $lecture)
                    <div class="p-4">
                        <p class="text-sm font-medium text-gray-800">{{ $lecture->title }}</p>
                        <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-400">
                            <span>{{ \Carbon\Carbon::parse($lecture->date)->format('d/m/Y') }}</span>
                            @if($lecture->start_time)
                            <span>{{ \Carbon\Carbon::parse($lecture->start_time)->format('H:i') }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

    </div>

</div>

@endsection
