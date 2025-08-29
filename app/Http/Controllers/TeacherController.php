<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Lecture;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function dashboard()
    {
        $teacher = auth()->user()->teacher;

        if (! $teacher) {
            return redirect()->route('home')->with('error', 'حساب المدرس غير مكتمل');
        }

        $todayLectures  = $teacher->getTodayLectures();
        $weeklySchedule = $teacher->getWeeklySchedule();
        $totalStudents  = $teacher->students()->count();

        $stats = [
            'today_lectures'  => $todayLectures->count(),
            'week_lectures'   => $weeklySchedule->count(),
            'total_students'  => $totalStudents,
            'attendance_rate' => $this->getTeacherAttendanceRate($teacher->id),
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
            ->orderBy('start_time')
            ->get();

        return view('teacher.attendance', compact('todayLectures'));
    }

    public function markAttendance(Request $request, Lecture $lecture)
    {
        // التحقق من ملكية المحاضرة
        if ($lecture->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'غير مخول بتسجيل الحضور لهذه المحاضرة');
        }

        // التحقق من التوقيت المناسب للحضور
        if (! $lecture->date->isToday() && ! $lecture->date->isPast()) {
            return back()->with('error', 'لا يمكن تسجيل الحضور لمحاضرة لم تحدث بعد');
        }

        $attendanceData = $request->validate([
            'attendance'   => 'required|array',
            'attendance.*' => 'required|in:present,absent,late',
        ]);

        $absentStudents = [];
        $markedCount    = 0;

        foreach ($attendanceData['attendance'] as $studentId => $status) {
            $attendance = Attendance::updateOrCreate([
                'student_id' => $studentId,
                'lecture_id' => $lecture->id,
            ], [
                'status' => $status,
            ]);

            $markedCount++;

            // جمع الطلاب الغائبين
            if ($status === 'absent') {
                $student = Student::with(['user', 'parent'])->find($studentId);
                if ($student && $student->parent) {
                    $absentStudents[] = $student;
                }
            }
        }

        // إرسال إشعارات الغياب باستخدام NotificationService
        foreach ($absentStudents as $student) {
            NotificationService::notifyStudentAbsence($student, $lecture);
        }

        $absentCount = count($absentStudents);
        $message     = "تم تسجيل الحضور لـ {$markedCount} طالب";

        if ($absentCount > 0) {
            $message .= " وإرسال إشعارات غياب لـ {$absentCount} ولي أمر";
        }

        return back()->with('success', $message);
    }

    public function bulkMarkPresent(Lecture $lecture)
    {
        // التحقق من الملكية
        if ($lecture->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'غير مخول');
        }

        $students    = $lecture->group->students;
        $markedCount = 0;

        foreach ($students as $student) {
            Attendance::updateOrCreate([
                'student_id' => $student->id,
                'lecture_id' => $lecture->id,
            ], [
                'status' => 'present',
            ]);
            $markedCount++;
        }

        return back()->with('success', "تم تسجيل حضور جميع الطلاب ({$markedCount} طالب)");
    }

    public function reschedule(Request $request, Lecture $lecture)
    {
        // التحقق من الملكية
        if ($lecture->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'غير مخول');
        }

        $request->validate([
            'new_date'       => 'required|date|after_or_equal:today',
            'new_start_time' => 'required|date_format:H:i',
            'new_end_time'   => 'required|date_format:H:i|after:new_start_time',
            'reason'         => 'nullable|string|max:500',
        ]);

        $oldDate = $lecture->date->format('Y-m-d');
        $oldTime = $lecture->start_time->format('H:i');

        $lecture->update([
            'date'       => $request->new_date,
            'start_time' => $request->new_start_time,
            'end_time'   => $request->new_end_time,
        ]);

        // إشعار بتغيير الموعد
        NotificationService::notifyLectureRescheduled($lecture, $oldDate, $oldTime);

        return back()->with('success', 'تم تغيير موعد المحاضرة وإرسال إشعارات للطلاب وأولياء الأمور');
    }

    public function cancelLecture(Request $request, Lecture $lecture)
    {
        // التحقق من الملكية
        if ($lecture->teacher_id !== auth()->user()->teacher->id) {
            abort(403, 'غير مخول');
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        // إشعار بإلغاء المحاضرة
        NotificationService::notifyLectureCancelled($lecture, $request->cancellation_reason);

        $lecture->delete();

        return back()->with('success', 'تم إلغاء المحاضرة وإرسال إشعارات للطلاب وأولياء الأمور');
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
                $group = $lectures->first()->group;
                return [
                    'lectures_count'   => $lectures->count(),
                    'students_count'   => $group->students()->count(),
                    'total_attendance' => Attendance::whereIn('lecture_id', $lectures->pluck('id'))->count(),
                    'present_count'    => Attendance::present()->whereIn('lecture_id', $lectures->pluck('id'))->count(),
                ];
            });

        return view('teacher.reports', compact(
            'lecturesThisMonth', 'attendanceRate', 'groupsStats', 'currentMonth'
        ));
    }

    private function getTeacherAttendanceRate($teacherId, $month = null)
    {
        $month = $month ?: now()->format('Y-m');

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