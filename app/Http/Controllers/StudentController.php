<?php
namespace App\Http\Controllers;

use App\Models\Student;

class StudentController extends Controller
{
    public function dashboard()
    {
        $student = auth()->user()->student;

        $upcomingLectures          = $student->getUpcomingLectures();
        $attendancePercentage      = $student->getAttendancePercentage();
        $currentMonthPaymentStatus = $student->getCurrentMonthPaymentStatus();

        $stats = [
            'upcoming_lectures'     => $upcomingLectures->count(),
            'attendance_percentage' => $attendancePercentage,
            'payment_status'        => $currentMonthPaymentStatus,
        ];

        return view('student.dashboard', compact('student', 'upcomingLectures', 'stats'));
    }

    public function schedule()
    {
        $student  = auth()->user()->student;
        $lectures = $student->getUpcomingLectures();

        // Calendar format
        $calendarEvents = $student->group?->lectures()
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->get()
            ->map(fn($lecture) => $lecture->toCalendarEvent()) ?? collect();

        return view('student.schedule', compact('student', 'lectures', 'calendarEvents'));
    }

    public function lectures()
    {
        $student = auth()->user()->student;

        $lectures = $student->group?->lectures()
            ->with(['teacher.user', 'attendance' => function ($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->orderBy('date', 'desc')
            ->paginate(20) ?? collect();

        return view('student.lectures', compact('student', 'lectures'));
    }

    public function reports()
    {
        $student = auth()->user()->student;

        $monthlyAttendance    = $student->getMonthlyAttendance();
        $attendancePercentage = $student->getAttendancePercentage();

        $paymentHistory = $student->payments()
            ->latest()
            ->limit(6)
            ->get();

        return view('student.reports', compact(
            'student', 'monthlyAttendance', 'attendancePercentage', 'paymentHistory'
        ));
    }
}
