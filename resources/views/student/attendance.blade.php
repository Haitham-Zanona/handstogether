@extends('layouts.dashboard')

@section('sidebar-menu')
@include('student.partials.sidebar')
@endsection

@php
$pageTitle = 'الحضور والغياب';
$pageDescription = 'سجل حضوري وغيابي';
@endphp

@section('content')
<!-- ملخص الحضور -->
<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-4">
    <div class="p-6 bg-white rounded-lg shadow text-center">
        <p class="text-sm text-gray-500 mb-1">نسبة الحضور الكلية</p>
        <p class="text-3xl font-bold {{ $attendancePercentage >= 75 ? 'text-green-600' : 'text-red-600' }}">
            {{ $attendancePercentage }}%
        </p>
        @if($attendancePercentage < 75)
        <p class="text-xs text-red-500 mt-1">نسبة الحضور أقل من المطلوب (75%)</p>
        @endif
    </div>
    <div class="p-6 bg-white rounded-lg shadow text-center">
        <p class="text-sm text-gray-500 mb-1">حاضر هذا الشهر</p>
        <p class="text-3xl font-bold text-green-600">{{ $monthlyAttendance->get('present', collect())->count() }}</p>
    </div>
    <div class="p-6 bg-white rounded-lg shadow text-center">
        <p class="text-sm text-gray-500 mb-1">غائب هذا الشهر</p>
        <p class="text-3xl font-bold text-red-600">{{ $monthlyAttendance->get('absent', collect())->count() }}</p>
    </div>
    <div class="p-6 bg-white rounded-lg shadow text-center">
        <p class="text-sm text-gray-500 mb-1">متأخر هذا الشهر</p>
        <p class="text-3xl font-bold text-yellow-600">{{ $monthlyAttendance->get('late', collect())->count() }}</p>
    </div>
</div>

<!-- جدول الحضور -->
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-900">سجل الحضور التفصيلي</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المحاضرة</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">التاريخ</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">الحالة</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($allAttendance as $record)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $record->lecture?->title ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $record->lecture?->date ? \Carbon\Carbon::parse($record->lecture->date)->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($record->status === 'present')
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">حاضر</span>
                        @elseif($record->status === 'absent')
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">غائب</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">متأخر</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-4 py-12 text-center text-gray-400">لا توجد سجلات حضور</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
