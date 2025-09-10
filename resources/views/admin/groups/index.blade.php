@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle = 'البوابة الإدارية';
$pageTitle = 'إدارة المجموعات';
$pageDescription = 'إضافة وإدارة مجموعات الطلاب والشعب الدراسية';
@endphp

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }

    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .student-card {
        transition: all 0.2s ease;
    }

    .student-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .progress-ring {
        transform: rotate(-90deg);
    }

    .progress-ring-circle {
        transition: stroke-dasharray 0.3s ease;
    }
</style>
@endpush

{{-- تمرير البيانات بشكل آمن --}}
<script>
    window.initialStats = @json($stats ?? []);
    window.routes = {
        groupsData: '{{ route("admin.groups.data") }}',
        groupsStore: '{{ route("admin.groups.store") }}',
        groupsUpdate: '{{ url("/admin/groups") }}',
        groupsDestroy: '{{ url("/admin/groups") }}',
        groupStudents: '{{ url("/admin/groups") }}',
        addStudentToGroup: '{{ url("/admin/groups") }}',
        removeStudentFromGroup: '{{ url("/admin/groups") }}',
        moveStudentToGroup: '{{ url("/admin/groups") }}',
        availableStudents: '{{ route("admin.groups.students.available") }}',
        availableGroups: '{{ route("admin.groups.available") }}',

        // إضافة routes جديدة للمواد
        availableSubjects: '{{ route("admin.groups.subjects.available") }}',
        groupSubjects: '{{ url("/admin/groups") }}',
        addSubjectToGroup: '{{ url("/admin/groups") }}',
        updateGroupSubject: '{{ url("/admin/groups") }}',
        removeSubjectFromGroup: '{{ url("/admin/groups") }}',
        copySubjectsBetweenGroups: '{{ url("/admin/groups") }}'
    };
</script>

