@extends('layouts.dashboard')

@section('sidebar-menu')
@include('parent.partials.sidebar')
@endsection

@php
$pageTitle = 'جدول المحاضرات';
$pageDescription = 'عرض جدول المحاضرات والامتحانات والأنشطة والمراجعات';
@endphp

@section('content')
@foreach($children as $child)
<div class="mb-8 bg-white rounded-lg shadow">
    <div class="flex items-center p-5 border-b border-gray-200">
        <div class="flex items-center justify-center w-10 h-10 ml-3 text-white rounded-full bg-primary font-bold">
            {{ mb_substr($child->user->name, 0, 1) }}
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-900">{{ $child->user->name }}</h2>
            <p class="text-sm text-gray-500">{{ $child->group->name ?? 'غير منضم لمجموعة' }}</p>
        </div>
    </div>

    <div class="p-5">
        @php $childSchedule = $schedules[$child->id] ?? []; @endphp
        @if(empty($childSchedule['upcoming_lectures']) || $childSchedule['upcoming_lectures']->isEmpty())
        <p class="text-center text-gray-400 py-8">لا توجد محاضرات قادمة</p>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">العنوان</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">النوع</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الوقت</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">المدرس</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($childSchedule['upcoming_lectures'] as $lecture)
                    @php
                    $typeColors = ['lecture'=>'bg-blue-100 text-blue-800','exam'=>'bg-red-100 text-red-800','activity'=>'bg-green-100 text-green-800','revision'=>'bg-yellow-100 text-yellow-800'];
                    $typeLabels = ['lecture'=>'محاضرة','exam'=>'امتحان','activity'=>'نشاط','revision'=>'مراجعة'];
                    $type = $lecture->type ?? 'lecture';
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $lecture->title }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ $typeColors[$type] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $typeLabels[$type] ?? $type }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ \Carbon\Carbon::parse($lecture->date)->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ \Carbon\Carbon::parse($lecture->start_time)->format('H:i') }}
                            @if($lecture->end_time) - {{ \Carbon\Carbon::parse($lecture->end_time)->format('H:i') }} @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $lecture->teacher?->user?->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endforeach
@endsection
