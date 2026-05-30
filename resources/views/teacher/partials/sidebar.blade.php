<aside id="sidebar" class="bg-primary w-64 flex flex-col h-full overflow-hidden">

    <!-- رأس القائمة -->
    <div class="flex items-center justify-between p-6 border-b border-blue-600">
        <div class="text-center">
            <h2 class="text-xl font-bold text-white">بوابة المدرسين</h2>
            <p class="text-sm text-white opacity-75">{{ auth()->user()->name }}</p>
        </div>
        <button id="sidebarClose" class="p-2 text-white hover:bg-blue-700 rounded-lg lg:hidden">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- قائمة التنقل -->
    <nav class="flex-1 py-6 overflow-y-auto">
        <ul class="space-y-2">
            <li>
                <a href="{{ route('teacher.dashboard') }}"
                    class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 transition-colors duration-200 {{ request()->routeIs('teacher.dashboard') ? 'bg-secondary' : '' }}">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    لوحة التحكم
                </a>
            </li>

            @php $sidebarGroups = auth()->user()->teacher?->assignedGroups()->get() ?? collect(); @endphp
            @foreach($sidebarGroups as $sg)
            <li>
                <a href="{{ route('teacher.groups.show', $sg) }}"
                    class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 transition-colors duration-200
                           {{ request()->route('group') && request()->route('group')->id == $sg->id ? 'bg-secondary' : '' }}">
                    <span class="w-5 h-5 ml-3 flex items-center justify-center">
                        <span class="w-2 h-2 rounded-full bg-orange-300"></span>
                    </span>
                    {{ $sg->name }}
                </a>
            </li>
            @endforeach

            <li>
                <a href="{{ route('teacher.lectures.index') }}"
                    class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 transition-colors duration-200 {{ request()->routeIs('teacher.lectures*') ? 'bg-secondary' : '' }}">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    محاضراتي
                </a>
            </li>

            <li>
                <a href="{{ route('teacher.attendance') }}"
                    class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 transition-colors duration-200 {{ request()->routeIs('teacher.attendance*') ? 'bg-secondary' : '' }}">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    الحضور والغياب
                </a>
            </li>

            <li>
                <a href="{{ route('teacher.grades.index') }}"
                    class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 transition-colors duration-200 {{ request()->routeIs('teacher.grades*') ? 'bg-secondary' : '' }}">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    الدرجات والتقييمات
                </a>
            </li>

            <li>
                <a href="{{ route('teacher.reports') }}"
                    class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 transition-colors duration-200 {{ request()->routeIs('teacher.reports*') ? 'bg-secondary' : '' }}">
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    تقاريري
                </a>
            </li>
        </ul>
    </nav>

    <!-- معلومات المستخدم -->
    <div class="p-4 border-t border-blue-600">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="flex items-center w-full px-4 py-2 text-sm text-white hover:bg-red-600 rounded-lg transition-colors duration-200">
                <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                تسجيل الخروج
            </button>
        </form>
    </div>

</aside>
