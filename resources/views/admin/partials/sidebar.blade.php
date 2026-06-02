<aside id="sidebar" class="bg-primary w-64 flex flex-col h-full overflow-hidden">

    <!-- Header -->
    <div class="flex items-center justify-between px-5 py-4 border-b border-blue-700 shrink-0">
        <div>
            <h2 class="text-base font-bold text-white leading-tight">البوابة الإدارية</h2>
            <p class="text-xs text-blue-200 mt-0.5">نظام إدارة الأكاديمية</p>
        </div>
        <button id="sidebarClose"
            class="lg:hidden flex items-center justify-center w-8 h-8 text-white rounded-lg hover:bg-blue-700 transition-colors"
            aria-label="إغلاق القائمة">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Nav links -->
    <nav class="flex-1 overflow-y-auto py-3 custom-scrollbar">
        @php
        $link = fn($route, $label, $icon, $active = null) =>
            '<a href="' . route($route) . '" class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm text-white rounded-lg transition-colors hover:bg-secondary ' . (request()->routeIs($active ?? $route) ? 'bg-secondary' : '') . '">' .
            '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="' . $icon . '"/></svg>' .
            $label . '</a>';
        @endphp

        <a href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm text-white rounded-lg transition-colors hover:bg-secondary {{ request()->routeIs('admin.dashboard') ? 'bg-secondary' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            لوحة التحكم
        </a>

        <a href="{{ route('admin.admissions.index') }}"
            class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm text-white rounded-lg transition-colors hover:bg-secondary {{ request()->routeIs('admin.admissions*') ? 'bg-secondary' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            طلبات الانتساب
            @php $pendingCount = \App\Models\Admission::pending()->count(); @endphp
            @if($pendingCount > 0)
            <span class="mr-auto px-1.5 py-0.5 text-xs bg-secondary text-white rounded-full">{{ $pendingCount }}</span>
            @endif
        </a>

        <a href="{{ route('admin.groups.index') }}"
            class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm text-white rounded-lg transition-colors hover:bg-secondary {{ request()->routeIs('admin.groups.index*') ? 'bg-secondary' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            المجموعات
        </a>

        <a href="{{ route('admin.lectures.index') }}"
            class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm text-white rounded-lg transition-colors hover:bg-secondary {{ request()->routeIs('admin.lectures*') ? 'bg-secondary' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            المحاضرات والجدولة
        </a>

        <a href="{{ route('admin.attendance') }}"
            class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm text-white rounded-lg transition-colors hover:bg-secondary {{ request()->routeIs('admin.attendance*') ? 'bg-secondary' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            الحضور والغياب
        </a>

        <a href="{{ route('admin.payments') }}"
            class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm text-white rounded-lg transition-colors hover:bg-secondary {{ request()->routeIs('admin.payments*') ? 'bg-secondary' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            القسم المالي
        </a>

        <a href="{{ route('admin.staff') }}"
            class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm text-white rounded-lg transition-colors hover:bg-secondary {{ request()->routeIs('admin.staff*') ? 'bg-secondary' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            إدارة العاملين
        </a>

        <a href="{{ route('admin.teacher-attendance') }}"
            class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm text-white rounded-lg transition-colors hover:bg-secondary {{ request()->routeIs('admin.teacher-attendance*') ? 'bg-secondary' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            حضور المدرسين
        </a>

        <a href="{{ route('admin.grades.index') }}"
            class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm text-white rounded-lg transition-colors hover:bg-secondary {{ request()->routeIs('admin.grades*') ? 'bg-secondary' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            الدرجات والتقييمات
        </a>

        <a href="{{ route('admin.archive') }}"
            class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm text-white rounded-lg transition-colors hover:bg-secondary {{ request()->routeIs('admin.archive*') ? 'bg-secondary' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            الأرشيف
        </a>

        <a href="{{ route('admin.reports') }}"
            class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm text-white rounded-lg transition-colors hover:bg-secondary {{ request()->routeIs('admin.reports*') ? 'bg-secondary' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            التقارير
        </a>

        <a href="{{ route('admin.messages') }}"
            class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm text-white rounded-lg transition-colors hover:bg-secondary {{ request()->routeIs('admin.messages*') ? 'bg-secondary' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            الطلبات والشكاوي
            @php $unread = \App\Models\ParentMessage::unread()->count(); @endphp
            @if($unread > 0)
            <span class="mr-auto px-1.5 py-0.5 text-xs bg-secondary text-white rounded-full">{{ $unread }}</span>
            @endif
        </a>

        <a href="{{ route('admin.settings') }}"
            class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 text-sm text-white rounded-lg transition-colors hover:bg-secondary {{ request()->routeIs('admin.settings*') ? 'bg-secondary' : '' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            الإعدادات
        </a>
    </nav>

    <!-- Footer: logout -->
    <div class="shrink-0 px-4 py-3 border-t border-blue-700">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="flex items-center gap-2 w-full px-3 py-2 text-sm text-blue-200 rounded-lg hover:bg-red-600 hover:text-white transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                تسجيل الخروج
            </button>
        </form>
    </div>

</aside>
