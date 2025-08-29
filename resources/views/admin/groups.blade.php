@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$sidebarTitle = 'البوابة الإدارية';
$pageTitle = 'إعدادات المجموعات';
$pageDescription = 'إدارة المجموعات';
@endphp
@section('content')
<div class="container-fluid">
    <div class="mb-4 row">
        <div class="col-12">
            <h1 class="mb-0 text-gray-800 h3">إدارة المجموعات</h1>
        </div>
    </div>

    <div class="row">
        {{-- <div class="mb-4 col-md-4">
            <div class="shadow card">
                <div class="py-3 card-header">
                    <h6 class="m-0 font-weight-bold text-primary">إضافة مجموعة جديدة</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.groups.store') }}" method="POST">
                        @csrf
                        <div class="inline-block form-group">
                            <label for="name" class="block mb-2">اسم المجموعة:</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name') }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="inline-block form-group ">
                            <label for="description" class="block mb-2">وصف المجموعة</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-md hover:shadow-lg transition-all duration-300 ease-in-out">
                            إضافة المجموعة
                        </button>
                    </form>
                </div>
                <div class="max-w-lg p-6 mx-auto bg-white border border-gray-200 shadow-lg rounded-xl" dir="rtl">
                    <form action="{{ route('admin.groups.store') }}" method="POST" class="space-y-5">
                        @csrf

                        <!-- اسم المجموعة -->
                        <div class="form-group">
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-700">
                                اسم المجموعة:
                            </label>
                            <input type="text" id="name" name="name"
                                class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-4 py-2 text-gray-700 placeholder-gray-400 shadow-sm transition-all duration-200 @error('name') border-red-500 @enderror"
                                value="{{ old('name') }}" required>

                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- وصف المجموعة -->
                        <div class="form-group">
                            <label for="description" class="block mb-2 text-sm font-medium text-gray-700">
                                وصف المجموعة:
                            </label>
                            <textarea id="description" name="description" rows="3"
                                class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-4 py-2 text-gray-700 placeholder-gray-400 shadow-sm transition-all duration-200 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>

                            @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- زر الإرسال -->
                        <button type="submit"
                            class="w-full md:w-auto px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-md hover:shadow-lg transition-all duration-300 ease-in-out">
                            إضافة المجموعة
                        </button>
                    </form>
                </div>
            </div>
        </div> --}}

        <div x-data="{ open: false }" class="mb-4 col-md-4">
            <div class="shadow card">
                <div class="flex items-center justify-between p-3 card-header">
                    <h6 class="m-0 font-weight-bold text-primary">إضافة مجموعة جديدة</h6>

                    <!-- زر فتح/إغلاق -->
                    <button @click="open = !open"
                        class="px-4 py-2 text-sm font-medium text-white transition ease-in-out bg-blue-600 rounded-lg shadow-md duration-2000 hover:bg-blue-700 hover:shadow-lg">
                        <span x-show="!open">+ إضافة مجموعة</span>
                        <span x-show="open">إخفاء النموذج</span>
                    </button>
                </div>

                <!-- الفورم -->
                <div x-show="open" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 -translate-y-5"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-400"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-5"
                    class="max-w-lg p-6 mx-auto mt-3 bg-white border border-gray-200 shadow-lg rounded-xl" dir="rtl">

                    <form action="{{ route('admin.groups.store') }}" method="POST" class="space-y-5">
                        @csrf

                        <!-- اسم المجموعة -->
                        <div class="form-group">
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-700">
                                اسم المجموعة:
                            </label>
                            <input type="text" id="name" name="name"
                                class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-4 py-2 text-gray-700 placeholder-gray-400 shadow-sm transition-all duration-200 @error('name') border-red-500 @enderror"
                                value="{{ old('name') }}" required>

                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- وصف المجموعة -->
                        <div class="form-group">
                            <label for="description" class="block mb-2 text-sm font-medium text-gray-700">
                                وصف المجموعة:
                            </label>
                            <textarea id="description" name="description" rows="3"
                                class="w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-4 py-2 text-gray-700 placeholder-gray-400 shadow-sm transition-all duration-200 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>

                            @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- زر الإرسال -->
                        <button type="submit"
                            class="w-full md:w-auto px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-md hover:shadow-lg transition-all duration-300 ease-in-out">
                            إضافة المجموعة
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="shadow card">
                <div class="py-3 card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">قائمة المجموعات</h6>
                    <span class="badge badge-primary">{{ $groups->count() }} مجموعة</span>
                </div>
                <div class="card-body">
                    @if($groups->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>اسم المجموعة</th>
                                    <th>عدد الطلاب</th>
                                    <th>الوصف</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groups as $group)
                                <tr>
                                    <td>{{ $group->name }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ $group->total_students }}</span>
                                    </td>
                                    <td>{{ $group->description ?? 'لا يوجد وصف' }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-info" data-toggle="modal"
                                                data-target="#editGroupModal{{ $group->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('admin.groups.destroy', $group) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger"
                                                    onclick="return confirm('هل أنت متأكد من حذف هذه المجموعة؟')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editGroupModal{{ $group->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">تعديل المجموعة</h5>
                                                        <button type="button" class="close" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <form action="{{ route('admin.groups.update', $group) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="edit_name{{ $group->id }}">اسم
                                                                    المجموعة</label>
                                                                <input type="text" class="form-control"
                                                                    id="edit_name{{ $group->id }}" name="name"
                                                                    value="{{ $group->name }}" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="edit_description{{ $group->id }}">وصف
                                                                    المجموعة</label>
                                                                <textarea class="form-control"
                                                                    id="edit_description{{ $group->id }}"
                                                                    name="description"
                                                                    rows="3">{{ $group->description }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-dismiss="modal">إلغاء</button>
                                                            <button type="submit" class="btn btn-primary">حفظ
                                                                التعديلات</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="py-4 text-center">
                        <i class="mb-3 text-gray-300 fas fa-users fa-3x"></i>
                        <p class="text-muted">لا توجد مجموعات مضافة حتى الآن</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif

    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif

    // إظهار modals في حالة وجود أخطاء
    @if($errors->any())
        @if(old('_method') === 'PUT')
            $('.modal').modal('show');
        @endif
    @endif
</script>
@endsection