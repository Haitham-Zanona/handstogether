<?php
namespace App\Services;

use App\Models\Lecture;
use App\Models\LectureSeries;
use Carbon\Carbon;

class SeriesGenerator
{
    /**
     * توليد محاضرات من سلسلة محاضرات
     */
    public function generateLectures(LectureSeries $series): void
    {
        $this->generateFromDate($series, Carbon::parse($series->start_date));
    }

    /**
     * إعادة توليد المحاضرات من تاريخ محدد حتى نهاية السلسلة
     */
    public function generateFromDate(LectureSeries $series, Carbon $fromDate): void
    {
        $days    = $series->days->pluck('day_of_week')->toArray();
        $end     = $series->end_date
            ? Carbon::parse($series->end_date)
            : $fromDate->copy()->addMonths(4);
        $current = $fromDate->copy();

        while ($current->lte($end)) {
            if (in_array((string) $current->dayOfWeek, $days)) {
                Lecture::create([
                    'title'      => $series->title,
                    'date'       => $current->toDateString(),
                    'start_time' => $series->start_time,
                    'end_time'   => $series->end_time,
                    'teacher_id' => $series->teacher_id,
                    'group_id'   => $series->group_id,
                    'subject_id' => $series->subject_id,
                    'series_id'  => $series->id,
                    'status'     => 'scheduled',
                    'type'       => 'lecture',
                ]);
            }
            $current->addDay();
        }
    }
}