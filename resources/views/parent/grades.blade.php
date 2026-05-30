@extends('layouts.dashboard')

@section('sidebar-menu')
@include('parent.partials.sidebar')
@endsection

@php
$pageTitle = 'التقييمات والدرجات';
$pageDescription = 'عرض درجات وتقييمات أبنائك الطلبة';
@endphp

@section('content')
@foreach($children as $child)
@php $data = $gradesData[$child->id] ?? null; @endphp
<div class="mb-8 bg-white rounded-lg shadow">
    <div class="flex items-center justify-between p-5 border-b border-gray-200">
        <div class="flex items-center">
            <div class="flex items-center justify-center w-10 h-10 ml-3 text-white rounded-full bg-primary font-bold">
                {{ mb_substr($child->user->name, 0, 1) }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ $child->user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $data['group'] ?? 'غير محدد' }}</p>
            </div>
        </div>
        @if($data)
        <div class="text-center">
            <div class="text-3xl font-bold {{ ($data['total'] ?? 0) >= 60 ? 'text-green-600' : 'text-red-600' }}">
                {{ $data['total'] ?? 0 }}
            </div>
            <p class="text-xs text-gray-500">المجموع الكلي</p>
        </div>
        @endif
    </div>

    @if(!$data)
    <div class="p-8 text-center text-gray-400">لا توجد درجات مسجلة بعد</div>
    @else
    <div class="p-5">
        <!-- ملخص الدرجات -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="p-4 rounded-lg bg-blue-50 text-center">
                <p class="text-xs text-gray-500 mb-1">التقييمات الدورية</p>
                <p class="text-2xl font-bold text-blue-600">{{ $data['eval_grade'] }}</p>
                <p class="text-xs text-gray-400">من {{ $data['weights']['evaluations'] }}</p>
            </div>
            <div class="p-4 rounded-lg bg-green-50 text-center">
                <p class="text-xs text-gray-500 mb-1">الاختبارات الشهرية</p>
                <p class="text-2xl font-bold text-green-600">{{ $data['test_grade'] }}</p>
                <p class="text-xs text-gray-400">من {{ $data['weights']['monthly_tests'] }}</p>
            </div>
            <div class="p-4 rounded-lg bg-purple-50 text-center">
                <p class="text-xs text-gray-500 mb-1">الامتحان النهائي</p>
                <p class="text-2xl font-bold text-purple-600">{{ $data['final_grade'] }}</p>
                <p class="text-xs text-gray-400">من {{ $data['weights']['final_exam'] }}</p>
            </div>
        </div>

        <!-- التقييمات الدورية -->
        @if($data['evaluations']->count() > 0)
        <h3 class="text-sm font-semibold text-gray-700 mb-3 border-b pb-2">التقييمات الدورية</h3>
        <div class="overflow-x-auto mb-6">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-right text-xs text-gray-500">التقييم</th>
                        <th class="px-3 py-2 text-right text-xs text-gray-500">المشاركة</th>
                        <th class="px-3 py-2 text-right text-xs text-gray-500">الانضباط</th>
                        <th class="px-3 py-2 text-right text-xs text-gray-500">التحسن</th>
                        <th class="px-3 py-2 text-right text-xs text-gray-500">الواجبات</th>
                        <th class="px-3 py-2 text-right text-xs text-gray-500">اختبارات قصيرة</th>
                        <th class="px-3 py-2 text-right text-xs text-gray-500">المجموع</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($data['evaluations'] as $num => $eval)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-medium">تقييم {{ $num }}</td>
                        <td class="px-3 py-2">{{ $eval->activity_participation }}/10</td>
                        <td class="px-3 py-2">{{ $eval->behavior_discipline }}/10</td>
                        <td class="px-3 py-2">{{ $eval->academic_improvement }}/10</td>
                        <td class="px-3 py-2">{{ $eval->homework }}/10</td>
                        <td class="px-3 py-2">{{ $eval->short_tests }}/10</td>
                        <td class="px-3 py-2 font-semibold text-blue-600">
                            {{ $eval->activity_participation + $eval->behavior_discipline + $eval->academic_improvement + $eval->homework + $eval->short_tests }}/50
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- الاختبارات الشهرية -->
        @if($data['tests']->count() > 0)
        <h3 class="text-sm font-semibold text-gray-700 mb-3 border-b pb-2">الاختبارات الشهرية</h3>
        <div class="flex flex-wrap gap-3 mb-6">
            @foreach($data['tests'] as $num => $test)
            <div class="p-3 rounded-lg bg-green-50 text-center min-w-24">
                <p class="text-xs text-gray-500">اختبار {{ $num }}</p>
                <p class="text-xl font-bold text-green-600">{{ $test->score }}/20</p>
                @if($test->month)
                <p class="text-xs text-gray-400">{{ $test->month }}</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- الامتحان النهائي -->
        @if($data['final_exam'])
        <h3 class="text-sm font-semibold text-gray-700 mb-3 border-b pb-2">الامتحان النهائي</h3>
        <div class="p-4 rounded-lg bg-purple-50 inline-block">
            <p class="text-xs text-gray-500 mb-1">النتيجة</p>
            <p class="text-3xl font-bold text-purple-600">{{ $data['final_exam']->score }}</p>
        </div>
        @endif
    </div>
    @endif
</div>
@endforeach
@endsection
