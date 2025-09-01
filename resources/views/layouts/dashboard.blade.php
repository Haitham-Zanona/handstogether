<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'لوحة التحكم' }} - الأكاديمية التعليمية</title>

    <!-- Arabic Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;700&display=swap"
        rel="stylesheet">

    <!-- تحميل CSS أولاً -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <!-- jsPDF for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Tajawal', Arial, sans-serif;
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

        .hover\:bg-secondary:hover {
            background-color: #EE8100;
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Notification styles */
        .notification-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="flex-shrink-0 w-64 overflow-y-auto text-white bg-primary custom-scrollbar">
            <!-- Logo -->
            <div class="p-6 border-b border-blue-600">
                <div class="text-2xl font-bold">{{ $sidebarTitle ?? 'لوحة التحكم' }}</div>
                <div class="mt-1 text-sm opacity-75">الأكاديمية التعليمية</div>
            </div>

            <!-- Navigation -->
            <nav class="mt-6">
                <ul class="space-y-1">
                    @yield('sidebar-menu')
                </ul>
            </nav>

            <!-- User Info -->
            <div class="absolute bottom-0 w-64 p-4 border-t border-blue-600">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 ml-3 bg-blue-600 rounded-full">
                        <span class="text-sm font-semibold">{{ substr(auth()->user()->name, 0, 2) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs truncate opacity-75">{{ auth()->user()->email }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex flex-col flex-1 min-w-0">
            <!-- Top Navigation -->
            <header class="bg-white border-b border-gray-200 shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Page Title -->
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $pageTitle ?? 'لوحة التحكم' }}</h1>
                        @if(isset($pageDescription))
                        <p class="mt-1 text-sm text-gray-600">{{ $pageDescription }}</p>
                        @endif
                    </div>

                    <!-- Right Side -->
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
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto custom-scrollbar">
                <div class="p-6">
                    <!-- Alerts -->
                    @if(session('success'))
                    <div class="px-4 py-3 mb-4 text-green-700 border border-green-200 rounded-lg bg-green-50"
                        role="alert">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ session('success') }}
                        </div>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="px-4 py-3 mb-4 text-red-700 border border-red-200 rounded-lg bg-red-50" role="alert">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ session('error') }}
                        </div>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="px-4 py-3 mb-4 text-red-700 border border-red-200 rounded-lg bg-red-50" role="alert">
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" stroke-width="2">
                                <path
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                            يرجى تصحيح الأخطاء التالية:
                        </div>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- FullCalendar JS -->
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/main.min.js'></script>


    <!-- ثم JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- ثم اللغة العربية إذا لزم الأمر -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>

    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>



    <!-- ثم JavaScript -->

    <!-- ثم اللغة العربية إذا لزم الأمر -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>

    <!-- Custom Scripts -->
    <script>
        // Notifications functionality
        document.getElementById('notifications-btn').addEventListener('click', function() {
            const dropdown = document.getElementById('notifications-dropdown');
            dropdown.classList.toggle('hidden');
        });

        // User menu functionality
        document.getElementById('user-menu-btn').addEventListener('click', function() {
            const dropdown = document.getElementById('user-menu-dropdown');
            dropdown.classList.toggle('hidden');
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const notificationsBtn = document.getElementById('notifications-btn');
            const notificationsDropdown = document.getElementById('notifications-dropdown');
            const userMenuBtn = document.getElementById('user-menu-btn');
            const userMenuDropdown = document.getElementById('user-menu-dropdown');

            if (!notificationsBtn.contains(event.target) && !notificationsDropdown.contains(event.target)) {
                notificationsDropdown.classList.add('hidden');
            }

            if (!userMenuBtn.contains(event.target) && !userMenuDropdown.contains(event.target)) {
                userMenuDropdown.classList.add('hidden');
            }
        });

        // Mark notification as read
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                const link = this.dataset.link;

                // Mark as read via AJAX
                fetch(`/notifications/${notificationId}/mark-as-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(() => {
                    if (link && link !== '#') {
                        window.location.href = link;
                    }
                });
            });
        });

        // Mark all as read
        const markAllReadBtn = document.getElementById('mark-all-read');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', function() {
                fetch('/notifications/mark-all-as-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(() => {
                    location.reload();
                });
            });
        }
    </script>

    @stack('scripts')
</body>

</html>
