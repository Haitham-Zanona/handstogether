<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Group;
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

        $groups         = $teacher->assignedGroups()->withCount('students')->get();
        $todayLectures  = $teacher->getTodayLectures();
        $weeklySchedule = $teacher->getWeeklySchedule();
        $totalStudents  = $groups->sum('students_count');

        $stats = [
            'today_lectures'  => $todayLectures->count(),
            'week_lectures'   => $weeklySchedule->count(),
            'total_students'  => $totalStudents,
            'total_groups'    => $groups->count(),
        ];

        return view('teacher.dashboard', compact('teacher', 'groups', 'todayLectures', 'weeklySchedule', 'stats'));
    }

    public function showGroup(Group $group)
    {
        $teacher = auth()->user()->teacher;

        if (! $teacher->assignedGroups()->where('groups.id', $group->id)->exists()) {
            abort(403, 'ليس لديك صلاحية لعرض هذه المجموعة');
        }

        $students = $group->students()->with('user')->orderBy('created_at')->get();

        $upcomingLectures = $teacher->lectures()
            ->where('group_id', $group->id)
            ->where('date', '>=', today())
            ->orderBy('date')->orderBy('start_time')
            ->limit(5)
            ->get();

        return view('teacher.groups.show', compact('group', 'students', 'upcomingLectures', 'teacher'));
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

    public function attendance(Request $request)
    {
        $teacher = auth()->user()->teacher;

        $date = $request->get('date', today()->format('Y-m-d'));

        // لا نسمح بتاريخ مستقبلي
        if ($date > today()->format('Y-m-d')) {
            $date = today()->format('Y-m-d');
        }

        $selectedDate = \Carbon\Carbon::parse($date);

        $lectures = $teacher->lectures()
            ->whereDate('date', $selectedDate)
            ->with(['group.students.user', 'attendance'])
            ->orderBy('start_time')
            ->get();

        return view('teacher.attendance', compact('lectures', 'date'));
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
        return view('teacher.reports');
    }

    public function getReportsData(Request $request)
    {
        $teacher = auth()->user()->teacher;
        $month   = $request->get('month', now()->format('Y-m'));

        // ── إجمالي المحاضرات ──
        $lectures = $teacher->lectures()
            ->with('group')
            ->whereYear('date', substr($month, 0, 4))->whereMonth('date', (int) substr($month, 5, 2))
            ->get();

        $lectureIds = $lectures->pluck('id');

        $totalLectures    = $lectures->count();
        $completedCount   = $lectures->where('status', 'completed')->count();
        $cancelledCount   = $lectures->where('status', 'cancelled')->count();
        $scheduledCount   = $lectures->where('status', 'scheduled')->count();

        // ── نسبة الحضور العامة ──
        $totalExpected = Attendance::whereIn('lecture_id', $lectureIds)->count();
        $presentCount  = Attendance::whereIn('lecture_id', $lectureIds)->where('status', 'present')->count();
        $overallRate   = $totalExpected > 0 ? round(($presentCount / $totalExpected) * 100, 1) : 0;

        // ── إحصائيات كل مجموعة ──
        $groups = $teacher->assignedGroups()->withCount('students')->get();

        $groupsStats = $groups->map(function ($group) use ($lectures, $month) {
            $groupLectures = $lectures->where('group_id', $group->id);
            $groupLectureIds = $groupLectures->pluck('id');

            $total   = Attendance::whereIn('lecture_id', $groupLectureIds)->count();
            $present = Attendance::whereIn('lecture_id', $groupLectureIds)->where('status', 'present')->count();
            $absent  = Attendance::whereIn('lecture_id', $groupLectureIds)->where('status', 'absent')->count();
            $late    = Attendance::whereIn('lecture_id', $groupLectureIds)->where('status', 'late')->count();

            return [
                'id'              => $group->id,
                'name'            => $group->name,
                'grade_level'     => $group->grade_level,
                'students_count'  => $group->students_count,
                'lectures_count'  => $groupLectures->count(),
                'present'         => $present,
                'absent'          => $absent,
                'late'            => $late,
                'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 1) : null,
            ];
        });

        // ── الطلاب الأكثر غياباً ──
        $lowAttendance = [];
        foreach ($groups as $group) {
            $groupLectureIds = $lectures->where('group_id', $group->id)->pluck('id');
            if ($groupLectureIds->isEmpty()) continue;

            $students = $group->students()->with('user')->get();
            foreach ($students as $student) {
                $studentTotal   = Attendance::whereIn('lecture_id', $groupLectureIds)->where('student_id', $student->id)->count();
                $studentPresent = Attendance::whereIn('lecture_id', $groupLectureIds)->where('student_id', $student->id)->where('status', 'present')->count();
                $studentAbsent  = Attendance::whereIn('lecture_id', $groupLectureIds)->where('student_id', $student->id)->where('status', 'absent')->count();

                if ($studentTotal === 0) continue;
                $rate = round(($studentPresent / $studentTotal) * 100, 1);
                if ($rate < 75) {
                    $lowAttendance[] = [
                        'name'       => $student->user?->name ?? '—',
                        'group_name' => $group->name,
                        'rate'       => $rate,
                        'absences'   => $studentAbsent,
                        'total'      => $studentTotal,
                    ];
                }
            }
        }
        usort($lowAttendance, fn($a, $b) => $a['rate'] <=> $b['rate']);

        return response()->json([
            'success' => true,
            'month'   => $month,
            'overview' => [
                'lectures_total'  => $totalLectures,
                'completed'       => $completedCount,
                'cancelled'       => $cancelledCount,
                'scheduled'       => $scheduledCount,
                'attendance_rate' => $overallRate,
                'groups_count'    => $groups->count(),
                'students_count'  => $groups->sum('students_count'),
            ],
            'groups_stats'    => $groupsStats->values(),
            'low_attendance'  => array_slice($lowAttendance, 0, 10),
        ]);
    }

    // ─── Teacher Lectures Management ─────────────────────────────────────────

    public function lecturesIndex()
    {
        return view('teacher.lectures.index');
    }

    public function getTeacherLecturesData(Request $request)
    {
        $teacher  = auth()->user()->teacher;
        $query    = $teacher->lectures()->with('group');

        if ($request->group_id)   $query->where('group_id', $request->group_id);
        if ($request->type)       $query->where('type', $request->type);
        if ($request->status)     $query->where('status', $request->status);
        if ($request->month) {
            $query->whereYear('date', substr($request->month, 0, 4))->whereMonth('date', (int) substr($request->month, 5, 2));
        }

        $lectures = $query->orderByDesc('date')->orderByDesc('start_time')->get()->map(fn ($l) => [
            'id'          => $l->id,
            'title'       => $l->title,
            'type'        => $l->type,
            'status'      => $l->status,
            'date'        => $l->date?->format('Y-m-d'),
            'start_time'  => $l->start_time ? substr($l->start_time, 0, 5) : null,
            'end_time'    => $l->end_time   ? substr($l->end_time,   0, 5) : null,
            'group_id'    => $l->group_id,
            'group_name'  => $l->group?->name,
            'description' => $l->description,
            'is_today'    => $l->date?->isToday(),
            'is_past'     => $l->date?->lt(today()),
        ]);

        $today = $teacher->lectures()->whereDate('date', today())->count();
        $week  = $teacher->lectures()->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->count();

        return response()->json(['success' => true, 'lectures' => $lectures, 'today_count' => $today, 'week_count' => $week]);
    }

    public function getTeacherGroupData()
    {
        $teacher = auth()->user()->teacher;
        $groups  = $teacher->assignedGroups()->with('subjects')->get();
        return response()->json([
            'success' => true,
            'groups'  => $groups->map(fn ($g) => [
                'id'       => $g->id,
                'name'     => $g->name,
                'subjects' => $g->subjects->map(fn ($s) => ['id' => $s->id, 'name' => $s->name]),
            ]),
        ]);
    }

    public function storeTeacherLecture(Request $request)
    {
        $teacher   = auth()->user()->teacher;
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:lecture,exam,review,activity',
            'date'        => 'required|date',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'group_id'    => 'required|exists:groups,id',
            'subject_id'  => 'nullable|exists:subjects,id',
            'description' => 'nullable|string|max:1000',
        ]);

        if (! $teacher->assignedGroups()->where('groups.id', $validated['group_id'])->exists()) {
            return response()->json(['success' => false, 'message' => 'ليس لديك صلاحية على هذه المجموعة'], 403);
        }

        $lecture = Lecture::create([...$validated, 'teacher_id' => $teacher->id, 'status' => 'scheduled']);

        return response()->json(['success' => true, 'message' => 'تم إنشاء المحاضرة بنجاح', 'lecture' => $lecture->load('group')], 201);
    }

    public function updateTeacherLecture(Request $request, Lecture $lecture)
    {
        $teacher = auth()->user()->teacher;
        if ($lecture->teacher_id !== $teacher->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:lecture,exam,review,activity',
            'date'        => 'required|date',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'group_id'    => 'required|exists:groups,id',
            'subject_id'  => 'nullable|exists:subjects,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $lecture->update($validated);
        return response()->json(['success' => true, 'message' => 'تم تحديث المحاضرة بنجاح']);
    }

    public function destroyTeacherLecture(Lecture $lecture)
    {
        $teacher = auth()->user()->teacher;
        if ($lecture->teacher_id !== $teacher->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }
        $lecture->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف المحاضرة']);
    }

    public function rescheduleTeacherLecture(Request $request, Lecture $lecture)
    {
        $teacher = auth()->user()->teacher;
        if ($lecture->teacher_id !== $teacher->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        $validated = $request->validate([
            'new_date'       => 'required|date',
            'new_start_time' => 'nullable|date_format:H:i',
            'new_end_time'   => 'nullable|date_format:H:i',
            'reason'         => 'nullable|string',
        ]);

        $lecture->update([
            'date'             => $validated['new_date'],
            'start_time'       => $validated['new_start_time'] ?? $lecture->start_time,
            'end_time'         => $validated['new_end_time']   ?? $lecture->end_time,
            'status'           => 'scheduled',
            'reschedule_reason'=> $validated['reason'] ?? null,
            'rescheduled_at'   => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'تم تأجيل المحاضرة بنجاح']);
    }

    public function cancelTeacherLecture(Request $request, Lecture $lecture)
    {
        $teacher = auth()->user()->teacher;
        if ($lecture->teacher_id !== $teacher->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        $lecture->update([
            'status'              => 'cancelled',
            'cancellation_reason' => $request->get('reason'),
            'cancelled_at'        => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'تم إلغاء المحاضرة']);
    }

    public function getTeacherLectureStudents(Lecture $lecture)
    {
        $teacher = auth()->user()->teacher;
        if ($lecture->teacher_id !== $teacher->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        $students = $lecture->group?->students()->with('user')->get() ?? collect();
        $existing = Attendance::where('lecture_id', $lecture->id)->pluck('status', 'student_id');

        return response()->json([
            'success'  => true,
            'lecture'  => ['id' => $lecture->id, 'title' => $lecture->title, 'date' => $lecture->date->format('Y-m-d'), 'group_name' => $lecture->group?->name],
            'students' => $students->map(fn ($s) => ['id' => $s->id, 'name' => $s->user?->name ?? '—']),
            'existing' => $existing,
        ]);
    }

    public function saveTeacherLectureAttendance(Request $request, Lecture $lecture)
    {
        $teacher = auth()->user()->teacher;
        if ($lecture->teacher_id !== $teacher->id) {
            return response()->json(['success' => false, 'message' => 'غير مصرح'], 403);
        }

        if ($lecture->date->gt(today())) {
            return response()->json(['success' => false, 'message' => 'لا يمكن تسجيل حضور لمحاضرة مستقبلية'], 400);
        }

        $validated = $request->validate([
            'attendance'   => 'required|array',
            'attendance.*' => 'required|in:present,absent,late',
        ]);

        foreach ($validated['attendance'] as $studentId => $status) {
            Attendance::updateOrCreate(
                ['student_id' => $studentId, 'lecture_id' => $lecture->id],
                ['status'     => $status]
            );
        }

        return response()->json(['success' => true, 'message' => 'تم حفظ الحضور بنجاح لـ ' . count($validated['attendance']) . ' طالب']);
    }

    private function getTeacherAttendanceRate($teacherId, $month = null)
    {
        $month = $month ?: now()->format('Y-m');

        $totalExpected = Attendance::whereHas('lecture', function ($q) use ($teacherId, $month) {
            $q->where('teacher_id', $teacherId)
                ->whereYear('date', substr($month, 0, 4))->whereMonth('date', (int) substr($month, 5, 2));
        })->count();

        if ($totalExpected === 0) {
            return 0;
        }

        $presentCount = Attendance::present()->whereHas('lecture', function ($q) use ($teacherId, $month) {
            $q->where('teacher_id', $teacherId)
                ->whereYear('date', substr($month, 0, 4))->whereMonth('date', (int) substr($month, 5, 2));
        })->count();

        return round(($presentCount / $totalExpected) * 100, 2);
    }
}