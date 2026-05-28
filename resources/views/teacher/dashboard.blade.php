@extends('layouts.dashboard')

@section('sidebar-menu')
@include('teacher.partials.sidebar')
@endsection

@php
$sidebarTitle    = 'بوابة المدرسين';
$pageTitle       = 'لوحة التحكم';
$pageDescription = 'مرحباً ' . auth()->user()->name;
@endphp

@section('content')

<div class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-xl p-5 text-white shadow-lg">
            <p class="text-sm text-blue-100">طلابي</p>
            <p class="text-3xl font-bold mt-1">{{ $stats['total_students'] }}</p>
            <p class="text-xs text-blue-200 mt-1">في جميع المجموعات</p>
        </div>
        <div class="bg-gradient-to-r from-orange-500 to-orange-700 rounded-xl p-5 text-white shadow-lg">
            <p class="text-sm text-orange-100">مجموعاتي</p>
            <p class="text-3xl font-bold mt-1">{{ $stats['total_groups'] }}</p>
            <p class="text-xs text-orange-200 mt-1">مجموعة مُكلَّفة</p>
        </div>
        <div class="bg-gradient-to-r from-green-500 to-green-700 rounded-xl p-5 text-white shadow-lg">
            <p class="text-sm text-green-100">محاضرات اليوم</p>
            <p class="text-3xl font-bold mt-1">{{ $stats['today_lectures'] }}</p>
            <p class="text-xs text-green-200 mt-1">{{ now()->format('l') }}</p>
        </div>
        <div class="bg-gradient-to-r from-purple-500 to-purple-700 rounded-xl p-5 text-white shadow-lg">
            <p class="text-sm text-purple-100">محاضرات الأسبوع</p>
            <p class="text-3xl font-bold mt-1">{{ $stats['week_lectures'] }}</p>
            <p class="text-xs text-purple-200 mt-1">هذا الأسبوع</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Groups Cards --}}
        <div class="lg:col-span-2 space-y-4">
            <h2 class="text-base font-semibold text-gray-800">مجموعاتي</h2>

            @if($groups->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-8 text-center">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-gray-400 text-sm">لم يتم تكليفك بأي مجموعة بعد</p>
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($groups as $group)
                <a href="{{ route('teacher.groups.show', $group) }}"
                    class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md hover:border-primary/30 transition-all group">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800 group-hover:text-primary transition-colors">
                                {{ $group->name }}
                            </h3>
                            <p class="text-sm text-gray-400 mt-0.5">{{ $group->grade_level }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-4 text-sm text-gray-500">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m3 5.197H9"/>
                            </svg>
                            {{ $group->students_count }} طالب
                        </span>
                        @if($group->section)
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            شعبة {{ $group->section }}
                        </span>
                        @endif
                    </div>
                    <div class="mt-3 flex items-center text-xs text-primary font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                        <span>عرض تفاصيل المجموعة</span>
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Weekly Schedule --}}
        <div class="space-y-4">
            <h2 class="text-base font-semibold text-gray-800">جدول هذا الأسبوع</h2>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-50">
                @forelse($weeklySchedule as $lecture)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="font-medium text-gray-800 text-sm truncate">{{ $lecture->title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $lecture->group?->name }}</p>
                        </div>
                        <div class="text-left shrink-0">
                            <p class="text-xs font-medium text-primary">
                                {{ \Carbon\Carbon::parse($lecture->date)->format('d/m') }}
                            </p>
                            @if($lecture->start_time)
                            <p class="text-xs text-gray-400">
                                {{ \Carbon\Carbon::parse($lecture->start_time)->format('H:i') }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-gray-400 text-sm">
                    لا توجد محاضرات هذا الأسبوع
                </div>
                @endforelse
            </div>

            {{-- Today's Lectures --}}
            @if($todayLectures->isNotEmpty())
            <h2 class="text-base font-semibold text-gray-800 mt-4">محاضرات اليوم</h2>
            <div class="space-y-2">
                @foreach($todayLectures as $lecture)
                <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                    <p class="font-medium text-gray-800 text-sm">{{ $lecture->title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $lecture->group?->name }}
                        @if($lecture->start_time)
                        — {{ \Carbon\Carbon::parse($lecture->start_time)->format('H:i') }}
                        @endif
                    </p>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>

</div>

@endsection
