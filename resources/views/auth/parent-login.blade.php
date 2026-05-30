@extends('layouts.public')

@section('content')
<div class="flex items-center justify-center min-h-screen px-4 py-12 bg-gray-50">
    <div class="w-full max-w-md space-y-8">
        <div class="text-center">
            <div class="inline-block px-4 py-2 mb-4 text-2xl font-bold text-white rounded bg-primary">
                الأكاديمية التعليمية
            </div>
            <h2 class="text-3xl font-bold text-gray-900">بوابة أولياء الأمور</h2>
            <p class="mt-2 text-sm text-gray-600">يرجى إدخال بياناتك لمتابعة أداء أبنائك الطلبة</p>
        </div>

        <form class="mt-8 space-y-6" action="{{ route('login.parent') }}" method="POST">
            @csrf

            <div class="space-y-4">
                <div>
                    <label for="national_id" class="block mb-2 text-sm font-medium text-gray-700">
                        رقم هوية الأب
                    </label>
                    <input id="national_id" name="national_id" type="text" required
                        class="appearance-none block w-full px-3 py-2 border rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm
                               {{ $errors->has('national_id') ? 'border-red-500' : 'border-gray-300' }}"
                        placeholder="أدخل رقم هوية الأب"
                        value="{{ old('national_id') }}"
                        inputmode="numeric">
                    @error('national_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-700">
                        كلمة المرور
                    </label>
                    <input id="password" name="password" type="password" required
                        class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm"
                        placeholder="رقم جوال الأب">
                    <p class="mt-1.5 text-xs text-gray-400">كلمة المرور هي رقم جوال الأب المسجّل في طلب الانتساب</p>
                </div>
            </div>

            <button type="submit"
                class="relative flex justify-center w-full px-4 py-2.5 text-sm font-medium text-white bg-primary rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition">
                <span class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v-2L3.257 9.257A6 6 0 0111 3h4a6 6 0 012 2v2z"/>
                    </svg>
                </span>
                تسجيل الدخول
            </button>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 text-gray-500 bg-gray-50">أو اختر بوابة أخرى</span>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 mt-4">
                    <a href="{{ route('portal.admin') }}"
                        class="inline-flex justify-center px-3 py-2 text-xs font-medium text-gray-500 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                        الإداريين
                    </a>
                    <a href="{{ route('portal.teacher') }}"
                        class="inline-flex justify-center px-3 py-2 text-xs font-medium text-gray-500 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                        المدرسين
                    </a>
                    <a href="{{ route('portal.student') }}"
                        class="inline-flex justify-center px-3 py-2 text-xs font-medium text-gray-500 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                        الطلبة
                    </a>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-primary">
                    ← العودة إلى الصفحة الرئيسية
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
