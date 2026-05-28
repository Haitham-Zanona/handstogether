@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle    = 'البوابة الإدارية';
$pageTitle       = 'الحضور والغياب';
$pageDescription = 'متابعة حضور الطلاب وتسجيلاتهم';
@endphp

@section('content')

<div x-data="attendancePage()" x-init="loadData()" class="space-y-6">

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 px-6 py-4">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">الشهر</label>
                <input type="month" x-model="filters.month" @change="loadData()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">المجموعة</label>
                <select x-model="filters.groupId" @change="loadData()"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-40">
                    <option value="">جميع المجموعات</option>
                    @foreach($groups as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button @click="loadData()"
                    class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition"
                    :class="loading ? 'opacity-60 cursor-wait' : ''">
                    <span x-show="!loading">تحديث</span>
                    <span x-show="loading">جار التحميل...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Error --}}
    <div x-show="error" class="px-4 py-3 text-red-700 bg-red-50 border border-red-200 rounded-lg text-sm" x-text="error"></div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900" x-text="summary.total_students ?? '—'"></p>
            <p class="text-xs text-gray-500 mt-1">إجمالي الطلاب</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-blue-600" x-text="summary.total_lectures ?? '—'"></p>
            <p class="text-xs text-gray-500 mt-1">المحاضرات</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-green-600" x-text="(summary.avg_rate ?? '—') + (summary.avg_rate != null ? '%' : '')"></p>
            <p class="text-xs text-gray-500 mt-1">متوسط الحضور</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-red-500" x-text="summary.low_attendance_count ?? '—'"></p>
            <p class="text-xs text-gray-500 mt-1">حضور منخفض (&lt;75%)</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-green-700" x-text="summary.present_total ?? '—'"></p>
            <p class="text-xs text-gray-500 mt-1">حضور كلي</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 text-center">
            <p class="text-2xl font-bold text-red-600" x-text="summary.absent_total ?? '—'"></p>
            <p class="text-xs text-gray-500 mt-1">غياب كلي</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
        <div class="flex border-b border-gray-200 px-4">
            <button @click="activeTab = 'all'"
                :class="activeTab === 'all' ? 'border-b-2 border-primary text-primary' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-3 text-sm font-medium transition">
                جميع الطلاب
                <span class="mr-1 px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600"
                      x-text="students.length"></span>
            </button>
            <button @click="activeTab = 'low'"
                :class="activeTab === 'low' ? 'border-b-2 border-red-500 text-red-600' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-3 text-sm font-medium transition">
                حضور منخفض
                <span class="mr-1 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-600"
                      x-text="lowAttendanceStudents.length"></span>
            </button>
        </div>

        {{-- Loading --}}
        <div x-show="loading" class="py-12 text-center text-gray-400">
            <svg class="w-8 h-8 mx-auto mb-2 animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <p class="text-sm">جار تحميل البيانات...</p>
        </div>

        {{-- Empty state --}}
        <div x-show="!loading && students.length === 0" class="py-12 text-center text-gray-400">
            <p class="text-sm">لا توجد بيانات للفترة المحددة</p>
        </div>

        {{-- Table --}}
        <div x-show="!loading && students.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الطالب</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المحاضرات</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">حاضر</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">متأخر</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">غائب</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">نسبة الحضور</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <template x-for="s in displayedStudents" :key="s.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-semibold shrink-0"
                                         x-text="s.name.charAt(0)"></div>
                                    <span class="font-medium text-gray-900 text-sm" x-text="s.name"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600" x-text="s.total_lectures"></td>
                            <td class="px-6 py-4 text-sm text-green-600 font-medium" x-text="s.present"></td>
                            <td class="px-6 py-4 text-sm text-yellow-600 font-medium" x-text="s.late"></td>
                            <td class="px-6 py-4 text-sm text-red-600 font-medium" x-text="s.absent"></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 h-2 bg-gray-200 rounded-full min-w-16">
                                        <div class="h-2 rounded-full transition-all"
                                             :class="s.rate >= 75 ? 'bg-green-500' : s.rate >= 50 ? 'bg-yellow-400' : 'bg-red-500'"
                                             :style="'width:' + s.rate + '%'">
                                        </div>
                                    </div>
                                    <span class="text-sm font-medium min-w-10"
                                          :class="s.rate >= 75 ? 'text-green-700' : s.rate >= 50 ? 'text-yellow-700' : 'text-red-700'"
                                          x-text="s.rate + '%'">
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button @click="openDetail(s)"
                                        class="text-xs px-3 py-1.5 bg-blue-50 text-primary rounded-lg hover:bg-blue-100 transition">
                                        تفاصيل
                                    </button>
                                    <button x-show="s.low_attendance"
                                        @click="notifyParent(s)"
                                        :disabled="notifying === s.id"
                                        class="text-xs px-3 py-1.5 bg-orange-50 text-orange-600 rounded-lg hover:bg-orange-100 transition disabled:opacity-60">
                                        <span x-show="notifying !== s.id">إشعار</span>
                                        <span x-show="notifying === s.id">جار...</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Student Detail Modal --}}
    <div x-show="modalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40" @click="modalOpen = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">سجل حضور الطالب</h2>
                    <p class="text-sm text-gray-500" x-text="modalStudent?.name + ' — ' + filters.month"></p>
                </div>
                <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-4">
                {{-- Modal loading --}}
                <div x-show="modalLoading" class="py-8 text-center text-gray-400">
                    <svg class="w-8 h-8 mx-auto animate-spin text-primary" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </div>

                {{-- Modal records --}}
                <div x-show="!modalLoading && modalRecords.length > 0">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr>
                                <th class="py-2 text-right text-xs text-gray-500 font-medium">التاريخ</th>
                                <th class="py-2 text-right text-xs text-gray-500 font-medium">المحاضرة</th>
                                <th class="py-2 text-right text-xs text-gray-500 font-medium">الوقت</th>
                                <th class="py-2 text-right text-xs text-gray-500 font-medium">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="r in modalRecords" :key="r.lecture_id">
                                <tr>
                                    <td class="py-2.5 text-gray-700" x-text="r.date"></td>
                                    <td class="py-2.5 text-gray-700" x-text="r.title"></td>
                                    <td class="py-2.5 text-gray-400" x-text="r.start_time"></td>
                                    <td class="py-2.5">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                              :class="{
                                                  'bg-green-100 text-green-700':  r.status === 'present',
                                                  'bg-yellow-100 text-yellow-700': r.status === 'late',
                                                  'bg-red-100 text-red-700':    r.status === 'absent',
                                                  'bg-gray-100 text-gray-500':  r.status === 'not_recorded',
                                              }">
                                            <span x-show="r.status === 'present'">حاضر</span>
                                            <span x-show="r.status === 'late'">متأخر</span>
                                            <span x-show="r.status === 'absent'">غائب</span>
                                            <span x-show="r.status === 'not_recorded'">غير مسجَّل</span>
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div x-show="!modalLoading && modalRecords.length === 0" class="py-8 text-center text-gray-400 text-sm">
                    لا توجد محاضرات مسجَّلة لهذا الطالب في الشهر المحدد
                </div>
            </div>
        </div>
    </div>

    {{-- Toast notification --}}
    <div x-show="toast" x-transition
         class="fixed bottom-6 left-6 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium text-white"
         :class="toastType === 'success' ? 'bg-green-600' : 'bg-red-600'"
         x-text="toast">
    </div>

