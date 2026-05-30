@extends('layouts.dashboard')

@section('sidebar-menu')
@include('parent.partials.sidebar')
@endsection

@php
$pageTitle = 'الحضور والغياب';
$pageDescription = 'سجل حضور وغياب أبنائك الطلبة';
@endphp

@section('content')
@foreach($children as $child)
@php $data = $attendanceData[$child->id] ?? []; @endphp
<div class="mb-8 bg-white rounded-lg shadow">
    <div class="flex items-center justify-between p-5 border-b border-gray-200">
        <div class="flex items-center">
            <div class="flex items-center justify-center w-10 h-10 ml-3 text-white rounded-full bg-primary font-bold">
                {{ mb_substr($child->user->name, 0, 1) }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ $data['name'] ?? $child->user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $data['group'] ?? 'غير محدد' }}</p>
            </div>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold {{ ($data['attendance_percentage'] ?? 0) >= 75 ? 'text-green-600' : 'text-red-600' }}">
                {{ $data['attendance_percentage'] ?? 0 }}%
            </div>
            <p class="text-xs text-gray-500">نسبة الحضور الكلية</p>
        </div>
    </div>

    <div class="p-5">
        @php $monthly = $data['monthly_attendance'] ?? collect(); @endphp
        <h3 class="text-sm font-semibold text-gray-700 mb-4">سجل الحضور هذا الشهر</h3>

        <div class="grid grid-cols-3 sm:grid-cols-3 gap-3 mb-6">
            <div class="p-3 text-center rounded-lg bg-green-50">
                <p class="text-2xl font-bold text-green-600">{{ $monthly->get('present', collect())->count() }}</p>
                <p class="text-xs text-gray-500">حاضر</p>
            </div>
            <div class="p-3 text-center rounded-lg bg-red-50">
                <p class="text-2xl font-bold text-red-600">{{ $monthly->get('absent', collect())->count() }}</p>
                <p class="text-xs text-gray-500">غائب</p>
            </div>
            <div class="p-3 text-center rounded-lg bg-yellow-50">
                <p class="text-2xl font-bold text-yellow-600">{{ $monthly->get('late', collect())->count() }}</p>
                <p class="text-xs text-gray-500">متأخر</p>
            </div>
        </div>

        @php
        $allRecords = $monthly->flatten()->sortByDesc(fn($a) => $a->lecture?->date);
        @endphp
        @if($allRecords->count() > 0)
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
                    @foreach($allRecords as $record)
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
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-center text-gray-400 py-6">لا توجد سجلات حضور لهذا الشهر</p>
        @endif
    </div>
</div>
@endforeach
@endsection
