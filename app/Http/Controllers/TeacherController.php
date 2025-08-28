<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Lecture;
use App\Models\Student;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function dashboard()
    {
        $teacher = auth()->user()->teacher;

        $todayLectures  = $teacher->getTodayLectures();
        $weeklySchedule = $teacher->getWeeklySchedule();
        $totalStudents  = $teacher->students()->count();

        $stats = [
            'today_lectures' => $todayLectures->count(),
            'week_lectures'  => $weeklySchedule->count(),
            'total_students' => $totalStudents,
        ];

        return view('teacher.dashboard', compact('todayLectures', 'weeklySchedule', 'stats'));
    }

    public function schedule()
    {
        $teacher = auth()->user()->teacher;

        $lectures = $teacher->lectures()
            ->with('group')
            ->where('date', '>=', today())
            ->orderBy('date')
            ->orderBy('start_time')
            ->paginate(20);

        // Calendar format for FullCalendar
        $calendarEvents = $teacher->lectures()
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->get()
            ->map(fn($lecture) => $lecture->toCalendarEvent());

        return view('teacher.schedule', compact('lectures', 'calendarEvents'));
    }

    public function students()
    {
        $teacher = auth()->user()->teacher;

        $students = $teacher->students()
            ->with(['user', 'parent', 'group'])
            ->paginate(20);

        return view('teacher.students', compact('students'));
    }

    public function attendance()
    {
        $teacher = auth()->user()->teacher;

        $todayLectures = $teacher->lectures()
            ->whereDate('date', today())
            ->with(['group.students.user', 'attendance'])
            ->get();

        return view('teacher.attendance', compact('todayLectures'));
    }

    public function markAttendance(Request $request, Lecture $lecture)
    {
        // Check if teacher owns this lecture
        if ($lecture->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'غير مخول بتسجيل الحضور لهذه المحاضرة');
        }

        $attendanceData = $request->validate([
            'attendance'   => 'required|array',
            'attendance.*' => 'required|in:present,absent,late',
        ]);

        foreach ($attendanceData['attendance'] as $studentId => $status) {
            Attendance::updateOrCreate([
                'student_id' => $studentId,
                'lecture_id' => $lecture->id,
            ], [
                'status' => $status,
            ]);
        }

        // Notify parents of absent students
        $absentStudents = Student::whereIn('id', array_keys($attendanceData['attendance']))
            ->where(function ($q) use ($attendanceData) {
                $absentIds = array_keys(array_filter($attendanceData['attendance'], fn($status) => $status === 'absent'));
                $q->whereIn('id', $absentIds);
            })
            ->with('parent')
            ->get();

        foreach ($absentStudents as $student) {
            if ($student->parent) {
                $student->parent->notify(new AcademyNotification(
                    'غياب الطالب ' . $student->user->name . ' من محاضرة ' . $lecture->title,
                    route('parent.attendance')
                ));
            }
        }

        return back()->with('success', 'تم تسجيل الحضور بنجاح');
    }

    public function reports()
    {
        $teacher      = auth()->user()->teacher;
        $currentMonth = now()->format('Y-m');

        $lecturesThisMonth = $teacher->lectures()
            ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$currentMonth])
            ->count();

        $attendanceRate = $this->getTeacherAttendanceRate($teacher->id, $currentMonth);

        $groupsStats = $teacher->lectures()
            ->with('group')
            ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$currentMonth])
            ->get()
            ->groupBy('group.name')
            ->map(function ($lectures) {
                return [
                    'lectures_count' => $lectures->count(),
                    'students_count' => $lectures->first()->group->students()->count(),
                ];
            });

        return view('teacher.reports', compact(
            'lecturesThisMonth', 'attendanceRate', 'groupsStats', 'currentMonth'
        ));
    }

    private function getTeacherAttendanceRate($teacherId, $month)
    {
        $totalExpected = Attendance::whereHas('lecture', function ($q) use ($teacherId, $month) {
            $q->where('teacher_id', $teacherId)
                ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$month]);
        })->count();

        if ($totalExpected === 0) {
            return 0;
        }

        $presentCount = Attendance::present()->whereHas('lecture', function ($q) use ($teacherId, $month) {
            $q->where('teacher_id', $teacherId)
                ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$month]);
        })->count();

        return round(($presentCount / $totalExpected) * 100, 2);
    }
}
