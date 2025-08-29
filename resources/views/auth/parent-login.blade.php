@extends('layouts.public')

@section('content')
<div class="flex items-center justify-center min-h-screen px-4 py-12 bg-gray-50 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
        <div class="text-center">
            <div class="inline-block px-4 py-2 mb-4 text-2xl font-bold text-white bg-green-600 rounded">
                الأكاديمية التعليمية
            </div>
            <h2 class="text-3xl font-bold text-gray-900">
                بوابة أولياء الأمور
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                يرجى إدخال بياناتك لمتابعة أداء أبنائك الطلبة
            </p>
        </div>

        <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
            @csrf
            <input type="hidden" name="role" value="parent">

            <div class="space-y-4">
                <!-- Email -->
                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-700">البريد الإلكتروني</label>
                    <input id="email" name="email" type="email" required
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror"
                        placeholder="أدخل بريدك الإلكتروني" value="{{ old('email') }}">
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-700">كلمة المرور</label>
                    <input id="password" name="password" type="password" required
                        class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror"
                        placeholder="أدخل كلمة المرور">
                    @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember" type="checkbox"
                        class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                    <label for="remember_me" class="block mr-2 text-sm text-gray-900">
                        تذكرني
                    </label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium text-green-600 hover:text-green-500">
                        نسيت كلمة المرور؟
                    </a>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit"
                    class="relative flex justify-center w-full px-4 py-2 text-sm font-medium text-white transition duration-300 bg-green-600 border border-transparent rounded-md group hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <span class="absolute inset-y-0 right-0 flex items-center pl-3">
                        <svg class="w-5 h-5 text-green-300 group-hover:text-green-400" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v-2L3.257 9.257A6 6 0 0111 3h4a6 6 0 012 2v2z" />
                        </svg>
                    </span>
                    تسجيل الدخول
                </button>
            </div>

            <!-- Alternative Portals -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 text-gray-500 bg-gray-50">أو اختر بوابة أخرى</span>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 mt-6">
                    <a href="{{ route('portal.admin') }}"
                        class="inline-flex justify-center w-full px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                        الإداريين
                    </a>
                    <a href="{{ route('portal.teacher') }}"
                        class="inline-flex justify-center w-full px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                        المدرسين
                    </a>
                    <a href="{{ route('portal.student') }}"
                        class="inline-flex justify-center w-full px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                        الطلبة
                    </a>
                </div>
            </div>

            <!-- Back to Home -->
            <div class="text-center">
                <a href="{{ route('home') }}" class="text-sm text-gray-600 hover:text-green-600">
                    ← العودة إلى الصفحة الرئيسية
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
