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
        $days  = $series->days->pluck('day_of_week')->toArray();
        $start = Carbon::parse($series->start_date);
        $end   = Carbon::parse($series->end_date);

        $current = $start->copy();

        while ($current->lte($end)) {
            $dayName = strtolower($current->format('l')); // sunday, monday, ...

            if (in_array($dayName, $days)) {
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