@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle = 'البوابة الإدارية';
$pageTitle = 'الحضور والغياب';
$pageDescription = 'مراجعة تقارير الحضور والغياب لجميع المجموعات';
@endphp

@section('content')



<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">تقارير الحضور والغياب</h1>
</div>

<!-- Statistics -->
<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
    <div class="p-6 bg-white rounded-lg shadow">
        <h3 class="mb-2 text-lg font-semibold text-gray-900">إجمالي المحاضرات</h3>
        <p class="text-3xl font-bold text-blue-600">{{ $attendanceStats['total_lectures'] ?? 0 }}</p>
        <p class="text-sm text-gray-600">هذا الشهر</p>
    </div>

    <div class="p-6 bg-white rounded-lg shadow">
        <h3 class="mb-2 text-lg font-semibold text-gray-900">إجمالي الحضور</h3>
        <p class="text-3xl font-bold text-green-600">{{ $attendanceStats['present_count'] ?? 0 }}</p>
        <p class="text-sm text-gray-600">طالب حاضر</p>
    </div>

    <div class="p-6 bg-white rounded-lg shadow">
        <h3 class="mb-2 text-lg font-semibold text-gray-900">نسبة الحضور</h3>
        @php
        $total = $attendanceStats['total_attendance'] ?? 0;
        $present = $attendanceStats['present_count'] ?? 0;
        $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        @endphp
        <p class="text-3xl font-bold text-primary">{{ $percentage }}%</p>
        <p class="text-sm text-gray-600">معدل الحضور العام</p>
    </div>
</div>

<!-- Groups Attendance -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">الحضور حسب المجموعات</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">المجموعة</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">عدد الطلاب</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">الحاضرون</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">الغائبون</th>
                    <th class="px-6 py-3 text-xs font-medium text-right text-gray-500 uppercase">نسبة الحضور</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($groups ?? [] as $group)
                @php
                $totalStudents = $group->students->count();
                $presentCount = $group->students->sum(function($student) {
                return $student->attendance->where('status', 'present')->count();
                });
                $absentCount = $group->students->sum(function($student) {
                return $student->attendance->where('status', 'absent')->count();
                });

                $totalAttendance = $presentCount + $absentCount;
                $attendanceRate = $totalAttendance > 0
                ? round(($presentCount / $totalAttendance) * 100, 1)
                : 0;
                @endphp
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                        {{ $group->name }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                        {{ $totalStudents }}
                    </td>
                    <td class="px-6 py-4 text-sm text-green-600 whitespace-nowrap">
                        {{ $presentCount }}
                    </td>
                    <td class="px-6 py-4 text-sm text-red-600 whitespace-nowrap">
                        {{ $absentCount }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-full h-2 mr-2 bg-gray-200 rounded-full">
                                <div class="h-2 bg-green-600 rounded-full" style="width: {{ $attendanceRate }}%"></div>
                            </div>
                            <span class="text-sm text-gray-900">{{ $attendanceRate }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        لا توجد بيانات حضور متاحة
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection