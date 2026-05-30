@extends('layouts.dashboard')

@section('sidebar-menu')
@include('student.partials.sidebar')
@endsection

@php
$pageTitle = 'السجل المالي';
$pageDescription = 'عرض دفعاتي ومستحقاتي المالية';
@endphp

@section('content')
<!-- ملخص مالي -->
<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
    <div class="p-6 bg-white rounded-lg shadow text-center">
        <p class="text-sm text-gray-500 mb-1">إجمالي المدفوع</p>
        <p class="text-2xl font-bold text-green-600">{{ number_format($paymentStats['total_paid'], 0) }} ر.س</p>
    </div>
    <div class="p-6 bg-white rounded-lg shadow text-center">
        <p class="text-sm text-gray-500 mb-1">دفعات معلقة</p>
        <p class="text-2xl font-bold text-orange-500">{{ $paymentStats['pending_count'] }}</p>
    </div>
    <div class="p-6 bg-white rounded-lg shadow text-center">
        <p class="text-sm text-gray-500 mb-1">دفعات متأخرة</p>
        <p class="text-2xl font-bold text-red-600">{{ $paymentStats['overdue_count'] }}</p>
    </div>
</div>

<!-- جدول الدفعات -->
<div class="bg-white rounded-lg shadow">
    <div class="p-5 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-900">سجل الدفعات</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">الشهر</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">المبلغ</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">نوع الدفعة</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">الحالة</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500">تاريخ الدفع</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($payments as $payment)
                @php
                $typeLabels = ['monthly'=>'شهري','educational_bundle'=>'باقة تعليمية','admission_fee'=>'رسوم انتساب'];
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $payment->month }}</td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ number_format($payment->amount, 0) }} ر.س</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $typeLabels[$payment->type ?? 'monthly'] ?? 'شهري' }}</td>
                    <td class="px-4 py-3">
                        @if($payment->status === 'paid')
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">مدفوعة</span>
                        @elseif($payment->status === 'pending')
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">معلقة</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">غير مدفوعة</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $payment->paid_date ? \Carbon\Carbon::parse($payment->paid_date)->format('d/m/Y') : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-gray-400">لا توجد دفعات مسجلة</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($payments->hasPages())
    <div class="p-4 border-t border-gray-200">
        {{ $payments->links() }}
    </div>
    @endif
</div>
@endsection
