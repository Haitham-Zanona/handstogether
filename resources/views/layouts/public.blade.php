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
    <script>
        document.querySelector('.mobile-menu-button').addEventListener('click', function() {
            document.querySelector('.mobile-menu').classList.toggle('hidden');
        });
    </script>
</body>

</html>