</div>

@push('scripts')
<script>
function attendancePage() {
    return {
        filters: {
            month:   new Date().toISOString().slice(0, 7),
            groupId: '',
        },
        loading: false,
        error:   null,
        summary: {},
        students: [],
        activeTab: 'all',
        notifying: null,
        modalOpen:    false,
        modalLoading: false,
        modalStudent: null,
        modalRecords: [],
        toast:     null,
        toastType: 'success',

        get lowAttendanceStudents() {
            return this.students.filter(s => s.low_attendance);
        },

        get displayedStudents() {
            return this.activeTab === 'low' ? this.lowAttendanceStudents : this.students;
        },

        async loadData() {
            this.loading = true;
            this.error   = null;
            try {
                const params = new URLSearchParams({ month: this.filters.month });
                if (this.filters.groupId) params.set('group_id', this.filters.groupId);
                const res  = await fetch(`/admin/attendance/data?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                if (data.success) {
                    this.summary  = data.summary;
                    this.students = data.students;
                } else {
                    this.error = data.message || 'حدث خطأ في تحميل البيانات';
                }
            } catch (e) {
                this.error = 'تعذّر الاتصال بالخادم';
            } finally {
                this.loading = false;
            }
        },

        async openDetail(student) {
            this.modalStudent = student;
            this.modalOpen    = true;
            this.modalLoading = true;
            this.modalRecords = [];
            try {
                const params = new URLSearchParams({ month: this.filters.month });
                const res  = await fetch(`/admin/attendance/student/${student.id}?${params}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                if (data.success) {
                    this.modalRecords = data.records;
                }
            } catch (e) {
                // leave empty
            } finally {
                this.modalLoading = false;
            }
        },

        async notifyParent(student) {
            this.notifying = student.id;
            try {
                const res  = await fetch(`/admin/attendance/notify/${student.id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await res.json();
                this.showToast(data.message, data.success ? 'success' : 'error');
            } catch (e) {
                this.showToast('تعذّر إرسال الإشعار', 'error');
            } finally {
                this.notifying = null;
            }
        },

        showToast(message, type = 'success') {
            this.toast     = message;
            this.toastType = type;
            setTimeout(() => { this.toast = null; }, 3500);
        },
    };
}
</script>
@endpush

@endsection
