<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AcademyNotification extends Notification
{
    use Queueable;

    public $message;
    public $link;
    public $type;

    public function __construct($message, $link = null, $type = 'info')
    {
        $this->message = $message;
        $this->link    = $link;
        $this->type    = $type; // info, success, warning, error
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message'              => $this->message,
            'link'                 => $this->link,
            'type'                 => $this->type,
            'created_at_formatted' => now()->format('Y-m-d H:i'),
        ];
    }

    // Helper methods for different notification types
    public static function success($message, $link = null)
    {
        return new self($message, $link, 'success');
    }

    public static function warning($message, $link = null)
    {
        return new self($message, $link, 'warning');
    }

    public static function error($message, $link = null)
    {
        return new self($message, $link, 'error');
    }

    // Specific notification types for academy
    public static function newAdmission($studentName, $parentName)
    {
        return new self(
            "طلب انتساب جديد: {$studentName} - ولي الأمر: {$parentName}",
            route('admin.admissions.index'),
            'info'
        );
    }

    public static function admissionApproved($studentName)
    {
        return new self(
            "تم قبول طلب انتساب {$studentName} بنجاح",
            route('parent.dashboard'),
            'success'
        );
    }

    public static function admissionRejected($studentName, $reason = null)
    {
        $message = "نأسف لإبلاغكم برفض طلب انتساب {$studentName}";
        if ($reason) {
            $message .= ". السبب: {$reason}";
        }
        return new self(
            $message,
            null,
            'error'
        );
    }

    public static function studentAbsent($studentName, $lectureTitle)
    {
        return new self(
            "غياب الطالب {$studentName} من محاضرة {$lectureTitle}",
            route('parent.attendance'),
            'warning'
        );
    }

    public static function paymentReceived($studentName, $month)
    {
        return new self(
            "تم استلام دفعة شهر {$month} للطالب {$studentName}",
            route('parent.payments'),
            'success'
        );
    }

    public static function paymentOverdue($studentName, $month)
    {
        return new self(
            "تأخير في دفع رسوم شهر {$month} للطالب {$studentName}",
            route('parent.payments'),
            'error'
        );
    }

    public static function newLecture($lectureTitle, $groupName, $date)
    {
        return new self(
            "محاضرة جديدة: {$lectureTitle} - مجموعة {$groupName} - {$date}",
            route('student.schedule'),
            'info'
        );
    }

    public static function lectureRescheduled($lectureTitle, $newDate)
    {
        return new self(
            "تم تغيير موعد محاضرة {$lectureTitle} إلى {$newDate}",
            route('student.schedule'),
            'warning'
        );
    }
}
