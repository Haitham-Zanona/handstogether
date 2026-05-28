<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\StudentEvaluation;
use App\Notifications\AcademyNotification;
use Illuminate\Console\Command;

class SendEvaluationReminders extends Command
{
    protected $signature   = 'grades:remind-evaluations';
    protected $description = 'إرسال تذكير للمدرسين بإدخال التقييمات الدورية عند بدء كل فترتين أسبوعيتين';

    public function handle(): int
    {
        $activeGroups = Group::where('is_active', true)
            ->whereNotNull('start_date')
            ->with(['teachers.user', 'students'])
            ->get();

        if ($activeGroups->isEmpty()) {
            $this->info('لا توجد مجموعات نشطة بتاريخ بداية محدد');
            return Command::SUCCESS;
        }

        $notifiedTeachers = [];

        foreach ($activeGroups as $group) {
            $daysSinceStart = (int) now()->diffInDays($group->start_date, false);

            if ($daysSinceStart < 0) continue;

            // Only remind on eval-period start days (0, 14, 28, 42 ...)
            if ($daysSinceStart % 14 !== 0) continue;

            $currentPeriod = min(8, max(1, (int) ceil(($daysSinceStart + 1) / 14)));
            $studentIds    = $group->students->pluck('id');

            if ($studentIds->isEmpty()) continue;

            $filled = StudentEvaluation::where('group_id', $group->id)
                ->where('eval_number', $currentPeriod)
                ->whereIn('student_id', $studentIds)
                ->count();

            if ($filled >= $studentIds->count()) continue;

            foreach ($group->teachers as $teacher) {
                if (! $teacher->user || in_array($teacher->id, $notifiedTeachers)) continue;

                $teacher->user->notify(new AcademyNotification(
                    "حان موعد التقييم الدوري رقم {$currentPeriod} للمجموعة \"{$group->name}\" — يرجى إدخال تقييمات طلابك",
                    route('teacher.grades.index'),
                    'info'
                ));
                $notifiedTeachers[] = $teacher->id;
            }
        }

        $count = count($notifiedTeachers);
        $this->info("تم إرسال تذكير التقييم لـ {$count} مدرس");
        return Command::SUCCESS;
    }
}
