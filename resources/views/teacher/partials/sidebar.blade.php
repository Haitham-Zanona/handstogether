<li>
    <a href="{{ route('teacher.dashboard') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('teacher.dashboard') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        لوحة التحكم
    </a>
</li>

<li>
    <a href="{{ route('teacher.schedule') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('teacher.schedule*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        جدول المحاضرات
    </a>
</li>

<li>
    <a href="{{ route('teacher.students') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('teacher.students*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m3 5.197H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        الطلاب
    </a>
</li>

<li>
    <a href="{{ route('teacher.attendance') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('teacher.attendance*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
        </svg>
        الحضور والغياب
    </a>
</li>

<li>
    <a href="{{ route('teacher.reports') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('teacher.reports*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        تقاريري
    </a>
</li>
