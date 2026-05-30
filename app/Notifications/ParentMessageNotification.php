<?php

namespace App\Notifications;

use App\Models\ParentMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ParentMessageNotification extends Notification
{
    use Queueable;

    public function __construct(public ParentMessage $message) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $parentName  = $this->message->parent?->name ?? 'ولي أمر';
        $studentName = $this->message->student?->user?->name ?? 'طالب';

        return [
            'type'       => 'warning',
            'message'    => "رسالة جديدة من ولي أمر {$studentName} ({$parentName})",
            'link'       => route('admin.messages'),
            'message_id' => $this->message->id,
        ];
    }
}
