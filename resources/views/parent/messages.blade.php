@extends('layouts.dashboard')

@section('sidebar-menu')
@include('parent.partials.sidebar')
@endsection

@php
$pageTitle = 'التواصل والملاحظات';
$pageDescription = 'إرسال ملاحظة أو شكوى أو طلب إلى إدارة الأكاديمية';
@endphp

@section('content')
<div class="grid grid-cols-1 gap-8 lg:grid-cols-2">

    <!-- نموذج إرسال رسالة -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">إرسال رسالة للإدارة</h2>
            <p class="text-sm text-gray-500 mt-1">يمكنك إرسال ملاحظة أو شكوى أو طلب وسيتواصل معك الفريق قريباً</p>
        </div>
        <div class="p-5">
            <form action="{{ route('parent.messages.send') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">الطالب المعني</label>
                    <select name="student_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-primary focus:border-primary {{ $errors->has('student_id') ? 'border-red-500' : '' }}">
                        <option value="">-- اختر الطالب --</option>
                        @foreach($children as $child)
                        <option value="{{ $child->id }}" {{ old('student_id') == $child->id ? 'selected' : '' }}>
                            {{ $child->user->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('student_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-700">الرسالة</label>
                    <textarea name="message" rows="6" required minlength="5" maxlength="1000"
                        placeholder="اكتب ملاحظتك أو شكواك أو طلبك هنا..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-primary focus:border-primary resize-none {{ $errors->has('message') ? 'border-red-500' : '' }}">{{ old('message') }}</textarea>
                    @error('message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">الحد الأقصى 1000 حرف</p>
                </div>

                <button type="submit"
                    class="w-full px-4 py-2.5 text-sm font-medium text-white bg-primary rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition">
                    إرسال الرسالة
                </button>
            </form>
        </div>
    </div>

    <!-- الرسائل السابقة -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-5 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">رسائلي السابقة</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($messages as $msg)
            <div class="p-4">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <span class="text-sm font-medium text-gray-900">{{ $msg->student?->user?->name ?? '—' }}</span>
                        <span class="text-xs text-gray-400 mr-2">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                    @if($msg->is_read)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">تمت القراءة</span>
                    @else
                    <span class="px-2 py-0.5 text-xs rounded-full bg-orange-100 text-orange-700">في الانتظار</span>
                    @endif
                </div>
                <p class="text-sm text-gray-600 leading-relaxed">{{ Str::limit($msg->message, 150) }}</p>
            </div>
            @empty
            <div class="p-12 text-center text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
                <p>لم ترسل أي رسائل بعد</p>
            </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
