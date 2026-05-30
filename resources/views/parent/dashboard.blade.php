@extends('layouts.dashboard')

@section('sidebar-menu')
@include('parent.partials.sidebar')
@endsection

@php
$pageTitle = 'لوحة تحكم ولي الأمر';
$pageDescription = 'متابعة أداء أبنائك الطلبة';
@endphp

@section('content')
<!-- إحصائيات سريعة -->
<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
    <div class="p-6 text-white rounded-lg shadow bg-gradient-to-r from-blue-500 to-blue-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-blue-100">عدد الأبناء المسجلين</p>
                <p class="text-3xl font-bold">{{ $stats['total_children'] }}</p>
            </div>
            <div class="p-3 bg-blue-600 rounded-full">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m3 5.197H9" />
                </svg>
            </div>
        </div>
    </div>

    <div class="p-6 text-white rounded-lg shadow bg-gradient-to-r from-orange-500 to-orange-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-orange-100">دفعات معلقة</p>
                <p class="text-3xl font-bold">{{ $stats['pending_payments'] }}</p>
            </div>
            <div class="p-3 bg-orange-600 rounded-full">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="p-6 text-white rounded-lg shadow bg-gradient-to-r from-green-500 to-green-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-green-100">نسبة الحضور هذا الشهر</p>
                <p class="text-3xl font-bold">{{ $stats['this_month_attendance'] }}%</p>
            </div>
            <div class="p-3 bg-green-600 rounded-full">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- بطاقات الأبناء -->
<h2 class="mb-4 text-xl font-bold text-gray-800">أبنائي الطلبة</h2>
<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    @forelse($children as $child)
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center mb-4">
            <div class="flex items-center justify-center w-12 h-12 ml-4 text-white rounded-full bg-primary text-lg font-bold">
                {{ mb_substr($child->user->name, 0, 1) }}
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $child->user->name }}</h3>
                <p class="text-sm text-gray-500">{{ $child->group->name ?? 'غير منضم لمجموعة' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="p-3 text-center rounded-lg bg-blue-50">
                <p class="text-xs text-gray-500 mb-1">نسبة الحضور</p>
                <p class="text-xl font-bold text-blue-600">{{ $child->getAttendancePercentage() }}%</p>
            </div>
            <div class="p-3 text-center rounded-lg bg-orange-50">
                <p class="text-xs text-gray-500 mb-1">حالة الدفعة</p>
                @php $payStatus = $child->getCurrentMonthPaymentStatus(); @endphp
                <span class="text-sm font-semibold {{ $payStatus === 'paid' ? 'text-green-600' : ($payStatus === 'pending' ? 'text-orange-600' : 'text-red-600') }}">
                    {{ $payStatus === 'paid' ? 'مدفوعة' : ($payStatus === 'pending' ? 'معلقة' : 'غير مدفوعة') }}
                </span>
            </div>
        </div>

        <div class="border-t pt-4">
            <p class="text-sm font-medium text-gray-700 mb-2">المحاضرات القادمة</p>
            @php $upcoming = $child->getUpcomingLectures()->take(3); @endphp
            @forelse($upcoming as $lecture)
            <div class="flex items-center justify-between py-1 text-sm">
                <span class="text-gray-700">{{ $lecture->title }}</span>
                <span class="text-gray-500">{{ \Carbon\Carbon::parse($lecture->date)->format('d/m') }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400">لا توجد محاضرات قادمة</p>
            @endforelse
        </div>
    </div>
    @empty
    <div class="col-span-2 p-12 text-center bg-white rounded-lg shadow">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m3 5.197H9" />
        </svg>
        <p class="text-gray-500">لا يوجد أبناء مسجلون حالياً</p>
    </div>
    @endforelse
</div>
@endsection
