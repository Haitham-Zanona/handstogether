@extends('layouts.dashboard')

@section('sidebar-menu')
@include('student.partials.sidebar')
@endsection

@php
$pageTitle = 'التقييمات والدرجات';
$pageDescription = 'درجاتي وتقييماتي الأكاديمية';
@endphp

@section('content')
@if(!$group)
<div class="p-12 text-center bg-white rounded-lg shadow text-gray-400">
    <p>لم يتم تعيينك في مجموعة بعد</p>
</div>
@else

<!-- ملخص الدرجات -->
<div class="grid grid-cols-2 gap-4 mb-8 md:grid-cols-4">
    <div class="p-6 bg-white rounded-lg shadow text-center md:col-span-1">
        <p class="text-sm text-gray-500 mb-1">المجموع الكلي</p>
        <p class="text-4xl font-bold {{ $total >= 60 ? 'text-green-600' : 'text-red-600' }}">{{ $total }}</p>
        <p class="text-xs text-gray-400 mt-1">من 100</p>
    </div>
    <div class="p-6 bg-blue-50 rounded-lg shadow text-center">
        <p class="text-xs text-gray-500 mb-1">التقييمات الدورية</p>
        <p class="text-2xl font-bold text-blue-600">{{ $evalGrade }}</p>
        <p class="text-xs text-gray-400">من {{ $weights['evaluations'] }}</p>
    </div>
    <div class="p-6 bg-green-50 rounded-lg shadow text-center">
        <p class="text-xs text-gray-500 mb-1">الاختبارات الشهرية</p>
        <p class="text-2xl font-bold text-green-600">{{ $testGrade }}</p>
        <p class="text-xs text-gray-400">من {{ $weights['monthly_tests'] }}</p>
    </div>
    <div class="p-6 bg-purple-50 rounded-lg shadow text-center">
        <p class="text-xs text-gray-500 mb-1">الامتحان النهائي</p>
        <p class="text-2xl font-bold text-purple-600">{{ $finalGrade }}</p>
        <p class="text-xs text-gray-400">من {{ $weights['final_exam'] }}</p>
    </div>
</div>

<!-- التقييمات الدورية -->
@if($evals->count() > 0)
<div class="mb-6 bg-white rounded-lg shadow">
    <div class="p-5 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-900">التقييمات الدورية</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">التقييم</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المشاركة /10</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">الانضباط /10</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">التحسن /10</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">الواجبات /10</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">اختبارات /10</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 bg-blue-50">المجموع /50</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($evals as $num => $eval)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">تقييم {{ $num }}</td>
                    <td class="px-4 py-3 text-center">{{ $eval->activity_participation }}</td>
                    <td class="px-4 py-3 text-center">{{ $eval->behavior_discipline }}</td>
                    <td class="px-4 py-3 text-center">{{ $eval->academic_improvement }}</td>
                    <td class="px-4 py-3 text-center">{{ $eval->homework }}</td>
                    <td class="px-4 py-3 text-center">{{ $eval->short_tests }}</td>
                    <td class="px-4 py-3 text-center font-bold text-blue-600 bg-blue-50">
                        {{ $eval->activity_participation + $eval->behavior_discipline + $eval->academic_improvement + $eval->homework + $eval->short_tests }}
                    </td>
                </tr>
                @if($eval->notes)
                <tr class="bg-gray-50">
                    <td colspan="7" class="px-4 py-2 text-xs text-gray-500">ملاحظة: {{ $eval->notes }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- الاختبارات الشهرية -->
@if($tests->count() > 0)
<div class="mb-6 bg-white rounded-lg shadow">
    <div class="p-5 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-900">الاختبارات الشهرية</h2>
    </div>
    <div class="p-5">
        <div class="flex flex-wrap gap-4">
            @foreach($tests as $num => $test)
            <div class="p-4 rounded-lg bg-green-50 border border-green-200 text-center min-w-28">
                <p class="text-xs text-gray-500 mb-1">اختبار {{ $num }}</p>
                <p class="text-2xl font-bold text-green-600">{{ $test->score }}<span class="text-sm text-gray-400">/20</span></p>
                @if($test->month)
                <p class="text-xs text-gray-400 mt-1">{{ $test->month }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- الامتحان النهائي -->
@if($finalExam)
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-900">الامتحان النهائي</h2>
    </div>
    <div class="p-5">
        <div class="inline-block p-6 rounded-lg bg-purple-50 border border-purple-200 text-center">
            <p class="text-xs text-gray-500 mb-1">النتيجة</p>
            <p class="text-4xl font-bold text-purple-600">{{ $finalExam->score }}</p>
            @if($finalExam->notes)
            <p class="text-xs text-gray-500 mt-2">{{ $finalExam->notes }}</p>
            @endif
        </div>
    </div>
</div>
@endif

@if($evals->count() === 0 && $tests->count() === 0 && !$finalExam)
<div class="bg-white rounded-lg shadow p-12 text-center text-gray-400">
    <p>لم يتم تسجيل أي درجات بعد</p>
</div>
@endif

@endif
@endsection
