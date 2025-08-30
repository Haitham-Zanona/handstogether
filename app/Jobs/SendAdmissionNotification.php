<?php

namespace App\Jobs;

use App\Models\Admission;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAdmissionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Admission $admission,
        public string $status,
        public ?string $reason = null
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        if ($this->status === 'approved') {
            $student = $this->admission->student;
            if ($student) {
                $notificationService->notifyAdmissionApproved($student);
            }
        } elseif ($this->status === 'rejected') {
            $message = "نأسف لإبلاغكم برفض طلب انتساب {$this->admission->student_name}";
            if ($this->reason) {
                $message .= ". السبب: {$this->reason}";
            }

            if ($this->admission->father_phone) {
                $this->sendSMS($this->admission->father_phone, $message);
            }

            if ($this->admission->mother_phone) {
                $this->sendSMS($this->admission->mother_phone, $message);
            }
        }
    }

    /**
     * Send SMS notification.
     */
    private function sendSMS(string $phone, string $message): void
    {
        try {
            // In a real application, you would integrate with an SMS gateway provider here.
            // For example: Twilio, Nexmo, etc.
            // SMSGateway::send($phone, $message);

            Log::info('SMS Sent', [
                'to' => $phone,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send SMS', [
                'to' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
