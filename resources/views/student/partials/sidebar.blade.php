<li>
    <a href="{{ route('student.dashboard') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('student.dashboard') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        لوحة التحكم
    </a>
</li>

<li>
    <a href="{{ route('student.schedule') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('student.schedule*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        جدولي الدراسي
    </a>
</li>

<li>
    <a href="{{ route('student.lectures') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('student.lectures*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        محاضراتي
    </a>
</li>

<li>
    <a href="{{ route('student.reports') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('student.reports*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        تقاريري
    </a>
</li>
