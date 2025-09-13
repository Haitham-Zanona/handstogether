<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LectureSeeder extends Seeder
{
    public function run()
    {
        // $teachers = Teacher::with('user')->get();
        // $groups   = Group::all();

        // // Create lectures for the current month
        // $startDate = now()->startOfMonth();
        // $endDate   = now()->endOfMonth();

        // for ($date = $startDate; $date <= $endDate; $date->addDay()) {
        //     // Skip Fridays and Saturdays (weekend)
        //     if ($date->dayOfWeek === Carbon::FRIDAY || $date->dayOfWeek === Carbon::SATURDAY) {
        //         continue;
        //     }

        //     // Create 3-5 lectures per day
        //     $lecturesCount = rand(3, 5);

        //     for ($i = 0; $i < $lecturesCount; $i++) {
        //         $teacher = $teachers->random();
        //         $group   = $groups->random();

        //         $startTime = Carbon::create($date->year, $date->month, $date->day, rand(8, 14), [0, 30][rand(0, 1)]);
        //         $endTime   = $startTime->copy()->addHours(rand(1, 2));

        //         $subjects = [
        //             'محاضرة الرياضيات',
        //             'درس اللغة العربية',
        //             'حصة العلوم',
        //             'درس اللغة الإنجليزية',
        //             'محاضرة التاريخ',
        //             'درس الجغرافيا',
        //             'حصة الحاسوب',
        //         ];

        //         $lecture = Lecture::create([
        //             'title'       => $subjects[array_rand($subjects)],
        //             'date'        => $date->format('Y-m-d'),
        //             'start_time'  => $startTime->format('H:i:s'),
        //             'end_time'    => $endTime->format('H:i:s'),
        //             'teacher_id'  => $teacher->id,
        //             'group_id'    => $group->id,
        //             'description' => 'وصف المحاضرة وأهدافها التعليمية',
        //         ]);

        //         // Create attendance records for past lectures
        //         if ($date->isPast()) {
        //             $students = $group->students;

        //             foreach ($students as $student) {
        //                 // 85% chance of attendance
        //                 $statuses = ['present', 'present', 'present', 'present', 'late', 'absent'];

        //                 Attendance::create([
        //                     'student_id' => $student->id,
        //                     'lecture_id' => $lecture->id,
        //                     'status'     => $statuses[array_rand($statuses)],
        //                 ]);
        //             }
        //         }
        //     }
        // }
    }
}