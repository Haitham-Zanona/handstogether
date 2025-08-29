@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle = 'البوابة الإدارية';
$pageTitle = 'طلبات الانتساب';
$pageDescription = 'إدارة ومراجعة طلبات انتساب الطلاب الجدد';
@endphp

@section('content')
<!-- Statistics -->
<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-4">
    <div class="p-4 border border-yellow-200 rounded-lg bg-yellow-50">
        <div class="flex items-center">
            <div class="p-2 ml-3 bg-yellow-500 rounded-full">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-600">في الانتظار</p>
                <p class="text-2xl font-bold text-yellow-700">{{ $admissions->where('status', 'pending')->count() }}</p>
            </div>
        </div>
    </div>

    <div class="p-4 border border-green-200 rounded-lg bg-green-50">
        <div class="flex items-center">
            <div class="p-2 ml-3 bg-green-500 rounded-full">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-600">مقبول</p>
                <p class="text-2xl font-bold text-green-700">{{ $admissions->where('status', 'approved')->count() }}</p>
            </div>
        </div>
    </div>

    <div class="p-4 border border-red-200 rounded-lg bg-red-50">
        <div class="flex items-center">
            <div class="p-2 ml-3 bg-red-500 rounded-full">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-600">مرفوض</p>
                <p class="text-2xl font-bold text-red-700">{{ $admissions->where('status', 'rejected')->count() }}</p>
            </div>
        </div>
    </div>

    <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
        <div class="flex items-center">
            <div class="p-2 ml-3 bg-blue-500 rounded-full">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2">
                    <path
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-600">إجمالي الطلبات</p>
                <p class="text-2xl font-bold text-blue-700">{{ $admissions->count() }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Admissions Table -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">طلبات الانتساب</h3>
            <div class="flex items-center space-x-3 space-x-reverse">
                <!-- Filter -->
                <select class="px-3 py-2 text-sm border border-gray-300 rounded-md">
                    <option value="">جميع الحالات</option>
                    <option value="pending">في الانتظار</option>
                    <option value="approved">مقبول</option>
                    <option value="rejected">مرفوض</option>
                </select>
                <!-- Add Button -->
                <button class="px-4 py-2 text-sm font-medium text-white rounded-md bg-primary hover:bg-blue-700">
                    إضافة طلب جديد
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                        اسم الطالب
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                        ولي الأمر
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                        رقم الهاتف
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                        الحالة
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                        تاريخ التقديم
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">
                        الإجراءات
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($admissions as $admission)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $admission->student_name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $admission->parent_name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $admission->phone }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                        $statusClasses = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'approved' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800'
                        ];
                        @endphp
                        <span
                            class="inline-flex px-2 text-xs font-semibold rounded-full {{ $statusClasses[$admission->status] }}">
                            {{ $admission->status_in_arabic }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                        {{ $admission->created_at->format('Y-m-d') }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                        @if($admission->status === 'pending')
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <!-- Approve Button -->
                            <button onclick="openApproveModal({{ $admission->id }}, '{{ $admission->student_name }}')"
                                class="text-green-600 hover:text-green-900">
                                قبول
                            </button>
                            <!-- Reject Button -->
                            <form method="POST" action="{{ route('admin.admissions.reject', $admission) }}"
                                class="inline">
                                @csrf
                                <button type="button" class="text-red-600 hover:text-red-900"
                                    onclick="openRejectModal(this.closest('form'))">
                                    رفض
                                </button>
                            </form>
                        </div>
                        @else
                        <span class="text-gray-400">تم المعالجة</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        لا توجد طلبات انتساب
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $admissions->links() }}
    </div>
</div>

<!-- Approve Modal -->
<div id="approve-modal" class="fixed inset-0 z-50 hidden w-full h-full overflow-y-auto bg-gray-600 bg-opacity-50">
    <div class="relative p-5 mx-auto bg-white border rounded-md shadow-lg top-20 w-96">
        <div class="mt-3">
            <h3 class="mb-4 text-lg font-medium text-center text-gray-900">قبول طلب الانتساب</h3>
            <form id="approve-form" method="POST">
                @csrf
                {{-- @method('PATCH') --}}
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">اختر المجموعة</label>
                    <select name="group_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-primary focus:border-primary">
                        <option value="">اختر المجموعة</option>
                        @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->students_count ?? 0 }} طالب)
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center justify-between">
                    <button type="button" onclick="closeApproveModal()"
                        class="px-4 py-2 text-gray-700 bg-gray-300 rounded-md hover:bg-gray-400">
                        إلغاء
                    </button>
                    <button type="submit" class="px-4 py-2 text-white bg-green-500 rounded-md hover:bg-green-600">
                        قبول الطلب
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="fixed inset-0 z-50 hidden bg-gray-600 bg-opacity-50">
    <div class="relative p-5 mx-auto mt-20 bg-white rounded shadow w-96">
        <h3 class="mb-4 text-lg font-medium text-center">رفض الطلب</h3>
        <p class="mb-4 text-center">هل أنت متأكد من رفض هذا الطلب؟</p>
        <div class="flex justify-between">
            <button onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 rounded">إلغاء</button>
            <button onclick="submitRejectForm()" class="px-4 py-2 text-white bg-red-500 rounded">رفض</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openApproveModal(admissionId, studentName) {
        document.getElementById('approve-modal').classList.remove('hidden');
        document.getElementById('approve-form').action = `/admin/admissions/${admissionId}/approve`;
    }

    function closeApproveModal() {
        document.getElementById('approve-modal').classList.add('hidden');
    }

    function openRejectModal(form) {
    window.currentRejectForm = form;
    document.getElementById('reject-modal').classList.remove('hidden');
    }

    function closeRejectModal() {
    document.getElementById('reject-modal').classList.add('hidden');
    }

    function submitRejectForm() {
    if (window.currentRejectForm) {
    window.currentRejectForm.submit();
    }
    }
</script>
@endpush