@section('content')
<div class="min-h-screen p-6 bg-gray-50" x-data="groupsManager()" x-init="loadData()">

    <!-- Loading State -->
    <div x-show="loading" class="flex items-center justify-center p-8">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 border-4 border-blue-500 rounded-full border-t-transparent animate-spin"></div>
            <span class="text-lg text-gray-600">جاري تحميل البيانات...</span>
        </div>
    </div>

    <!-- Content -->
    <div x-show="!loading" x-cloak>
        <!-- إحصائيات سريعة -->
        <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-4">
            <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <i class="text-xl text-blue-600 fas fa-users"></i>
                    </div>
                    <div class="mr-4">
                        <p class="text-sm text-gray-600">إجمالي المجموعات</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.total_groups">0</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <i class="text-xl text-green-600 fas fa-user-graduate"></i>
                    </div>
                    <div class="mr-4">
                        <p class="text-sm text-gray-600">إجمالي الطلاب</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.total_students">0</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <i class="text-xl text-yellow-600 fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="mr-4">
                        <p class="text-sm text-gray-600">مجموعات ممتلئة</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.full_groups">0</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <i class="text-xl text-purple-600 fas fa-clock"></i>
                    </div>
                    <div class="mr-4">
                        <p class="text-sm text-gray-600">محاضرات اليوم</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="stats.today_lectures">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- أزرار الإجراءات الرئيسية -->
        <div class="flex flex-wrap gap-4 mb-8">
            <button @click="showCreateModal = true"
                class="flex items-center gap-2 px-6 py-3 font-medium text-white transition-all duration-200 bg-blue-600 rounded-lg shadow-md hover:bg-blue-700 hover:shadow-lg">
                <i class="fas fa-plus"></i>
                إضافة مجموعة جديدة
            </button>

            <button @click="refreshData()"
                class="flex items-center gap-2 px-6 py-3 font-medium text-white transition-all duration-200 bg-green-600 rounded-lg shadow-md hover:bg-green-700 hover:shadow-lg">
                <i class="fas fa-sync-alt" :class="{ 'animate-spin': refreshing }"></i>
                تحديث البيانات
            </button>

            <button onclick="window.print()"
                class="flex items-center gap-2 px-6 py-3 font-medium text-white transition-all duration-200 bg-gray-600 rounded-lg shadow-md hover:bg-gray-700 hover:shadow-lg">
                <i class="fas fa-print"></i>
                طباعة التقرير
            </button>
        </div>

        <!-- فلاتر البحث -->
        <div class="p-6 mb-8 bg-white border border-gray-100 shadow-sm rounded-xl">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">البحث بالاسم</label>
                    <input type="text" x-model="searchTerm" placeholder="ابحث عن مجموعة..."
                        class="w-full px-6 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">المرحلة الدراسية</label>
                    <select x-model="gradeFilter"
                        class="w-full px-8 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">جميع المراحل</option>
                        <option value="الصف الأول">الصف الأول</option>
                        <option value="الصف الثاني">الصف الثاني</option>
                        <option value="الصف الثالث">الصف الثالث</option>
                        <option value="الصف الرابع">الصف الرابع</option>
                        <option value="الصف الخامس">الصف الخامس</option>
                        <option value="الصف السادس">الصف السادس</option>
                        <option value="الصف السابع">الصف السابع</option>
                        <option value="الصف الثامن">الصف الثامن</option>
                        <option value="الصف التاسع">الصف التاسع</option>
                        <option value="الصف العاشر">الصف العاشر</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">حالة المجموعة</label>
                    <select x-model="statusFilter"
                        class="w-full px-8 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">جميع الحالات</option>
                        <option value="active">نشطة</option>
                        <option value="full">ممتلئة</option>
                        <option value="available">متاحة</option>
                    </select>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">ترتيب حسب</label>
                    <select x-model="sortBy"
                        class="w-full px-8 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="name">الاسم</option>
                        <option value="students_count">عدد الطلاب</option>
                        <option value="created_at">تاريخ الإنشاء</option>
                        <option value="grade_level">المرحلة الدراسية</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- جدول المجموعات -->
        <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">قائمة المجموعات</h3>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600"
                            x-text="`عرض ${filteredGroups.length} من ${groups.length} مجموعة`"></span>
                        <div class="flex gap-2">
                            <button @click="viewMode = 'table'"
                                :class="viewMode === 'table' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'"
                                class="p-2 transition-colors rounded-lg">
                                <i class="fas fa-table"></i>
                            </button>
                            <button @click="viewMode = 'cards'"
                                :class="viewMode === 'cards' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'"
                                class="p-2 transition-colors rounded-lg">
                                <i class="fas fa-th-large"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- عرض جدولي -->
            <div x-show="viewMode === 'table'" class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-4 w-1/4 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                                المجموعة</th>
                            <th
                                class="px-4 py-4 w-1/6 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                                المرحلة</th>
                            <th
                                class="px-6 py-4 w-1/6 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                                عدد الطلاب</th>
                            <th
                                class="px-4 py-4 w-1/6 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                                الحالة</th>
                            {{-- <th
                                class="px-6 py-4 w-1/4 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                                المعلمين</th> --}}
                            <th
                                class="px-6 py-4 w-1/4 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                                الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="group in filteredGroups" :key="group.id">
                            <tr class="transition-colors hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class=" items-center">
                                        <div class="p-2 ml-1 bg-blue-100 rounded-lg inline-block">
                                            <i class="text-blue-600 fas fa-users"></i>
                                        </div>
                                        <div class="inline-block">
                                            <div class="text-sm font-medium text-gray-900" x-text="group.name"></div>
                                            <div class="text-sm text-gray-500"
                                                x-text="group.section ? `الشعبة ${group.section}` : ''"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class=" py-4">
                                    <span
                                        class="inline-flex py-3 px-4 text-xs font-semibold text-purple-800 bg-purple-100 rounded-full"
                                        x-text="group.grade_level"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-900"
                                            x-text="`${group.students_count}/${group.max_capacity}`"></span>
                                        <div class="w-16 h-2 ml-3 bg-gray-200 rounded-full">
                                            <div class="h-2 bg-blue-600 rounded-full"
                                                :style="`width: ${group.occupancy_percentage}%`"></div>
                                        </div>
                                        <span class="ml-2 text-xs text-gray-500"
                                            x-text="`${group.occupancy_percentage}%`"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span :class="{
                                        'bg-green-100 text-green-800': group.can_add_students,
                                        'bg-red-100 text-red-800': !group.can_add_students && group.is_active,
                                        'bg-gray-100 text-gray-800': !group.is_active
                                    }" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                        <span
                                            x-text="!group.is_active ? 'غير نشطة' : group.can_add_students ? 'متاحة' : 'ممتلئة'"></span>
                                    </span>
                                </td>
                                {{-- <td class="px-6 py-4">
                                    <div class="flex -space-x-2">
                                        <template x-for="teacher in group.teachers.slice(0, 3)" :key="teacher.id">
                                            <div class="flex items-center justify-center w-8 h-8 text-xs font-medium text-gray-700 bg-gray-300 border-2 border-white rounded-full"
                                                :title="teacher.name" x-text="teacher.name.charAt(0)"></div>
                                        </template>
                                        <div x-show="group.teachers.length > 3"
                                            class="flex items-center justify-center w-8 h-8 text-xs font-medium text-white bg-gray-500 border-2 border-white rounded-full"
                                            x-text="`+${group.teachers.length - 3}`"></div>
                                    </div>
                                </td> --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button @click="viewStudents(group)" class="flex items-center justify-center w-9
                                            h-9 text-blue-600 bg-blue-50 rounded-full hover:bg-blue-100
                                            hover:text-blue-800 transition-colors" title="عرض الطلاب">
                                            <i class="fas fa-users"></i>
                                        </button>

                                        <!-- زر جديد لإدارة المواد -->
                                        <button @click="viewSubjects(group)" class="flex items-center justify-center w-9
                                                    h-9 text-purple-600 bg-purple-50 rounded-full hover:bg-purple-100
                                                    hover:text-purple-800 transition-colors" title="إدارة المواد">
                                            <i class="fas fa-book"></i>
                                        </button>

                                        <button @click="editGroup(group)"
                                            class="p-2 text-green-600 transition-colors rounded-lg hover:text-green-800 hover:bg-green-50"
                                            title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="viewSchedule(group)"
                                            class="p-2 text-orange-600 transition-colors rounded-lg hover:text-orange-800 hover:bg-orange-50"
                                            title="الجدول الزمني">
                                            <i class="fas fa-calendar-alt"></i>
                                        </button>
                                        <button @click="deleteGroup(group)"
                                            class="p-2 text-red-600 transition-colors rounded-lg hover:text-red-800 hover:bg-red-50"
                                            title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div x-show="filteredGroups.length === 0" class="py-12 text-center">
                    <i class="mb-4 text-gray-300 fas fa-search fa-3x"></i>
                    <h3 class="text-lg font-medium text-gray-900">لا توجد نتائج</h3>
                    <p class="text-gray-600">لم يتم العثور على مجموعات تطابق معايير البحث</p>
                </div>
            </div>


            <!-- Modal إدارة مواد المجموعة -->
            <div x-show="showSubjectsModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                @click.self="showSubjectsModal = false">
                <div class="w-full max-w-5xl max-h-screen mx-4 overflow-y-auto bg-white shadow-2xl rounded-2xl fade-in"
                    @click.stop>
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-800" x-text="`مواد ${selectedGroup?.name}`">
                                </h3>
                                <p class="text-sm text-gray-600"
                                    x-text="`إدارة مواد المرحلة: ${selectedGroup?.grade_level}`"></p>
                            </div>
                            <div class="flex items-center gap-4">
                                <button @click="showAddSubjectModal = true" :disabled="loadingSubjects"
                                    class="flex items-center gap-2 px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                    <i class="fas fa-plus"></i>
                                    إضافة مادة
                                </button>
                                <button @click="showCopySubjectsModal = true" :disabled="loadingSubjects"
                                    class="flex items-center gap-2 px-4 py-2 text-white transition-colors bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50">
                                    <i class="fas fa-copy"></i>
                                    نسخ من مجموعة
                                </button>
                                <button @click="refreshSubjects()" :disabled="loadingSubjects"
                                    class="flex items-center gap-2 px-4 py-2 text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">
                                    <i class="fas fa-sync-alt" :class="{ 'animate-spin': loadingSubjects }"></i>
                                    تحديث
                                </button>
                                <button @click="showSubjectsModal = false"
                                    class="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Loading state للمواد -->
                        <div x-show="loadingSubjects" class="flex items-center justify-center p-8">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-6 h-6 border-4 border-blue-500 rounded-full border-t-transparent animate-spin">
                                </div>
                                <span class="text-gray-600">جاري تحميل المواد...</span>
                            </div>
                        </div>

                        <!-- محتوى المواد -->
                        <div x-show="!loadingSubjects">
                            <!-- إحصائيات سريعة -->
                            <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
                                <div class="p-4 bg-blue-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="text-blue-600 fas fa-book fa-2x"></i>
                                        <div class="mr-3">
                                            <p class="text-sm text-blue-600">إجمالي المواد</p>
                                            <p class="text-xl font-bold text-blue-800" x-text="groupSubjects.length">0
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 bg-green-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="text-green-600 fas fa-user-check fa-2x"></i>
                                        <div class="mr-3">
                                            <p class="text-sm text-green-600">مواد لها مدرسين</p>
                                            <p class="text-xl font-bold text-green-800"
                                                x-text="groupSubjects.filter(s => s.teacher_id).length">0</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 bg-purple-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="text-purple-600 fas fa-toggle-on fa-2x"></i>
                                        <div class="mr-3">
                                            <p class="text-sm text-purple-600">مواد نشطة</p>
                                            <p class="text-xl font-bold text-purple-800"
                                                x-text="groupSubjects.filter(s => s.is_active).length">0</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- جدول المواد -->
                            <div class="overflow-x-auto bg-white border border-gray-200 rounded-lg">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">
                                                المادة</th>
                                            <th
                                                class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">
                                                المدرس</th>
                                            <th
                                                class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">
                                                الحالة</th>
                                            <th
                                                class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">
                                                الإجراءات
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="subject in groupSubjects" :key="subject.id">
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center">
                                                        <div class="p-2 ml-3 bg-blue-100 rounded-lg">
                                                            <i class="text-blue-600 fas fa-book"></i>
                                                        </div>
                                                        <div>
                                                            <div class="text-sm font-medium text-gray-900"
                                                                x-text="subject.subject_name"></div>
                                                            <div class="text-sm text-gray-500">مادة أساسية</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="text-sm"
                                                        :class="subject.teacher_id ? 'text-gray-900 font-medium' : 'text-gray-500'"
                                                        x-text="subject.teacher_name"></span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span :class="{
                                                            'bg-green-100 text-green-800': subject.is_active,
                                                            'bg-red-100 text-red-800': !subject.is_active
                                                        }"
                                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                                        <span x-text="subject.is_active ? 'نشطة' : 'معطلة'"></span>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-2">
                                                        <button @click="editGroupSubject(subject)"
                                                            class="p-1 text-blue-600 rounded hover:text-blue-800 hover:bg-blue-50"
                                                            title="تحرير المادة">
                                                            <i class="text-sm fas fa-edit"></i>
                                                        </button>
                                                        <button @click="toggleSubjectStatus(subject)"
                                                            :class="subject.is_active ? 'text-yellow-600 hover:text-yellow-800 hover:bg-yellow-50' : 'text-green-600 hover:text-green-800 hover:bg-green-50'"
                                                            class="p-1 rounded"
                                                            :title="subject.is_active ? 'تعطيل المادة' : 'تفعيل المادة'">
                                                            <i class="text-sm"
                                                                :class="subject.is_active ? 'fas fa-toggle-off' : 'fas fa-toggle-on'"></i>
                                                        </button>
                                                        <button @click="removeGroupSubject(subject)"
                                                            class="p-1 text-red-600 rounded hover:text-red-800 hover:bg-red-50"
                                                            title="إزالة المادة">
                                                            <i class="text-sm fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>

                                <!-- Empty State للمواد -->
                                <div x-show="groupSubjects.length === 0" class="py-8 text-center">
                                    <i class="mb-3 text-gray-300 fas fa-book fa-3x"></i>
                                    <h3 class="text-lg font-medium text-gray-900">لا توجد مواد</h3>
                                    <p class="text-gray-600">لم يتم إضافة أي مواد لهذه المجموعة بعد</p>
                                    <button @click="showAddSubjectModal = true"
                                        class="px-4 py-2 mt-4 text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50">
                                        إضافة أول مادة
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal إضافة مادة للمجموعة -->
            <div x-show="showAddSubjectModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                @click.self="showAddSubjectModal = false">
                <div class="w-full max-w-md mx-4 bg-white shadow-2xl rounded-2xl fade-in" @click.stop>
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">إضافة مادة للمجموعة</h3>
                            <button @click="showAddSubjectModal = false"
                                class="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <p class="text-sm text-gray-600" x-text="`إضافة مادة جديدة إلى ${selectedGroup?.name}`"></p>
                    </div>

                    <div class="p-6">
                        <form @submit.prevent="confirmAddSubject()" class="space-y-4">
                            <!-- اختيار المادة -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">اختر المادة</label>
                                <select x-model="selectedSubjectId" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">اختر مادة...</option>
                                    <template x-for="subject in availableSubjects" :key="subject.id">
                                        <option :value="subject.id" x-text="subject.name"></option>
                                    </template>
                                </select>

                                <!-- عرض معلومات إضافية -->
                                <div x-show="availableSubjects.length > 0" class="mt-2 text-xs text-gray-500">
                                    <span x-text="`${availableSubjects.length} مادة متاحة للإضافة`"></span>
                                </div>
                            </div>

                            <!-- اختيار المدرس (اختياري) -->
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">المدرس (اختياري)</label>
                                <select x-model="selectedTeacherId"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">بدون مدرس (يمكن تعيينه لاحقاً)</option>
                                    <template x-for="teacher in availableTeachers" :key="teacher.id">
                                        <option :value="teacher.id"
                                            x-text="`${teacher.name} ${teacher.specialization ? '(' + teacher.specialization + ')' : ''}`">
                                        </option>
                                    </template>
                                </select>
                            </div>

                            <!-- حالة المادة -->
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" x-model="newSubjectActive"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm font-medium text-gray-700">مادة نشطة</span>
                                </label>
                            </div>

                            <!-- معلومات المادة المختارة -->
                            <div x-show="selectedSubjectId" class="p-4 bg-gray-50 rounded-lg">
                                <template x-for="subject in availableSubjects" :key="subject.id">
                                    <div x-show="subject.id == selectedSubjectId">
                                        <h4 class="font-medium text-gray-800" x-text="subject.name"></h4>
                                        <p class="text-sm text-gray-600" x-text="subject.description || 'لا يوجد وصف'">
                                        </p>
                                    </div>
                                </template>
                            </div>

                            <!-- رسالة لعدم وجود مواد -->
                            <div x-show="availableSubjects.length === 0"
                                class="p-4 text-center bg-yellow-50 rounded-lg">
                                <i class="mb-2 text-yellow-500 fas fa-exclamation-triangle"></i>
                                <p class="text-sm text-yellow-700">لا يوجد مواد متاحة للإضافة</p>
                                <p class="text-xs text-yellow-600">جميع مواد هذه المرحلة مضافة بالفعل للمجموعة</p>
                            </div>

                            <div class="flex justify-end gap-4 pt-4 border-t border-gray-200">
                                <button type="button" @click="showAddSubjectModal = false"
                                    class="px-4 py-2 text-gray-700 transition-colors border border-gray-300 rounded-lg hover:bg-gray-50">
                                    إلغاء
                                </button>
                                <button type="submit" :disabled="addingSubject || !selectedSubjectId"
                                    class="px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                    <span x-text="addingSubject ? 'جاري الإضافة...' : 'إضافة المادة'"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal تحرير مادة المجموعة -->
            <div x-show="showEditSubjectModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                @click.self="showEditSubjectModal = false">
                <div class="w-full max-w-md mx-4 bg-white shadow-2xl rounded-2xl fade-in" @click.stop>
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">تحرير المادة</h3>
                            <button @click="showEditSubjectModal = false"
                                class="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <p class="text-sm text-gray-600" x-text="`تحرير ${editingSubject?.subject_name || 'المادة'}`">
                        </p>
                    </div>

                    <form @submit.prevent="updateGroupSubject()" class="p-6 space-y-4">
                        <!-- اسم المادة (للعرض فقط) -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">المادة</label>
                            <div class="px-4 py-3 bg-gray-100 border border-gray-300 rounded-lg">
                                <span class="text-gray-800" x-text="editingSubject?.subject_name"></span>
                            </div>
                        </div>

                        <!-- تغيير المدرس -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">المدرس</label>
                            <select x-model="editingSubject.teacher_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">بدون مدرس</option>
                                <template x-for="teacher in availableTeachers" :key="teacher.id">
                                    <option :value="teacher.id"
                                        x-text="`${teacher.name} ${teacher.specialization ? '(' + teacher.specialization + ')' : ''}`">
                                    </option>
                                </template>
                            </select>
                        </div>

                        <!-- حالة المادة -->
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="editingSubject.is_active"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-sm font-medium text-gray-700">مادة نشطة</span>
                            </label>
                        </div>

                        <div class="flex justify-end gap-4 pt-4 border-t border-gray-200">
                            <button type="button" @click="showEditSubjectModal = false"
                                class="px-4 py-2 text-gray-700 transition-colors border border-gray-300 rounded-lg hover:bg-gray-50">
                                إلغاء
                            </button>
                            <button type="submit" :disabled="updatingSubject"
                                class="px-4 py-2 text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">
                                <span x-text="updatingSubject ? 'جاري التحديث...' : 'حفظ التعديلات'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal نسخ المواد من مجموعة أخرى -->
            <div x-show="showCopySubjectsModal" x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                @click.self="showCopySubjectsModal = false">
                <div class="w-full max-w-lg mx-4 bg-white shadow-2xl rounded-2xl fade-in" @click.stop>
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800">نسخ المواد من مجموعة أخرى</h3>
                            <button @click="showCopySubjectsModal = false"
                                class="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <p class="text-sm text-gray-600">نسخ مواد من مجموعة أخرى لهذه المجموعة</p>
                    </div>

                    <form @submit.prevent="confirmCopySubjects()" class="p-6 space-y-4">
                        <!-- اختيار المجموعة المصدر -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">المجموعة المصدر</label>
                            <select x-model="sourceGroupId" @change="loadSourceGroupSubjects()" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">اختر المجموعة...</option>
                                <template x-for="group in availableGroups" :key="group.id">
                                    <option :value="group.id" x-text="group.display_name"></option>
                                </template>
                            </select>
                        </div>

                        <!-- المواد المتاحة للنسخ -->
                        <div x-show="sourceGroupSubjects.length > 0">
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                المواد المراد نسخها
                                <span class="text-xs text-gray-500">(اختر واحدة أو أكثر)</span>
                            </label>
                            <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-lg">
                                <template x-for="subject in sourceGroupSubjects" :key="subject.id">
                                    <label class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" :value="subject.subject_id" x-model="selectedSubjectIds"
                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-gray-900"
                                                x-text="subject.subject_name"></div>
                                            <div class="text-xs text-gray-500"
                                                x-text="`المدرس: ${subject.teacher_name || 'غير محدد'}`"></div>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </div>

                        <!-- خيارات النسخ -->
                        <div x-show="selectedSubjectIds.length > 0" class="space-y-3">
                            <h4 class="text-sm font-medium text-gray-700">خيارات النسخ:</h4>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="copyTeachers"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-sm text-gray-700">نسخ المدرسين المعينين</span>
                            </label>
                        </div>

                        <!-- معلومات النسخ -->
                        <div x-show="selectedSubjectIds.length > 0" class="p-3 bg-blue-50 rounded-lg">
                            <p class="text-sm text-blue-700">
                                سيتم نسخ <span x-text="selectedSubjectIds.length"></span> مادة إلى هذه المجموعة
                            </p>
                        </div>

                        <div class="flex justify-end gap-4 pt-4 border-t border-gray-200">
                            <button type="button" @click="showCopySubjectsModal = false"
                                class="px-4 py-2 text-gray-700 transition-colors border border-gray-300 rounded-lg hover:bg-gray-50">
                                إلغاء
                            </button>
                            <button type="submit" :disabled="copyingSubjects || selectedSubjectIds.length === 0"
                                class="px-4 py-2 text-white transition-colors bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50">
                                <span
                                    x-text="copyingSubjects ? 'جاري النسخ...' : `نسخ ${selectedSubjectIds.length} مادة`"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- عرض البطاقات -->
            <div x-show="viewMode === 'cards'" class="p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <template x-for="group in filteredGroups" :key="group.id">
                        <div class="p-6 transition-shadow bg-white border border-gray-200 rounded-xl hover:shadow-lg">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-800" x-text="group.name"></h4>
                                    <span class="text-sm text-gray-500" x-text="group.grade_level"></span>
                                </div>
                                <span :class="{
                                    'bg-green-100 text-green-800': group.can_add_students,
                                    'bg-red-100 text-red-800': !group.can_add_students
                                }" class="px-2 py-1 text-xs font-semibold rounded-full"
                                    x-text="group.can_add_students ? 'متاحة' : 'ممتلئة'"></span>
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between mb-1 text-sm text-gray-600">
                                    <span>عدد الطلاب</span>
                                    <span x-text="`${group.students_count}/${group.max_capacity}`"></span>
                                </div>
                                <div class="w-full h-2 bg-gray-200 rounded-full">
                                    <div class="h-2 bg-blue-600 rounded-full"
                                        :style="`width: ${group.occupancy_percentage}%`"></div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex -space-x-2">
                                    <template x-for="teacher in group.teachers.slice(0, 2)" :key="teacher.id">
                                        <div class="flex items-center justify-center w-8 h-8 text-xs font-medium text-gray-700 bg-gray-300 border-2 border-white rounded-full"
                                            x-text="teacher.name.charAt(0)"></div>
                                    </template>
                                </div>

                                <div class="flex gap-1">
                                    <button @click="viewStudents(group)"
                                        class="p-1 text-blue-600 rounded hover:text-blue-800" title="عرض الطلاب">
                                        <i class="text-sm fas fa-users"></i>
                                    </button>
                                    <!-- زر جديد لإدارة المواد في عرض البطاقات -->
                                    <button @click="viewSubjects(group)"
                                        class="p-1 text-purple-600 rounded hover:text-purple-800" title="إدارة المواد">
                                        <i class="text-sm fas fa-book"></i>
                                    </button>
                                    <button @click="editGroup(group)"
                                        class="p-1 text-green-600 rounded hover:text-green-800" title="تعديل">
                                        <i class="text-sm fas fa-edit"></i>
                                    </button>
                                    <button @click="deleteGroup(group)"
                                        class="p-1 text-red-600 rounded hover:text-red-800" title="حذف">
                                        <i class="text-sm fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State for Cards -->
                <div x-show="filteredGroups.length === 0" class="py-12 text-center">
                    <i class="mb-4 text-gray-300 fas fa-search fa-3x"></i>
                    <h3 class="text-lg font-medium text-gray-900">لا توجد نتائج</h3>
                    <p class="text-gray-600">لم يتم العثور على مجموعات تطابق معايير البحث</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal إنشاء مجموعة جديدة -->
    <div x-show="showCreateModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="showCreateModal = false">
        <div class="w-full max-w-2xl max-h-screen mx-4 overflow-y-auto bg-white shadow-2xl rounded-2xl fade-in"
            @click.stop>
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800">إنشاء مجموعة جديدة</h3>
                    <button @click="showCreateModal = false"
                        class="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form @submit.prevent="createGroup()" class="p-6 space-y-6">
                <!-- معلومات أساسية -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">اسم المجموعة *</label>
                        <input type="text" x-model="newGroup.name" required
                            class="w-full px-4 py-3 transition-colors border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="مثال: الصف الأول - الشعبة أ">
                        <div x-show="errors.name" class="mt-1 text-sm text-red-600" x-text="errors.name"></div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">المرحلة الدراسية *</label>
                        <select x-model="newGroup.grade_level" required
                            class="w-full px-4 py-3 transition-colors border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">اختر المرحلة</option>
                            <option value="الصف الأول">الصف الأول</option>
                            <option value="الصف الثاني">الصف الثاني</option>
                            <option value="الصف الثالث">الصف الثالث</option>
                            <option value="الصف الرابع">الصف الرابع</option>
                            <option value="الصف الخامس">الصف الخامس</option>
                            <option value="الصف السادس">الصف السادس</option>
                            <option value="الصف السابع">الصف السابع</option>
                            <option value="الصف الثامن">الصف الثامن</option>
                            <option value="الصف التاسع">الصف التاسع</option>
                            <option value="الصف العاشر">الصف العاشر</option>
                        </select>
                        <div x-show="errors.grade_level" class="mt-1 text-sm text-red-600" x-text="errors.grade_level">
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">الشعبة</label>
                        <input type="text" x-model="newGroup.section"
                            class="w-full px-4 py-3 transition-colors border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="مثال: أ، ب، ج (اختياري)">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">الحد الأقصى للطلاب *</label>
                        <input type="number" x-model="newGroup.max_capacity" required min="1" max="50"
                            class="w-full px-4 py-3 transition-colors border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="30">
                        <div x-show="errors.max_capacity" class="mt-1 text-sm text-red-600"
                            x-text="errors.max_capacity"></div>
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">وصف المجموعة</label>
                    <textarea x-model="newGroup.description" rows="3"
                        class="w-full px-4 py-3 transition-colors border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="وصف مختصر عن المجموعة..."></textarea>
                </div>

                <!-- حالة المجموعة -->
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="newGroup.is_active"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">مجموعة نشطة</span>
                    </label>
                </div>

                <!-- أزرار الإجراءات -->
                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                    <button type="button" @click="showCreateModal = false"
                        class="px-6 py-3 font-medium text-gray-700 transition-colors border border-gray-300 rounded-lg hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" :disabled="creating"
                        class="flex items-center gap-2 px-6 py-3 font-medium text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        <i class="fas fa-plus" :class="{ 'animate-spin fa-spinner': creating }"></i>
                        <span x-text="creating ? 'جاري الإنشاء...' : 'إنشاء المجموعة'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal تعديل المجموعة -->
    <div x-show="showEditModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="showEditModal = false">
        <div class="w-full max-w-2xl max-h-screen mx-4 overflow-y-auto bg-white shadow-2xl rounded-2xl fade-in"
            @click.stop>
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-800">تعديل المجموعة</h3>
                    <button @click="showEditModal = false"
                        class="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form @submit.prevent="updateGroup()" class="p-6 space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">اسم المجموعة *</label>
                        <input type="text" x-model="editingGroup.name" required
                            class="w-full px-4 py-3 transition-colors border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <div x-show="editErrors.name" class="mt-1 text-sm text-red-600" x-text="editErrors.name"></div>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">المرحلة الدراسية *</label>
                        <select x-model="editingGroup.grade_level" required
                            class="w-full px-4 py-3 transition-colors border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="الصف الأول">الصف الأول</option>
                            <option value="الصف الثاني">الصف الثاني</option>
                            <option value="الصف الثالث">الصف الثالث</option>
                            <option value="الصف الرابع">الصف الرابع</option>
                            <option value="الصف الخامس">الصف الخامس</option>
                            <option value="الصف السادس">الصف السادس</option>
                            <option value="الصف السابع">الصف السابع</option>
                            <option value="الصف الثامن">الصف الثامن</option>
                            <option value="الصف التاسع">الصف التاسع</option>
                            <option value="الصف العاشر">الصف العاشر</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">الشعبة</label>
                        <input type="text" x-model="editingGroup.section"
                            class="w-full px-4 py-3 transition-colors border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">الحد الأقصى للطلاب *</label>
                        <input type="number" x-model="editingGroup.max_capacity" required min="1" max="50"
                            class="w-full px-4 py-3 transition-colors border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <div x-show="editErrors.max_capacity" class="mt-1 text-sm text-red-600"
                            x-text="editErrors.max_capacity"></div>
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">وصف المجموعة</label>
                    <textarea x-model="editingGroup.description" rows="3"
                        class="w-full px-4 py-3 transition-colors border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="editingGroup.is_active"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">مجموعة نشطة</span>
                    </label>
                </div>

                <div class="flex justify-end gap-4 pt-6 border-t border-gray-200">
                    <button type="button" @click="showEditModal = false"
                        class="px-6 py-3 font-medium text-gray-700 transition-colors border border-gray-300 rounded-lg hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" :disabled="updating"
                        class="flex items-center gap-2 px-6 py-3 font-medium text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">
                        <i class="fas fa-save" :class="{ 'animate-spin fa-spinner': updating }"></i>
                        <span x-text="updating ? 'جاري التحديث...' : 'حفظ التعديلات'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal عرض الطلاب -->
    <div x-show="showStudentsModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="showStudentsModal = false">
        <div class="w-full max-w-6xl max-h-screen mx-4 overflow-y-auto bg-white shadow-2xl rounded-2xl fade-in"
            @click.stop>
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800" x-text="`طلاب ${selectedGroup?.name}`"></h3>
                        <p class="text-sm text-gray-600"
                            x-text="`${selectedGroup?.students_count} طالب من أصل ${selectedGroup?.max_capacity}`"></p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button @click="showAddStudentModal = true" :disabled="loadingStudents"
                            class="flex items-center gap-2 px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            <i class="fas fa-user-plus"></i>
                            إضافة طالب
                        </button>
                        <button @click="refreshStudents()" :disabled="loadingStudents"
                            class="flex items-center gap-2 px-4 py-2 text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-50">
                            <i class="fas fa-sync-alt" :class="{ 'animate-spin': loadingStudents }"></i>
                            تحديث
                        </button>
                        <button @click="showStudentsModal = false"
                            class="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- Loading state للطلاب -->
                <div x-show="loadingStudents" class="flex items-center justify-center p-8">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 border-4 border-blue-500 rounded-full border-t-transparent animate-spin">
                        </div>
                        <span class="text-gray-600">جاري تحميل الطلاب...</span>
                    </div>
                </div>

                <!-- محتوى الطلاب -->
                <div x-show="!loadingStudents">
                    <!-- فلتر الطلاب -->
                    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
                        <input type="text" x-model="studentSearchTerm" placeholder="البحث عن طالب..."
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <select x-model="studentStatusFilter"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">جميع الحالات</option>
                            <option value="active">نشط</option>
                            <option value="inactive">غير نشط</option>
                        </select>
                        <button @click="exportStudentsList()"
                            class="flex items-center gap-2 px-4 py-2 text-white transition-colors bg-green-600 rounded-lg hover:bg-green-700">
                            <i class="fas fa-file-export"></i>
                            تصدير القائمة
                        </button>
                    </div>

                    <!-- جدول الطلاب -->
                    <div class="overflow-x-auto">
                        <table class="w-full border border-gray-200 rounded-lg">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">#</th>
                                    <th class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">اسم
                                        الطالب</th>
                                    <th class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">البريد
                                        الإلكتروني</th>
                                    <th class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">العمر
                                    </th>
                                    <th class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">تاريخ
                                        الالتحاق</th>
                                    <th class="px-4 py-3 text-xs font-medium text-right text-gray-500 uppercase">
                                        الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(student, index) in filteredStudents" :key="student.id">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm text-gray-900" x-text="index + 1"></td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <div
                                                    class="flex items-center justify-center w-8 h-8 ml-3 bg-blue-100 rounded-full">
                                                    <i class="text-sm text-blue-600 fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900"
                                                        x-text="student.name"></div>
                                                    <div class="text-sm text-gray-500"
                                                        x-text="student.group_name || 'غير محدد'"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500"
                                            x-text="student.email || 'غير محدد'"></td>
                                        <td class="px-4 py-3 text-sm text-gray-900" x-text="student.age || 'غير محدد'">
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500" x-text="student.enrollment_date">
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <button @click="editStudent(student)"
                                                    class="p-1 text-blue-600 rounded hover:text-blue-800 hover:bg-blue-50"
                                                    title="تعديل">
                                                    <i class="text-sm fas fa-edit"></i>
                                                </button>
                                                <button @click="moveStudent(student)"
                                                    class="p-1 text-green-600 rounded hover:text-green-800 hover:bg-green-50"
                                                    title="نقل لمجموعة أخرى">
                                                    <i class="text-sm fas fa-exchange-alt"></i>
                                                </button>
                                                <button @click="removeStudent(student)"
                                                    class="p-1 text-red-600 rounded hover:text-red-800 hover:bg-red-50"
                                                    title="إزالة من المجموعة">
                                                    <i class="text-sm fas fa-user-minus"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <!-- Empty State للطلاب -->
                        <div x-show="filteredStudents.length === 0" class="py-8 text-center">
                            <i class="mb-3 text-gray-300 fas fa-users fa-2x"></i>
                            <p class="text-gray-600">لا يوجد طلاب في هذه المجموعة</p>
                            <button @click="showAddStudentModal = true"
                                class="mt-4 px-4 py-2 text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50">
                                إضافة أول طالب
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal إضافة طالب للمجموعة -->
    <div x-show="showAddStudentModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="showAddStudentModal = false">
        <div class="w-full max-w-md mx-4 bg-white shadow-2xl rounded-2xl fade-in" @click.stop>
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">إضافة طالب للمجموعة</h3>
                    <button @click="showAddStudentModal = false"
                        class="p-2 text-gray-400 transition-colors rounded-lg hover:text-gray-600 hover:bg-gray-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <p class="text-sm text-gray-600" x-text="`إضافة طالب جديد إلى ${selectedGroup?.name}`"></p>
            </div>

            <div class="p-6">
                <!-- Loading state للطلاب المتاحين -->
                <div x-show="loadingAvailableStudents" class="flex items-center justify-center p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-5 h-5 border-4 border-blue-500 rounded-full border-t-transparent animate-spin">
                        </div>
                        <span class="text-gray-600">جاري تحميل الطلاب المتاحين...</span>
                    </div>
                </div>

                <!-- قائمة الطلاب المتاحين -->
                <div x-show="!loadingAvailableStudents">
                    <form @submit.prevent="confirmAddStudent()" class="space-y-4">
                        <!-- تحديث template اختيار الطالب في الـ view -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">اختر الطالب</label>
                            <select x-model="selectedStudentId" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">اختر طالب...</option>
                                <template x-for="student in availableStudents" :key="student.id">
                                    <option :value="student.id" x-text="student.display_name"></option>
                                </template>
                            </select>

                            <!-- عرض معلومات إضافية -->
                            <div x-show="availableStudents.length > 0" class="mt-2 text-xs text-gray-500">
                                <span x-text="`${availableStudents.length} طالب متاح للإضافة`"></span>
                            </div>
                        </div>

                        <!-- معلومات الطالب المختار محدثة -->
                        <div x-show="selectedStudentId" class="p-4 bg-gray-50 rounded-lg">
                            <template x-for="student in availableStudents" :key="student.id">
                                <div x-show="student.id == selectedStudentId">
                                    <h4 class="font-medium text-gray-800" x-text="student.name"></h4>
                                    <p class="text-sm text-gray-600" x-text="student.email || 'لا يوجد بريد إلكتروني'">
                                    </p>
                                    <p class="text-sm text-gray-600" x-text="`العمر: ${student.age || 'غير محدد'}`"></p>
                                    <p class="text-sm text-blue-600" x-text="`المصدر: ${student.source}`"></p>
                                    <p x-show="student.admission_date" class="text-sm text-gray-500"
                                        x-text="`تاريخ الطلب: ${student.admission_date}`"></p>
                                </div>
                            </template>
                        </div>

                        <!-- رسالة محدثة لعدم وجود طلاب -->
                        <div x-show="availableStudents.length === 0" class="p-4 text-center bg-yellow-50 rounded-lg">
                            <i class="mb-2 text-yellow-500 fas fa-exclamation-triangle"></i>
                            <p class="text-sm text-yellow-700"
                                x-text="`لا يوجد طلاب متاحين لمرحلة ${selectedGroup?.grade_level}`"></p>
                            <p class="text-xs text-yellow-600">تأكد من وجود طلبات انتساب مقبولة لهذه المرحلة</p>
                        </div>

                        <div class="flex justify-end gap-4 pt-4 border-t border-gray-200">
                            <button type="button" @click="showAddStudentModal = false"
                                class="px-4 py-2 text-gray-700 transition-colors border border-gray-300 rounded-lg hover:bg-gray-50">
                                إلغاء
                            </button>
                            <button type="submit" :disabled="addingStudent || !selectedStudentId"
                                class="px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                <span x-text="addingStudent ? 'جاري الإضافة...' : 'إضافة الطالب'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal نقل الطالب -->
    <div x-show="showMoveStudentModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="showMoveStudentModal = false">
        <div class="w-full max-w-md mx-4 bg-white shadow-2xl rounded-2xl fade-in" @click.stop>
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">نقل الطالب</h3>
                <p class="text-sm text-gray-600" x-text="`نقل ${selectedStudent?.name} إلى مجموعة أخرى`"></p>
            </div>

            <form @submit.prevent="confirmMoveStudent()" class="p-6 space-y-4">
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">اختر المجموعة الجديدة</label>
                    <select x-model="targetGroupId" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">اختر المجموعة</option>
                        <template x-for="group in availableGroups" :key="group.id">
                            <option :value="group.id" x-text="group.display_name"></option>
                        </template>
                    </select>
                </div>

                <!-- معلومات المجموعة المختارة -->
                <div x-show="targetGroupId" class="p-4 bg-blue-50 rounded-lg">
                    <template x-for="group in availableGroups" :key="group.id">
                        <div x-show="group.id == targetGroupId">
                            <h4 class="font-medium text-blue-800" x-text="group.name"></h4>
                            <p class="text-sm text-blue-600"
                                x-text="`${group.students_count}/${group.max_capacity} طالب`"></p>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-gray-200">
                    <button type="button" @click="showMoveStudentModal = false"
                        class="px-4 py-2 text-gray-700 transition-colors border border-gray-300 rounded-lg hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" :disabled="movingStudent"
                        class="px-4 py-2 text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                        <span x-text="movingStudent ? 'جاري النقل...' : 'نقل الطالب'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- رسائل التنبيه -->
    <div x-show="showAlert" x-cloak class="fixed z-50 transform -translate-x-1/2 top-4 left-1/2 fade-in">
        <div :class="{
            'bg-green-100 border-green-500 text-green-700': alertType === 'success',
            'bg-red-100 border-red-500 text-red-700': alertType === 'error',
            'bg-yellow-100 border-yellow-500 text-yellow-700': alertType === 'warning'
        }" class="max-w-md p-4 border-r-4 rounded-lg shadow-lg">
            <div class="flex items-center">
                <i :class="{
                    'fas fa-check-circle text-green-500': alertType === 'success',
                    'fas fa-exclamation-circle text-red-500': alertType === 'error',
                    'fas fa-exclamation-triangle text-yellow-500': alertType === 'warning'
                }" class="ml-3"></i>
                <p x-text="alertMessage" class="font-medium"></p>
            </div>
        </div>
    </div>
