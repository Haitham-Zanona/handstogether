<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'لوحة التحكم' }} - الأكاديمية التعليمية</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preload" as="style"
        href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"
        onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" as="style"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </noscript>

    @stack('styles')

    <style>
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; padding: 0; }
        body {
            font-family: 'Tajawal', Arial, sans-serif;
            background: #f3f4f6;
            overflow-x: hidden;
        }

        /* Brand colours */
        .text-primary   { color: #2778E5; }
        .bg-primary     { background-color: #2778E5; }
        .hover\:bg-primary:hover { background-color: #2778E5; }
        .text-secondary { color: #EE8100; }
        .bg-secondary   { background-color: #EE8100; }
        .hover\:bg-secondary:hover { background-color: #EE8100; }
        .border-secondary { border-color: #EE8100; }
        .bg-primary-dark { background-color: #1e5ab8; }

        /* Scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }

        /* Notification pulse */
        .notification-dot { animation: ndPulse 2s infinite; }
        @keyframes ndPulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        /* ──── Layout shell ──── */
        .dashboard-shell {
            display: flex;
            height: 100vh;
            height: 100dvh; /* dynamic viewport height for mobile browsers */
        }

        /* ──── Sidebar ──── */
        #sidebar {
            position: fixed;
            top: 0; right: 0;
            width: 260px;
            height: 100%;
            z-index: 40;
            transition: transform 0.3s cubic-bezier(.4,0,.2,1);
            transform: translateX(100%);   /* hidden off-screen by default */
        }
        #sidebar.sidebar-open {
            transform: translateX(0);
        }
        @media (min-width: 1024px) {
            #sidebar {
                position: relative;
                transform: translateX(0);
                z-index: auto;
                flex-shrink: 0;
            }
        }

        /* ──── Overlay ──── */
        #sidebarOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 35;
        }
        #sidebarOverlay.overlay-open { display: block; }

        /* ──── Content area ──── */
        .dashboard-content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            height: 100dvh;
            overflow: hidden;
        }

        /* ──── Header ──── */
        .dashboard-header {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
            flex-shrink: 0;
            position: relative;
            z-index: 20;
        }

        /* ──── Hamburger button ──── */
        #hamburgerBtn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px; height: 40px;
            border-radius: 8px;
            background: #2778E5;
            color: #fff;
            border: none;
            cursor: pointer;
            flex-shrink: 0;
            touch-action: manipulation;
        }
        #hamburgerBtn:active { background: #1e5ab8; }
        @media (min-width: 1024px) {
            #hamburgerBtn { display: none; }
        }

        /* Hamburger lines animation */
        .ham-line {
            display: block;
            width: 18px; height: 2px;
            background: currentColor;
            border-radius: 2px;
            transition: transform .25s, opacity .25s;
        }
        .ham-lines { display: flex; flex-direction: column; gap: 4px; }
        .is-open .ham-line:nth-child(1) { transform: translateY(6px) rotate(45deg); }
        .is-open .ham-line:nth-child(2) { opacity: 0; }
        .is-open .ham-line:nth-child(3) { transform: translateY(-6px) rotate(-45deg); }

        /* ──── Main scrollable area ──── */
        .dashboard-main {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
        }

        /* ──── Responsive tables ──── */
        .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-wrap table { min-width: 520px; }
        @media (max-width: 639px) {
            .table-wrap table th,
            .table-wrap table td { font-size: .75rem; padding: .4rem .5rem; }
        }
    </style>
</head>

<body>

<!-- Overlay (outside all containers, truly body-level) -->
<div id="sidebarOverlay"></div>

<div class="dashboard-shell">

    <!-- ═══ SIDEBAR (rendered by each page's section) ═══ -->
    @yield('sidebar-menu')

    <!-- ═══ MAIN CONTENT ═══ -->
    <div class="dashboard-content">

        <!-- Top Header -->
        <header class="dashboard-header">
            <div class="flex items-center gap-3 px-3 py-2 sm:px-5 sm:py-3">

                <!-- Hamburger (mobile only) — in the header, never clipped -->
                <button id="hamburgerBtn" aria-label="فتح القائمة">
                    <span class="ham-lines">
                        <span class="ham-line"></span>
                        <span class="ham-line"></span>
                        <span class="ham-line"></span>
                    </span>
                </button>

                <!-- Page title -->
                <div class="flex-1 min-w-0">
                    <h1 class="text-base font-bold text-gray-900 truncate sm:text-xl md:text-2xl">
                        {{ $pageTitle ?? 'لوحة التحكم' }}
                    </h1>
                    @if(isset($pageDescription))
                    <p class="hidden text-xs text-gray-500 truncate sm:block sm:text-sm">{{ $pageDescription }}</p>
                    @endif
                </div>

                <!-- Right actions -->
                <div class="flex items-center gap-2 flex-shrink-0">

                    <!-- Notifications -->
                    <div class="relative">
                        <button id="notifications-btn"
                            class="relative flex items-center justify-center w-9 h-9 text-gray-500 rounded-full hover:bg-gray-100 transition-colors"
                            aria-label="الإشعارات">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                            @if($unreadCount > 0)
                            <span class="absolute top-0 left-0 flex items-center justify-center w-4 h-4 text-xs text-white rounded-full bg-secondary notification-dot">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                            @endif
                        </button>

                        <div id="notifications-dropdown"
                            class="absolute left-0 z-50 hidden mt-1 bg-white border border-gray-200 rounded-xl shadow-xl"
                            style="width:min(340px,90vw)">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                <span class="text-sm font-semibold text-gray-800">الإشعارات</span>
                                @if($unreadCount > 0)
                                <button id="mark-all-read" class="text-xs text-primary hover:underline">تمييز كمقروء</button>
                                @endif
                            </div>
                            <div class="overflow-y-auto max-h-64 custom-scrollbar">
                                @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                <div class="px-4 py-3 border-b border-gray-50 cursor-pointer hover:bg-gray-50 notification-item"
                                    data-id="{{ $notification->id }}"
                                    data-link="{{ $notification->data['link'] ?? '#' }}">
                                    <p class="text-xs text-gray-800 leading-5">{{ $notification->data['message'] }}</p>
                                    <p class="mt-1 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                                @empty
                                <p class="px-4 py-5 text-xs text-center text-gray-400">لا توجد إشعارات جديدة</p>
                                @endforelse
                            </div>
                            @if(auth()->user()->notifications->count() > 5)
                            <div class="px-4 py-2 text-center border-t border-gray-100">
                                <a href="{{ route('notifications.index') }}" class="text-xs text-primary hover:underline">عرض الكل</a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- User avatar + dropdown -->
                    <div class="relative">
                        <button id="user-menu-btn"
                            class="flex items-center gap-1.5 text-sm text-gray-600 hover:text-gray-900 focus:outline-none"
                            aria-label="قائمة المستخدم">
                            <div class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white rounded-full bg-primary flex-shrink-0">
                                {{ mb_substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <span class="hidden max-w-24 truncate sm:block">{{ auth()->user()->name }}</span>
                            <svg class="hidden w-3.5 h-3.5 text-gray-400 sm:block" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div id="user-menu-dropdown"
                            class="absolute left-0 z-50 hidden w-44 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        <!-- Page content -->
        <main class="dashboard-main custom-scrollbar">
            <div class="p-3 sm:p-5 md:p-6">

                @if(session('success'))
                <div class="flex items-start gap-2 px-4 py-3 mb-4 text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg" role="alert">
                    <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="flex items-start gap-2 px-4 py-3 mb-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg" role="alert">
                    <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
                @endif

                @if($errors->any())
                <div class="px-4 py-3 mb-4 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg" role="alert">
                    <p class="font-medium mb-1">يرجى تصحيح الأخطاء التالية:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div><!-- /.dashboard-content -->

</div><!-- /.dashboard-shell -->

<!-- Deferred non-critical scripts -->
<script defer src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script defer src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>

@stack('scripts')

<script>
(function () {
    var csrf   = document.querySelector('meta[name="csrf-token"]').content;
    var sidebar  = document.getElementById('sidebar');
    var overlay  = document.getElementById('sidebarOverlay');
    var hamburger = document.getElementById('hamburgerBtn');
    var isOpen   = false;

    function openSidebar() {
        isOpen = true;
        sidebar && sidebar.classList.add('sidebar-open');
        overlay && overlay.classList.add('overlay-open');
        hamburger && hamburger.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        isOpen = false;
        sidebar && sidebar.classList.remove('sidebar-open');
        overlay && overlay.classList.remove('overlay-open');
        hamburger && hamburger.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    hamburger && hamburger.addEventListener('click', function (e) {
        e.stopPropagation();
        isOpen ? closeSidebar() : openSidebar();
    });

    overlay && overlay.addEventListener('click', closeSidebar);

    /* Close button inside sidebar */
    var closeBtn = document.getElementById('sidebarClose');
    closeBtn && closeBtn.addEventListener('click', closeSidebar);

    /* Close on sidebar link click (mobile) */
    sidebar && sidebar.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () {
            if (window.innerWidth < 1024) closeSidebar();
        });
    });

    /* Close on resize to desktop */
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 1024) { closeSidebar(); document.body.style.overflow = ''; }
    });

    /* ─── Dropdowns ─── */
    ['notifications-btn,notifications-dropdown', 'user-menu-btn,user-menu-dropdown'].forEach(function (pair) {
        var parts = pair.split(',');
        var btn  = document.getElementById(parts[0]);
        var drop = document.getElementById(parts[1]);
        if (!btn || !drop) return;
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            drop.classList.toggle('hidden');
        });
    });

    document.addEventListener('click', function () {
        ['notifications-dropdown','user-menu-dropdown'].forEach(function (id) {
            var el = document.getElementById(id);
            el && el.classList.add('hidden');
        });
    });

    /* ─── Notifications ─── */
    document.querySelectorAll('.notification-item').forEach(function (item) {
        item.addEventListener('click', function () {
            var id   = this.dataset.id;
            var link = this.dataset.link;
            fetch('/notifications/' + id + '/mark-as-read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }
            }).then(function () { if (link && link !== '#') window.location.href = link; });
        });
    });

    var markAll = document.getElementById('mark-all-read');
    markAll && markAll.addEventListener('click', function () {
        fetch('/notifications/mark-all-as-read', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }
        }).then(function () { location.reload(); });
    });
})();
</script>
</body>
</html>
