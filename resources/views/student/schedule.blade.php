@extends('layouts.dashboard')

@section('sidebar-menu')
@include('student.partials.sidebar')
@endsection

@php
$pageTitle = 'جدول المحاضرات';
$pageDescription = 'جدول محاضراتي وامتحاناتي وأنشطتي القادمة';
@endphp

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">محاضرات مجموعة: {{ $student->group?->name ?? 'غير محدد' }}</h2>
        </div>
    </div>

    @if($lectures->isEmpty())
    <div class="p-12 text-center text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p>لا توجد محاضرات قادمة</p>
    </div>
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
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($lectures as $lecture)
                @php
                $typeColors = ['lecture'=>'bg-blue-100 text-blue-800','exam'=>'bg-red-100 text-red-800','activity'=>'bg-green-100 text-green-800','revision'=>'bg-yellow-100 text-yellow-800'];
                $typeLabels = ['lecture'=>'محاضرة','exam'=>'امتحان','activity'=>'نشاط','revision'=>'مراجعة'];
                $type = $lecture->type ?? 'lecture';
                $isPast = \Carbon\Carbon::parse($lecture->date)->isPast();
                @endphp
                <tr class="hover:bg-gray-50 {{ $isPast ? 'opacity-60' : '' }}">
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
                    <td class="px-4 py-3">
                        @if($lecture->status === 'cancelled')
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">ملغى</span>
                        @elseif($isPast)
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">منتهي</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">قادم</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(method_exists($lectures, 'hasPages') && $lectures->hasPages())
    <div class="p-4 border-t border-gray-200">
        {{ $lectures->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