</div>



@endsection

@push('scripts')
<script>
    function groupsManager() {
    return {
    // حالة التحميل
    loading: true,
    refreshing: false,
    creating: false,
    updating: false,
    loadingStudents: false,
    loadingAvailableStudents: false,
    addingStudent: false,
    movingStudent: false,

    // حالة التحميل للمواد
    loadingSubjects: false,
    addingSubject: false,
    updatingSubject: false,
    copyingSubjects: false,

    // البيانات
    groups: [],
    students: [],
    availableStudents: [],
    availableGroups: [],

    // بيانات المواد
    groupSubjects: [],
    availableSubjects: [],
    availableTeachers: [],
    sourceGroupSubjects: [],

    stats: {
    total_groups: window.initialStats.total_groups || 0,
    total_students: window.initialStats.total_students || 0,
    full_groups: window.initialStats.full_groups || 0,
    today_lectures: window.initialStats.today_lectures || 0,
    available_groups: window.initialStats.available_groups || 0,
    active_groups: window.initialStats.active_groups || 0,
    inactive_groups: window.initialStats.inactive_groups || 0,
    occupancy_rate: window.initialStats.occupancy_rate || 0
    },

    // المتغيرات
    viewMode: 'table',
    searchTerm: '',
    gradeFilter: '',
    statusFilter: '',
    sortBy: 'name',

    // Modals
    showCreateModal: false,
    showEditModal: false,
    showStudentsModal: false,
    showAddStudentModal: false,
    showMoveStudentModal: false,

    // Modals للمواد
    showSubjectsModal: false,
    showAddSubjectModal: false,
    showEditSubjectModal: false,
    showCopySubjectsModal: false,

    // بيانات النماذج
    newGroup: {
    name: '',
    grade_level: '',
    section: '',
    max_capacity: 30,
    description: '',
    is_active: true
    },
    editingGroup: {},
    selectedGroup: null,
    selectedStudent: null,
    selectedStudentId: '',
    targetGroupId: '',

    // بيانات نماذج المواد
    selectedSubjectId: '',
    selectedTeacherId: '',
    newSubjectActive: true,
    editingSubject: {},
    sourceGroupId: '',
    selectedSubjectIds: [],
    copyTeachers: false,

    // فلترة الطلاب
    studentSearchTerm: '',
    studentStatusFilter: '',

    // الأخطاء
    errors: {},
    editErrors: {},

    // التنبيهات
    showAlert: false,
    alertType: 'success',
    alertMessage: '',

    // Computed properties
    get filteredGroups() {
    return this.groups.filter(group => {
    const matchesSearch = group.name.toLowerCase().includes(this.searchTerm.toLowerCase());
    const matchesGrade = !this.gradeFilter || group.grade_level === this.gradeFilter;
    const matchesStatus = !this.statusFilter ||
    (this.statusFilter === 'active' && group.is_active) ||
    (this.statusFilter === 'full' && !group.can_add_students && group.is_active) ||
    (this.statusFilter === 'available' && group.can_add_students);

    return matchesSearch && matchesGrade && matchesStatus;
    }).sort((a, b) => {
    switch(this.sortBy) {
    case 'students_count':
    return b.students_count - a.students_count;
    case 'created_at':
    return new Date(b.created_at) - new Date(a.created_at);
    case 'grade_level':
    return a.grade_level.localeCompare(b.grade_level);
    default:
    return a.name.localeCompare(b.name);
    }
    });
    },

    get filteredStudents() {
    return this.students.filter(student => {
    const matchesSearch = student.name.toLowerCase().includes(this.studentSearchTerm.toLowerCase()) ||
    (student.email && student.email.toLowerCase().includes(this.studentSearchTerm.toLowerCase()));
    const matchesStatus = !this.studentStatusFilter ||
    (this.studentStatusFilter === 'active' && student.is_active) ||
    (this.studentStatusFilter === 'inactive' && !student.is_active);

    return matchesSearch && matchesStatus;
    });
    },

    // تحميل البيانات
    async loadData() {
    this.loading = true;
    try {
    const response = await fetch(window.routes.groupsData, {
    headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
    });

    const data = await response.json();

    if (data.success) {
    this.groups = data.groups;
    this.stats = { ...this.stats, ...data.stats };
    } else {
    throw new Error(data.message || 'فشل في تحميل البيانات');
    }
    } catch (error) {
    console.error('Error loading data:', error);
    this.showAlertMessage('error', 'حدث خطأ في تحميل البيانات');
    } finally {
    this.loading = false;
    }
    },

    // تحديث البيانات
    async refreshData() {
    this.refreshing = true;
    await this.loadData();
    this.refreshing = false;
    this.showAlertMessage('success', 'تم تحديث البيانات بنجاح');
    },

    // إنشاء مجموعة جديدة
    async createGroup() {
    this.creating = true;
    this.errors = {};

    try {
    const response = await fetch(window.routes.groupsStore, {
    method: 'POST',
    headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'Accept': 'application/json'
    },
    body: JSON.stringify(this.newGroup)
    });

    const data = await response.json();

    if (data.success) {
    this.groups.push(data.group);
    this.stats.total_groups++;
    this.resetNewGroup();
    this.showCreateModal = false;
    this.showAlertMessage('success', data.message);
    } else {
    if (data.errors) {
    this.errors = data.errors;
    }
    throw new Error(data.message || 'فشل في إنشاء المجموعة');
    }
    } catch (error) {
    console.error('Error creating group:', error);
    this.showAlertMessage('error', error.message);
    } finally {
    this.creating = false;
    }
    },

    // تعديل المجموعة
    editGroup(group) {
    this.editingGroup = { ...group };
    this.editErrors = {};
    this.showEditModal = true;
    },

    async updateGroup() {
    this.updating = true;
    this.editErrors = {};

    try {
    const response = await fetch(`${window.routes.groupsUpdate}/${this.editingGroup.id}`, {
    method: 'PUT',
    headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'Accept': 'application/json'
    },
    body: JSON.stringify(this.editingGroup)
    });

    const data = await response.json();

    if (data.success) {
    const index = this.groups.findIndex(g => g.id === this.editingGroup.id);
    if (index !== -1) {
    this.groups[index] = { ...this.groups[index], ...data.group };
    }
    this.showEditModal = false;
    this.showAlertMessage('success', data.message);
    } else {
    if (data.errors) {
    this.editErrors = data.errors;
    }
    throw new Error(data.message || 'فشل في تحديث المجموعة');
    }
    } catch (error) {
    console.error('Error updating group:', error);
    this.showAlertMessage('error', error.message);
    } finally {
    this.updating = false;
    }
    },

    // حذف المجموعة
    async deleteGroup(group) {
    if (!confirm(`هل أنت متأكد من حذف مجموعة "${group.name}"؟`)) {
    return;
    }

    try {
    const response = await fetch(`${window.routes.groupsDestroy}/${group.id}`, {
    method: 'DELETE',
    headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'Accept': 'application/json'
    }
    });

    const data = await response.json();

    if (data.success) {
    const index = this.groups.findIndex(g => g.id === group.id);
    if (index !== -1) {
    this.groups.splice(index, 1);
    this.stats.total_groups--;
    }
    this.showAlertMessage('success', data.message);
    } else {
    throw new Error(data.message || 'فشل في حذف المجموعة');
    }
    } catch (error) {
    console.error('Error deleting group:', error);
    this.showAlertMessage('error', error.message);
    }
    },

    // عرض الطلاب
    async viewStudents(group) {
    this.selectedGroup = group;
    this.showStudentsModal = true;
    await this.loadStudents(group.id);
    },

    async loadStudents(groupId) {
    this.loadingStudents = true;
    try {
    const response = await fetch(`${window.routes.groupStudents}/${groupId}/students`, {
    headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
    });

    const data = await response.json();

    if (data.success) {
    this.students = data.students;
    this.selectedGroup = { ...this.selectedGroup, ...data.group };
    } else {
    throw new Error(data.message || 'فشل في تحميل الطلاب');
    }
    } catch (error) {
    console.error('Error loading students:', error);
    this.showAlertMessage('error', 'حدث خطأ في تحميل الطلاب');
    } finally {
    this.loadingStudents = false;
    }
    },

    async refreshStudents() {
    if (this.selectedGroup) {
    await this.loadStudents(this.selectedGroup.id);
    this.showAlertMessage('success', 'تم تحديث قائمة الطلاب');
    }
    },

    // إضافة طالب للمجموعة
    async showAddStudentForm() {
    this.showAddStudentModal = true;
    this.selectedStudentId = '';
    await this.loadAvailableStudents();
    },

    async loadAvailableStudents() {
    this.loadingAvailableStudents = true;
    try {
    const url = `${window.routes.availableStudents}?group_id=${this.selectedGroup.id}`;

    const response = await fetch(url, {
    headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
    });

    const data = await response.json();

    if (data.success) {
    this.availableStudents = data.students;

    if (data.students.length === 0 && data.group_info) {
    this.showAlertMessage('warning',
    `لا يوجد طلاب متاحين لمرحلة ${data.group_info.grade_level}`);
    }
    } else {
    throw new Error(data.message || 'فشل في تحميل الطلاب المتاحين');
    }
    } catch (error) {
    console.error('Error loading available students:', error);
    this.showAlertMessage('error', 'حدث خطأ في تحميل الطلاب المتاحين');
    } finally {
    this.loadingAvailableStudents = false;
    }
    },

    async confirmAddStudent() {
    if (!this.selectedStudentId) {
    this.showAlertMessage('error', 'يرجى اختيار طالب');
    return;
    }

    this.addingStudent = true;

    try {
    const response = await fetch(`${window.routes.addStudentToGroup}/${this.selectedGroup.id}/students`, {
    method: 'POST',
    headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'Accept': 'application/json'
    },
    body: JSON.stringify({
    student_id: this.selectedStudentId
    })
    });

    const data = await response.json();

    if (data.success) {
    this.showAddStudentModal = false;
    await this.loadStudents(this.selectedGroup.id);

    const groupIndex = this.groups.findIndex(g => g.id === this.selectedGroup.id);
    if (groupIndex !== -1) {
    this.groups[groupIndex].students_count++;
    }

    this.stats.total_students++;
    this.showAlertMessage('success', data.message);
    } else {
    throw new Error(data.message || 'فشل في إضافة الطالب');
    }
    } catch (error) {
    console.error('Error adding student:', error);
    this.showAlertMessage('error', error.message);
    } finally {
    this.addingStudent = false;
    }
    },

    // نقل الطالب
    async moveStudent(student) {
    this.selectedStudent = student;
    this.targetGroupId = '';

    try {
    const response = await fetch(`${window.routes.availableGroups}?exclude_group_id=${this.selectedGroup.id}`, {
    headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
    });

    const data = await response.json();

    if (data.success) {
    this.availableGroups = data.groups;
    this.showMoveStudentModal = true;
    } else {
    throw new Error(data.message);
    }
    } catch (error) {
    console.error('Error loading available groups:', error);
    this.showAlertMessage('error', 'حدث خطأ في تحميل المجموعات المتاحة');
    }
    },

    async confirmMoveStudent() {
    if (!this.targetGroupId) {
    this.showAlertMessage('error', 'يرجى اختيار المجموعة الجديدة');
    return;
    }

    this.movingStudent = true;

    try {
    const response = await
    fetch(`${window.routes.moveStudentToGroup}/${this.selectedGroup.id}/students/${this.selectedStudent.id}/move`, {
    method: 'POST',
    headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'Accept': 'application/json'
    },
    body: JSON.stringify({
    target_group_id: this.targetGroupId
    })
    });

    const data = await response.json();

    if (data.success) {
    this.students = this.students.filter(s => s.id !== this.selectedStudent.id);
    this.selectedGroup.students_count--;

    const groupIndex = this.groups.findIndex(g => g.id === this.selectedGroup.id);
    if (groupIndex !== -1) {
    this.groups[groupIndex].students_count--;
    }

    const targetGroupIndex = this.groups.findIndex(g => g.id == this.targetGroupId);
    if (targetGroupIndex !== -1) {
    this.groups[targetGroupIndex].students_count++;
    }

    this.showMoveStudentModal = false;
    this.showAlertMessage('success', data.message);
    } else {
    throw new Error(data.message || 'فشل في نقل الطالب');
    }
    } catch (error) {
    console.error('Error moving student:', error);
    this.showAlertMessage('error', error.message);
    } finally {
    this.movingStudent = false;
    }
    },

    // إزالة الطالب
    async removeStudent(student) {
    if (!confirm(`هل أنت متأكد من إزالة ${student.name} من المجموعة؟`)) {
    return;
    }

    try {
    const response = await fetch(`${window.routes.removeStudentFromGroup}/${this.selectedGroup.id}/students/${student.id}`,
    {
    method: 'DELETE',
    headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'Accept': 'application/json'
    }
    });

    const data = await response.json();

    if (data.success) {
    this.students = this.students.filter(s => s.id !== student.id);
    this.selectedGroup.students_count--;
    this.stats.total_students--;

    const groupIndex = this.groups.findIndex(g => g.id === this.selectedGroup.id);
    if (groupIndex !== -1) {
    this.groups[groupIndex].students_count--;
    }

    this.showAlertMessage('success', data.message);
    } else {
    throw new Error(data.message || 'فشل في إزالة الطالب');
    }
    } catch (error) {
    console.error('Error removing student:', error);
    this.showAlertMessage('error', error.message);
    }
    },

    // ========== إدارة المواد ==========

    // عرض مواد المجموعة
    async viewSubjects(group) {
    this.selectedGroup = group;
    this.showSubjectsModal = true;
    await this.loadGroupSubjects(group.id);
    },

    // تحميل مواد المجموعة
    async loadGroupSubjects(groupId) {
    this.loadingSubjects = true;
    try {
    const response = await fetch(`${window.routes.groupsUpdate}/${groupId}/subjects`, {
    headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
    });

    const data = await response.json();

    if (data.success) {
    this.groupSubjects = data.group_subjects;
    this.availableSubjects = data.available_subjects;
    this.availableTeachers = data.available_teachers;
    } else {
    throw new Error(data.message || 'فشل في تحميل مواد المجموعة');
    }
    } catch (error) {
    console.error('Error loading group subjects:', error);
    this.showAlertMessage('error', 'حدث خطأ في تحميل مواد المجموعة');
    } finally {
    this.loadingSubjects = false;
    }
    },

    // تحديث مواد المجموعة
    async refreshSubjects() {
    if (this.selectedGroup) {
    await this.loadGroupSubjects(this.selectedGroup.id);
    this.showAlertMessage('success', 'تم تحديث قائمة المواد');
    }
    },

    // إضافة مادة جديدة للمجموعة
    async confirmAddSubject() {
    if (!this.selectedSubjectId) {
    this.showAlertMessage('error', 'يرجى اختيار مادة');
    return;
    }

    this.addingSubject = true;

    try {
    const response = await fetch(`${window.routes.groupsUpdate}/${this.selectedGroup.id}/subjects`, {
    method: 'POST',
    headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'Accept': 'application/json'
    },
    body: JSON.stringify({
    subject_id: this.selectedSubjectId,
    teacher_id: this.selectedTeacherId || null,
    is_active: this.newSubjectActive
    })
    });

    const data = await response.json();

    if (data.success) {
    this.groupSubjects.push(data.group_subject);
    this.showAddSubjectModal = false;
    this.resetAddSubjectForm();
    this.showAlertMessage('success', data.message);

    // إعادة تحميل المواد المتاحة
    await this.loadGroupSubjects(this.selectedGroup.id);
    } else {
    throw new Error(data.message || 'فشل في إضافة المادة');
    }
    } catch (error) {
    console.error('Error adding subject:', error);
    this.showAlertMessage('error', error.message);
    } finally {
    this.addingSubject = false;
    }
    },

    // تحرير مادة في المجموعة
    editGroupSubject(subject) {
    this.editingSubject = { ...subject };
    this.showEditSubjectModal = true;
    },

    // تحديث مادة المجموعة
    async updateGroupSubject() {
    this.updatingSubject = true;

    try {
    const response = await
    fetch(`${window.routes.groupsUpdate}/${this.selectedGroup.id}/subjects/${this.editingSubject.id}`, {
    method: 'PUT',
    headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'Accept': 'application/json'
    },
    body: JSON.stringify({
    teacher_id: this.editingSubject.teacher_id || null,
    is_active: this.editingSubject.is_active
    })
    });

    const data = await response.json();

    if (data.success) {
    // تحديث البيانات المحلية
    const index = this.groupSubjects.findIndex(s => s.id === this.editingSubject.id);
    if (index !== -1) {
    this.groupSubjects[index] = { ...this.groupSubjects[index], ...data.group_subject };
    }

    this.showEditSubjectModal = false;
    this.showAlertMessage('success', data.message);
    } else {
    throw new Error(data.message || 'فشل في تحديث المادة');
    }
    } catch (error) {
    console.error('Error updating group subject:', error);
    this.showAlertMessage('error', error.message);
    } finally {
    this.updatingSubject = false;
    }
    },

    // تبديل حالة المادة (تفعيل/تعطيل)
    async toggleSubjectStatus(subject) {
    try {
    const newStatus = !subject.is_active;

    const response = await fetch(`${window.routes.groupsUpdate}/${this.selectedGroup.id}/subjects/${subject.id}`, {
    method: 'PUT',
    headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'Accept': 'application/json'
    },
    body: JSON.stringify({
    teacher_id: subject.teacher_id,
    is_active: newStatus
    })
    });

    const data = await response.json();

    if (data.success) {
    // تحديث البيانات المحلية
    const index = this.groupSubjects.findIndex(s => s.id === subject.id);
    if (index !== -1) {
    this.groupSubjects[index].is_active = newStatus;
    }

    this.showAlertMessage('success', `تم ${newStatus ? 'تفعيل' : 'تعطيل'} المادة`);
    } else {
    throw new Error(data.message || 'فشل في تحديث حالة المادة');
    }
    } catch (error) {
    console.error('Error toggling subject status:', error);
    this.showAlertMessage('error', error.message);
    }
    },

    // إزالة مادة من المجموعة
    async removeGroupSubject(subject) {
    if (!confirm(`هل أنت متأكد من إزالة مادة "${subject.subject_name}" من المجموعة؟`)) {
    return;
    }

    try {
    const response = await fetch(`${window.routes.groupsUpdate}/${this.selectedGroup.id}/subjects/${subject.id}`, {
    method: 'DELETE',
    headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'Accept': 'application/json'
    }
    });

    const data = await response.json();

    if (data.success) {
    // إزالة المادة من البيانات المحلية
    this.groupSubjects = this.groupSubjects.filter(s => s.id !== subject.id);

    // إضافة المادة للمواد المتاحة
    const subjectInfo = {
    id: subject.subject_id,
    name: subject.subject_name,
    description: 'مادة متاحة للإضافة'
    };
    this.availableSubjects.push(subjectInfo);

    this.showAlertMessage('success', data.message);
    } else {
    throw new Error(data.message || 'فشل في إزالة المادة');
    }
    } catch (error) {
    console.error('Error removing subject:', error);
    this.showAlertMessage('error', error.message);
    }
    },

    // تحميل مواد المجموعة المصدر للنسخ
    async loadSourceGroupSubjects() {
    if (!this.sourceGroupId) {
    this.sourceGroupSubjects = [];
    return;
    }

    try {
    const response = await fetch(`${window.routes.groupsUpdate}/${this.sourceGroupId}/subjects`, {
    headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
    });

    const data = await response.json();

    if (data.success) {
    this.sourceGroupSubjects = data.group_subjects;
    this.selectedSubjectIds = [];
    } else {
    throw new Error(data.message || 'فشل في تحميل مواد المجموعة المصدر');
    }
    } catch (error) {
    console.error('Error loading source group subjects:', error);
    this.showAlertMessage('error', 'حدث خطأ في تحميل مواد المجموعة المصدر');
    }
    },

    // نسخ المواد من مجموعة أخرى
    async confirmCopySubjects() {
    if (this.selectedSubjectIds.length === 0) {
    this.showAlertMessage('error', 'يرجى اختيار مادة واحدة على الأقل');
    return;
    }

    this.copyingSubjects = true;

    try {
    const response = await fetch(`${window.routes.groupsUpdate}/${this.sourceGroupId}/subjects/copy`, {
    method: 'POST',
    headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    'Accept': 'application/json'
    },
    body: JSON.stringify({
    target_group_id: this.selectedGroup.id,
    subject_ids: this.selectedSubjectIds,
    copy_teachers: this.copyTeachers
    })
    });

    const data = await response.json();

    if (data.success) {
    this.showCopySubjectsModal = false;
    this.resetCopySubjectsForm();
    this.showAlertMessage('success', data.message);

    // إعادة تحميل مواد المجموعة
    await this.loadGroupSubjects(this.selectedGroup.id);
    } else {
    throw new Error(data.message || 'فشل في نسخ المواد');
    }
    } catch (error) {
    console.error('Error copying subjects:', error);
    this.showAlertMessage('error', error.message);
    } finally {
    this.copyingSubjects = false;
    }
    },

    // إعادة تعيين نموذج إضافة المادة
    resetAddSubjectForm() {
    this.selectedSubjectId = '';
    this.selectedTeacherId = '';
    this.newSubjectActive = true;
    },

    // إعادة تعيين نموذج نسخ المواد
    resetCopySubjectsForm() {
    this.sourceGroupId = '';
    this.selectedSubjectIds = [];
    this.copyTeachers = false;
    this.sourceGroupSubjects = [];
    },

    // وظائف أخرى
    editStudent(student) {
    this.showAlertMessage('success', `سيتم فتح نموذج تعديل ${student.name}`);
    },

    viewSchedule(group) {
    this.showAlertMessage('success', `سيتم عرض جدول ${group.name}`);
    },

    exportStudentsList() {
    this.showAlertMessage('success', 'سيتم تصدير قائمة الطلاب');
    },

    // وظائف مساعدة
    resetNewGroup() {
    this.newGroup = {
    name: '',
    grade_level: '',
    section: '',
    max_capacity: 30,
    description: '',
    is_active: true
    };
    this.errors = {};
    },

    showAlertMessage(type, message) {
    this.alertType = type;
    this.alertMessage = message;
    this.showAlert = true;

    setTimeout(() => {
    this.showAlert = false;
    }, 5000);
    },

    // مراقبة تغيير المتغيرات
    init() {
    this.$watch('showAddStudentModal', (value) => {
    if (value) {
    this.loadAvailableStudents();
    }
    });

    // مراقبة جديدة لمواد المجموعة
    this.$watch('showAddSubjectModal', (value) => {
    if (value) {
    this.resetAddSubjectForm();
    }
    });

    this.$watch('showCopySubjectsModal', (value) => {
    if (value) {
    this.resetCopySubjectsForm();
    }
    });
    }
    }
    }
</script>
@endpush
