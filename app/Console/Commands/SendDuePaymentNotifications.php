<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Notifications\AcademyNotification;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendDuePaymentNotifications extends Command
{
    protected $signature   = 'payments:notify-due';
    protected $description = 'إرسال إشعارات للدفعات الشهرية غير المسددة لنهاية اليوم';

    public function handle(): int
    {
        $currentMonth = now()->format('Y-m');

        // ① Daily summary for standard unpaid monthly payments
        $duePayments = Payment::with(['student.user', 'student.parent'])
            ->where('month', $currentMonth)
            ->where('status', 'unpaid')
            ->where('type', 'monthly')
            ->get();

        $notified = 0;

        foreach ($duePayments as $payment) {
            $parent      = $payment->student?->parent;
            $studentName = $payment->student?->user?->name ?? 'الطالب';

            if ($parent) {
                $parent->notify(new AcademyNotification(
                    "تذكير: دفعة شهر {$payment->formatted_month} للطالب {$studentName} بمبلغ {$payment->amount} ش.ج لم تُسدَّد بعد",
                    route('parent.payments'),
                    'warning'
                ));
                $notified++;
            }
        }

        $totalAmount = $duePayments->sum('amount');
        NotificationService::notifyRole(
            'admin',
            "ملخص اليوم: {$duePayments->count()} دفعة غير مسددة لشهر {$currentMonth} بإجمالي {$totalAmount} ش.ج",
            route('admin.payments'),
            'info'
        );

        // ② Grace-period expired: payments where reminder was sent and deadline has passed
        $expiredGrace = Payment::with(['student.user', 'student.parent'])
            ->whereNotNull('last_reminder_sent_at')
            ->where('status', '!=', 'paid')
            ->whereRaw(DB::getDriverName() === 'pgsql'
                ? "(last_reminder_sent_at::date + (reminder_grace_days || ' days')::interval) <= CURRENT_DATE"
                : 'DATE_ADD(DATE(last_reminder_sent_at), INTERVAL reminder_grace_days DAY) <= CURDATE()'
            )
            ->get();

        $overdueCount = 0;

        foreach ($expiredGrace as $payment) {
            $parent      = $payment->student?->parent;
            $studentName = $payment->student?->user?->name ?? 'الطالب';

            NotificationService::notifyRole(
                'admin',
                "انتهت مهلة الدفع: {$studentName} لم يسدد دفعة {$payment->formatted_month} بمبلغ {$payment->amount} ش.ج",
                route('admin.payments'),
                'danger'
            );

            if ($parent) {
                $parent->notify(new AcademyNotification(
                    "انتهت مهلة السداد لدفعة {$payment->formatted_month} بمبلغ {$payment->amount} ش.ج، يرجى السداد فوراً",
                    route('parent.payments'),
                    'danger'
                ));
            }

            // Clear so the next daily run doesn't repeat this notification
            $payment->update(['last_reminder_sent_at' => null]);
            $overdueCount++;
        }

        $this->info("إشعارات يومية: {$notified} ولي أمر | مهل منتهية: {$overdueCount}");

        return Command::SUCCESS;
    }
}
