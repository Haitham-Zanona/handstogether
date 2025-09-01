<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'الأكاديمية التعليمية' }}</title>

    <!-- Arabic Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;700&display=swap"
        rel="stylesheet">


    <!-- HTML2Canvas for image export -->
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>

    <!-- jsPDF for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Tajawal', Arial, sans-serif;
        }

        .hero-bg {
            background: linear-gradient(135deg, #2778E5 0%, #1e5cb8 100%);
        }

        .text-primary {
            color: #2778E5;
        }

        .bg-primary {
            background-color: #2778E5;
        }

        .text-secondary {
            color: #EE8100;
        }

        .bg-secondary {
            background-color: #EE8100;
        }

        .border-secondary {
            border-color: #EE8100;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    {{-- <nav class="fixed top-0 z-50 w-full bg-white shadow-md">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <div class="px-4 py-2 text-lg font-bold text-white rounded bg-primary">
                            الأكاديمية التعليمية
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:block">
                    <div class="flex items-center space-x-8 space-x-reverse">
                        <a href="{{ route('home') }}"
                            class="px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-primary">
                            الرئيسية
                        </a>
                        <a href="{{ route('about') }}"
                            class="px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-primary">
                            عن الأكاديمية
                        </a>
                        <a href="{{ route('contact') }}"
                            class="px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-primary">
                            تواصل معنا
                        </a>

                        <!-- Academy Portals Dropdown -->
                        <div class="relative group">
                            <button
                                class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-primary">
                                بوابات الأكاديمية
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div
                                class="absolute left-0 invisible w-48 mt-2 transition-all duration-300 bg-white rounded-md shadow-lg opacity-0 group-hover:opacity-100 group-hover:visible">
                                <div class="py-1">
                                    <a href="{{ route('portal.admin') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white">
                                        بوابة الإداريين
                                    </a>
                                    <a href="{{ route('portal.teacher') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white">
                                        بوابة المدرسين
                                    </a>
                                    <a href="{{ route('portal.parent') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white">
                                        بوابة أولياء الأمور
                                    </a>
                                    <a href="{{ route('portal.student') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white">
                                        بوابة الطلبة
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button"
                        class="p-2 text-gray-400 bg-gray-200 rounded-md mobile-menu-button hover:text-gray-500 hover:bg-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="hidden mobile-menu md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t sm:px-3">
                <a href="{{ route('home') }}"
                    class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">الرئيسية</a>
                <a href="{{ route('about') }}"
                    class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">عن
                    الأكاديمية</a>
                <a href="{{ route('contact') }}"
                    class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">تواصل
                    معنا</a>
                <div class="pt-2 border-t border-gray-200">
                    <p class="px-3 py-1 text-xs tracking-wider text-gray-500 uppercase">بوابات الأكاديمية</p>
                    <a href="{{ route('portal.admin') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">بوابة
                        الإداريين</a>
                    <a href="{{ route('portal.teacher') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">بوابة
                        المدرسين</a>
                    <a href="{{ route('portal.parent') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">بوابة
                        أولياء الأمور</a>
                    <a href="{{ route('portal.student') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">بوابة
                        الطلبة</a>
                </div>
            </div>
        </div>
    </nav> --}}

    <nav class="fixed top-0 z-50 w-full bg-white shadow-md">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <div class="px-4 py-2 text-lg font-bold text-white rounded bg-primary">
                            الأكاديمية التعليمية
                        </div>
                    </a>
                </div>

                <!-- Navigation Links (وسط الشاشة) -->
                <div class="hidden md:block">
                    <div class="flex items-center space-x-8 space-x-reverse">
                        <a href="{{ route('home') }}"
                            class="px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-primary">
                            الرئيسية
                        </a>
                        <a href="{{ route('about') }}"
                            class="px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-primary">
                            عن الأكاديمية
                        </a>
                        <a href="{{ route('contact') }}"
                            class="px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-primary">
                            تواصل معنا
                        </a>

                        <!-- Academy Portals Dropdown -->
                        <div class="relative group">
                            <button
                                class="flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:text-primary">
                                بوابات الأكاديمية
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div
                                class="absolute left-0 invisible w-48 mt-2 transition-all duration-300 bg-white rounded-md shadow-lg opacity-0 group-hover:opacity-100 group-hover:visible">
                                <div class="py-1">
                                    <a href="{{ route('portal.admin') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white">
                                        بوابة الإداريين
                                    </a>
                                    <a href="{{ route('portal.teacher') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white">
                                        بوابة المدرسين
                                    </a>
                                    <a href="{{ route('portal.parent') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white">
                                        بوابة أولياء الأمور
                                    </a>
                                    <a href="{{ route('portal.student') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white">
                                        بوابة الطلبة
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Actions (يسار الشاشة) -->
                <div class="flex items-center">
                    <!-- Mobile menu button (يظهر دائماً على الموبايل) -->
                    <div class="md:hidden">
                        <button type="button"
                            class="p-2 text-gray-400 bg-gray-200 rounded-md mobile-menu-button hover:text-gray-500 hover:bg-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>

                    <!-- User Menu for Authenticated Users Only -->
                    @auth
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <!-- Notifications -->
                        <div class="relative">
                            <button id="notifications-btn"
                                class="relative p-2 text-gray-600 transition-colors rounded-full hover:text-primary hover:bg-gray-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2">
                                    <path
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                <span
                                    class="absolute flex items-center justify-center w-5 h-5 text-xs text-white rounded-full -top-1 -left-1 bg-secondary notification-dot">
                                    {{ auth()->user()->unreadNotifications->count() }}
                                </span>
                                @endif
                            </button>

                            <!-- Notifications Dropdown -->
                            <div id="notifications-dropdown"
                                class="absolute left-0 z-50 hidden mt-2 bg-white border border-gray-200 rounded-lg shadow-lg w-80">
                                <div class="p-4 border-b border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold text-gray-900">الإشعارات</h3>
                                        @if(auth()->user()->unreadNotifications->count() > 0)
                                        <button id="mark-all-read" class="text-sm text-primary hover:text-blue-700">
                                            تمييز الكل كمقروء
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="overflow-y-auto max-h-96 custom-scrollbar">
                                    @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                    <div class="p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50 notification-item"
                                        data-id="{{ $notification->id }}"
                                        data-link="{{ $notification->data['link'] ?? '#' }}">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0 ml-3">
                                                @php
                                                $type = $notification->data['type'] ?? 'info';
                                                $iconColor = match($type) {
                                                'success' => 'text-green-500',
                                                'warning' => 'text-yellow-500',
                                                'error' => 'text-red-500',
                                                default => 'text-blue-500'
                                                };
                                                @endphp
                                                <div
                                                    class="flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full">
                                                    <svg class="w-4 h-4 {{ $iconColor }}" fill="none"
                                                        stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="10" />
                                                        <path d="M12 6v6l4 2" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm leading-5 text-gray-900">{{
                                                    $notification->data['message'] }}</p>
                                                <p class="mt-1 text-xs text-gray-500">{{
                                                    $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="p-4 text-center text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <path
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        <p>لا توجد إشعارات جديدة</p>
                                    </div>
                                    @endforelse
                                </div>
                                @if(auth()->user()->notifications->count() > 5)
                                <div class="p-3 text-center border-t border-gray-200">
                                    <a href="{{ route('notifications.index') }}"
                                        class="text-sm text-primary hover:text-blue-700">
                                        عرض جميع الإشعارات
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="relative">
                            <button id="user-menu-btn"
                                class="flex items-center text-sm text-gray-600 hover:text-gray-900 focus:outline-none">
                                <div
                                    class="flex items-center justify-center w-8 h-8 ml-2 text-white rounded-full bg-primary">
                                    <span class="text-sm font-semibold">{{ substr(auth()->user()->name, 0, 2) }}</span>
                                </div>
                                <span class="hidden md:block">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- User Dropdown -->
                            <div id="user-menu-dropdown"
                                class="absolute left-0 z-50 hidden w-48 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg">
                                <div class="py-1">
                                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">الملف
                                        الشخصي</a>
                                    <a href="#"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">الإعدادات</a>
                                    <div class="border-t border-gray-200"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="block w-full px-4 py-2 text-sm text-right text-red-700 hover:bg-red-50">
                                            تسجيل الخروج
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endauth

                    <!-- Guest Actions (للزوار غير المسجلين) -->
                    @guest
                    {{-- <div class="items-center hidden space-x-4 space-x-reverse md:flex">
                        <a href="{{ route('login') }}"
                            class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-primary">
                            تسجيل الدخول
                        </a>
                        <a href="{{ route('register') }}"
                            class="px-4 py-2 text-sm font-medium text-white rounded-md bg-primary hover:bg-blue-700">
                            التسجيل
                        </a>
                    </div> --}}
                    @endguest
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="hidden mobile-menu md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 bg-white border-t sm:px-3">
                <a href="{{ route('home') }}"
                    class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">الرئيسية</a>
                <a href="{{ route('about') }}"
                    class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">عن
                    الأكاديمية</a>
                <a href="{{ route('contact') }}"
                    class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">تواصل
                    معنا</a>

                <div class="pt-2 border-t border-gray-200">
                    <p class="px-3 py-1 text-xs tracking-wider text-gray-500 uppercase">بوابات الأكاديمية</p>
                    <a href="{{ route('portal.admin') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">بوابة
                        الإداريين</a>
                    <a href="{{ route('portal.teacher') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">بوابة
                        المدرسين</a>
                    <a href="{{ route('portal.parent') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">بوابة
                        أولياء الأمور</a>
                    <a href="{{ route('portal.student') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">بوابة
                        الطلبة</a>
                </div>

                <!-- Mobile User Menu -->
                @auth
                <div class="pt-2 mt-2 border-t border-gray-200">
                    <div class="flex items-center px-3 py-2">
                        <div class="flex items-center justify-center w-8 h-8 ml-2 text-white rounded-full bg-primary">
                            <span class="text-sm font-semibold">{{ substr(auth()->user()->name, 0, 2) }}</span>
                        </div>
                        <span class="text-base font-medium text-gray-700">{{ auth()->user()->name }}</span>
                    </div>
                    <a href="#"
                        class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">
                        الملف الشخصي
                    </a>
                    <a href="#"
                        class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">
                        الإعدادات
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="block w-full px-3 py-2 text-base font-medium text-right text-red-700 rounded-md hover:bg-red-50">
                            تسجيل الخروج
                        </button>
                    </form>
                </div>
                @endauth

                @guest
                <div class="pt-2 mt-2 border-t border-gray-200">
                    <a href="{{ route('login') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 rounded-md hover:text-primary">
                        تسجيل الدخول
                    </a>
                    <a href="{{ route('register') }}"
                        class="block px-3 py-2 text-base font-medium rounded-md text-primary hover:bg-gray-100">
                        التسجيل
                    </a>
                </div>
                @endguest
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-16">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="text-white bg-gray-800">
        <div class="px-4 py-12 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                <!-- Logo and Description -->
                <div class="col-span-1 md:col-span-2">
                    <div class="inline-block px-4 py-2 mb-4 text-xl font-bold text-white rounded bg-primary">
                        الأكاديمية التعليمية
                    </div>
                    <p class="text-sm leading-relaxed text-gray-300">
                        أكاديمية تعليمية رائدة تسعى لتقديم أفضل الخدمات التعليمية وبناء جيل متميز من الطلبة المبدعين.
                    </p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="mb-4 text-lg font-semibold">روابط سريعة</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}"
                                class="text-sm text-gray-300 hover:text-secondary">الرئيسية</a></li>
                        <li><a href="{{ route('about') }}" class="text-sm text-gray-300 hover:text-secondary">عن
                                الأكاديمية</a></li>
                        <li><a href="{{ route('contact') }}" class="text-sm text-gray-300 hover:text-secondary">تواصل
                                معنا</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="mb-4 text-lg font-semibold">تواصل معنا</h3>
                    <div class="space-y-2 text-sm text-gray-300">
                        <p class="flex items-center">
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M3 8l7.89 7.89a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            info@academy.edu
                        </p>
                        <p class="flex items-center">
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            +970-555-1234
                        </p>
                    </div>
                </div>
            </div>

            <div class="pt-8 mt-8 text-center border-t border-gray-700">
                <p class="text-sm text-gray-300">
                    © {{ date('Y') }} الأكاديمية التعليمية. جميع الحقوق محفوظة.
                </p>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu Toggle Script -->
    {{-- <script>
        document.querySelector('.mobile-menu-button').addEventListener('click', function() {
            document.querySelector('.mobile-menu').classList.toggle('hidden');
        });
    </script> --}}

    <script>
        // JavaScript لتشغيل الإشعارات وقوائم المستخدم
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle Mobile Menu
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.querySelector('.mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Toggle Notifications Dropdown
            const notificationsBtn = document.getElementById('notifications-btn');
            const notificationsDropdown = document.getElementById('notifications-dropdown');

            if (notificationsBtn && notificationsDropdown) {
                notificationsBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notificationsDropdown.classList.toggle('hidden');

                    // إغلاق قائمة المستخدم إذا كانت مفتوحة
                    const userDropdown = document.getElementById('user-menu-dropdown');
                    if (userDropdown && !userDropdown.classList.contains('hidden')) {
                        userDropdown.classList.add('hidden');
                    }
                });
            }

            // Toggle User Menu Dropdown
            const userMenuBtn = document.getElementById('user-menu-btn');
            const userMenuDropdown = document.getElementById('user-menu-dropdown');

            if (userMenuBtn && userMenuDropdown) {
                userMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    userMenuDropdown.classList.toggle('hidden');

                    // إغلاق قائمة الإشعارات إذا كانت مفتوحة
                    if (notificationsDropdown && !notificationsDropdown.classList.contains('hidden')) {
                        notificationsDropdown.classList.add('hidden');
                    }
                });
            }

            // Close dropdowns when clicking outside
            document.addEventListener('click', function() {
                if (notificationsDropdown && !notificationsDropdown.classList.contains('hidden')) {
                    notificationsDropdown.classList.add('hidden');
                }
                if (userMenuDropdown && !userMenuDropdown.classList.contains('hidden')) {
                    userMenuDropdown.classList.add('hidden');
                }
            });

            // Handle notification clicks
            const notificationItems = document.querySelectorAll('.notification-item');
            notificationItems.forEach(item => {
                item.addEventListener('click', function() {
                    const notificationId = this.dataset.id;
                    const link = this.dataset.link;

                    // Mark as read (AJAX call)
                    if (notificationId) {
                        markNotificationAsRead(notificationId);
                    }

                    // Navigate to link if exists
                    if (link && link !== '#') {
                        window.location.href = link;
                    }
                });
            });

            // Mark all notifications as read
            const markAllReadBtn = document.getElementById('mark-all-read');
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function() {
                    markAllNotificationsAsRead();
                });
            }
        });

        // Functions for notification handling
        function markNotificationAsRead(notificationId) {
            fetch(`/notifications/${notificationId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update notification count
                    const notificationDot = document.querySelector('.notification-dot');
                    if (notificationDot) {
                        let count = parseInt(notificationDot.textContent) - 1;
                        if (count <= 0) {
                            notificationDot.remove();
                        } else {
                            notificationDot.textContent = count;
                        }
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function markAllNotificationsAsRead() {
            fetch('/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide notification dot
                    const notificationDot = document.querySelector('.notification-dot');
                    if (notificationDot) {
                        notificationDot.remove();
                    }

                    // Refresh notifications dropdown
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>

</body>

</html>
