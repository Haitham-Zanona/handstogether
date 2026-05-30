@extends('layouts.dashboard')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@php
$pageTitle = 'الطلبات والشكاوي';
$pageDescription = 'رسائل وملاحظات أولياء الأمور';
@endphp

@section('content')
<!-- ملخص -->
<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2">
    <div class="p-6 bg-white rounded-lg shadow text-center">
        <p class="text-sm text-gray-500 mb-1">إجمالي الرسائل</p>
        <p class="text-3xl font-bold text-gray-800">{{ $messages->total() }}</p>
    </div>
    <div class="p-6 bg-white rounded-lg shadow text-center">
        <p class="text-sm text-gray-500 mb-1">رسائل غير مقروءة</p>
        <p class="text-3xl font-bold text-orange-500">{{ $unreadCount }}</p>
    </div>
</div>

<!-- جدول الرسائل -->
<div class="bg-white rounded-lg shadow">
    <div class="flex items-center justify-between p-5 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-900">رسائل أولياء الأمور</h2>
        @if($unreadCount > 0)
        <button id="mark-all-messages-read"
            class="px-3 py-1.5 text-xs text-white bg-primary rounded-md hover:bg-blue-700 transition">
            تمييز الكل كمقروء
        </button>
        @endif
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($messages as $message)
        <div class="p-5 message-item {{ !$message->is_read ? 'bg-blue-50' : '' }}" data-id="{{ $message->id }}">
            <div class="flex items-start justify-between">
                <div class="flex items-start flex-1">
                    <div class="flex items-center justify-center w-10 h-10 ml-4 text-white rounded-full bg-primary flex-shrink-0 font-bold text-sm">
                        {{ mb_substr($message->parent?->name ?? '؟', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-1 flex-wrap">
                            <span class="text-sm font-semibold text-gray-900">{{ $message->parent?->name ?? '—' }}</span>
                            @if($message->parent?->phone)
                            <span class="text-xs text-gray-500 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ $message->parent->phone }}
                            </span>
                            @endif
                            <span class="text-xs text-gray-400">•</span>
                            <span class="text-xs text-gray-500">
                                الطالب: <span class="font-medium text-gray-700">{{ $message->student?->user?->name ?? '—' }}</span>
                                @if($message->student?->group)
                                <span class="text-gray-400">({{ $message->student->group->name }})</span>
                                @endif
                            </span>
                        </div>
                        <p class="text-sm text-gray-700 leading-relaxed mt-2">{{ $message->message }}</p>
                        <div class="flex items-center gap-4 mt-3">
                            <span class="text-xs text-gray-400">{{ $message->created_at->diffForHumans() }}</span>
                            <span class="text-xs text-gray-400">{{ $message->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 mr-4 flex-shrink-0">
                    @if(!$message->is_read)
                    <span class="w-2.5 h-2.5 rounded-full bg-orange-500 flex-shrink-0" title="غير مقروء"></span>
                    <button class="mark-read-btn px-3 py-1 text-xs text-primary border border-primary rounded hover:bg-primary hover:text-white transition"
                        data-id="{{ $message->id }}">
                        تمييز كمقروء
                    </button>
                    @else
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">مقروء</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="p-16 text-center text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <p class="text-lg">لا توجد رسائل بعد</p>
        </div>
        @endforelse
    </div>

    @if($messages->hasPages())
    <div class="p-4 border-t border-gray-200">
        {{ $messages->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.mark-read-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch(`/admin/messages/${id}/mark-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        }).then(r => r.json()).then(data => {
            if (data.success) {
                const row = this.closest('.message-item');
                row.classList.remove('bg-blue-50');
                this.closest('.flex.items-center').innerHTML =
                    '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">مقروء</span>';
                const dot = row.querySelector('.bg-orange-500');
                if (dot) dot.remove();
            }
        });
    });
});

const markAllBtn = document.getElementById('mark-all-messages-read');
if (markAllBtn) {
    markAllBtn.addEventListener('click', function() {
        document.querySelectorAll('.mark-read-btn').forEach(btn => btn.click());
        this.remove();
    });
}
</script>
@endpush
