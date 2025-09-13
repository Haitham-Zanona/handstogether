<li>
    <a href="{{ route('admin.dashboard') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('admin.dashboard') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        لوحة التحكم الرئيسية
    </a>
</li>

<li>
    <a href="{{ route('admin.admissions.index') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('admin.admissions*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>
        طلبات الانتساب
        @php $pendingCount = \App\Models\Admission::pending()->count(); @endphp
        @if($pendingCount > 0)
        <span class="px-2 py-1 mr-auto text-xs text-white rounded-full bg-secondary">{{ $pendingCount }}</span>
        @endif
    </a>
</li>

<li>
    <a href="{{ route('admin.groups.index') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('admin.groups.index*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        المجموعات
    </a>
</li>

<li>
    <a href="{{ route('admin.lectures.index') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('admin.lectures*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        المحاضرات والجدولة
    </a>
</li>

<li>
    <a href="{{ route('admin.attendance') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('admin.attendance*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
        </svg>
        الحضور والغياب
    </a>
</li>

<li>
    <a href="{{ route('admin.payments') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('admin.payments*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        الدفعات الشهرية
    </a>
</li>
<li>
    <a href="{{ route('admin.payments') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('admin.payments*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <!-- Person 1 -->
            <circle cx="30" cy="25" r="8" fill="#fff" stroke="#fff" stroke-width="1" />
            <rect x="22" y="33" width="16" height="20" rx="3" fill="#fff" stroke="#fff" stroke-width="1" />

            <!-- Person 2 (center, slightly forward) -->
            <circle cx="50" cy="30" r="10" fill="#fff" stroke="#fff" stroke-width="1" />
            <rect x="40" y="40" width="20" height="25" rx="4" fill="#fff" stroke="#fff" stroke-width="1" />

            <!-- Person 3 -->
            <circle cx="70" cy="25" r="8" fill="#fff" stroke="#fff" stroke-width="1" />
            <rect x="62" y="33" width="16" height="20" rx="3" fill="#fff" stroke="#fff" stroke-width="1" />

            <!-- Arms for center person -->
            <rect x="35" y="45" width="8" height="3" rx="1" fill="#fff" />
            <rect x="57" y="45" width="8" height="3" rx="1" fill="#fff" />

            <!-- Base/ground line -->
            <line x1="15" y1="75" x2="85" y2="75" stroke="#fff" stroke-width="2" />
        </svg>

        إدارة العاملين
    </a>
</li>

{{-- يمكن إضافة المزيد من الروابط --}}
<li>
    <a href="{{ route('admin.reports') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('admin.reports*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        التقارير
    </a>
</li>

<li>
    <a href="{{ route('admin.settings') }}"
        class="flex items-center px-6 py-3 text-white hover:bg-secondary rounded-r-lg mx-2 {{ request()->routeIs('admin.settings*') ? 'bg-secondary' : '' }}">
        <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" stroke-width="2">
            <path
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        الإعدادات
    </a>
</li>
