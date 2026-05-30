<?php
namespace App\Http\Controllers;

use App\Models\FinalExamScore;
use App\Models\MonthlyTestScore;
use App\Models\Student;
use App\Models\StudentEvaluation;

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

        $lectures = $student->group?->lectures()
            ->with('teacher.user')
            ->where('date', '>=', today())
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(30) ?? collect();

        return view('student.schedule', compact('student', 'lectures'));
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

    public function attendance()
    {
        $student = auth()->user()->student;

        $monthlyAttendance    = $student->getMonthlyAttendance();
        $attendancePercentage = $student->getAttendancePercentage();

        $allAttendance = $student->attendance()
            ->with('lecture')
            ->orderByDesc(function ($q) {
                $q->select('date')->from('lectures')->whereColumn('lectures.id', 'attendance.lecture_id');
            })
            ->get();

        return view('student.attendance', compact('student', 'monthlyAttendance', 'attendancePercentage', 'allAttendance'));
    }

    public function grades()
    {
        $student = auth()->user()->student;
        $group   = $student->group;
        $weights = $group?->weights ?? ['evaluations' => 40, 'monthly_tests' => 30, 'final_exam' => 30];

        $evals = StudentEvaluation::where('student_id', $student->id)
            ->where('group_id', $student->group_id)
            ->get()->keyBy('eval_number');

        $tests = MonthlyTestScore::where('student_id', $student->id)
            ->where('group_id', $student->group_id)
            ->get()->keyBy('test_number');

        $finalExam = FinalExamScore::where('student_id', $student->id)
            ->where('group_id', $student->group_id)
            ->first();

        $evalSum    = $evals->sum(fn ($e) =>
            $e->activity_participation + $e->behavior_discipline +
            $e->academic_improvement + $e->homework + $e->short_tests
        );
        $evalGrade  = $evals->count() > 0 ? round(($evalSum / (200 * $evals->count())) * $weights['evaluations'], 2) : 0;
        $testGrade  = $tests->count() > 0 ? round(($tests->sum('score') / (20 * $tests->count())) * $weights['monthly_tests'], 2) : 0;
        $finalGrade = (float) ($finalExam?->score ?? 0);
        $total      = round($evalGrade + $testGrade + $finalGrade, 2);

        return view('student.grades', compact(
            'student', 'group', 'weights', 'evals', 'tests', 'finalExam',
            'evalGrade', 'testGrade', 'finalGrade', 'total'
        ));
    }

    public function payments()
    {
        $student = auth()->user()->student;

        $payments = $student->payments()->latest()->paginate(20);

        $paymentStats = [
            'total_paid'    => $student->payments()->where('status', 'paid')->sum('amount'),
            'pending_count' => $student->payments()->where('status', 'pending')->count(),
            'overdue_count' => $student->payments()
                ->where('month', '<', now()->format('Y-m'))
                ->where('status', 'unpaid')->count(),
        ];

        return view('student.payments', compact('student', 'payments', 'paymentStats'));
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
