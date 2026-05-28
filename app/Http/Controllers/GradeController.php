<?php
namespace App\Http\Controllers;

use App\Models\FinalExamScore;
use App\Models\Group;
use App\Models\Lecture;
use App\Models\MonthlyTestScore;
use App\Models\Student;
use App\Models\StudentEvaluation;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    // ─── Views ───────────────────────────────────────────────────────────────

    public function adminIndex()
    {
        return view('admin.grades');
    }

    public function teacherIndex()
    {
        return view('teacher.grades');
    }

    // ─── Data APIs ────────────────────────────────────────────────────────────

    public function getGroupsData()
    {
        if (auth()->user()->isAdmin()) {
            $groups = Group::select('id', 'name', 'grade_level', 'grade_weights', 'is_active', 'is_archived', 'final_exam_active', 'start_date', 'end_date')
                ->where('is_active', true)->orderBy('name')->get();
        } else {
            $teacher = auth()->user()->teacher;
            $groups  = $teacher
                ? $teacher->assignedGroups()
                    ->select('groups.id', 'groups.name', 'groups.grade_level', 'groups.grade_weights', 'groups.is_active', 'groups.is_archived', 'groups.final_exam_active', 'groups.start_date', 'groups.end_date')
                    ->orderBy('groups.name')->get()
                : collect();
        }
        return response()->json(['success' => true, 'groups' => $groups]);
    }

    public function getGradesData(Request $request)
    {
        $groupId = $request->get('group_id');

        if (! $groupId) {
            return response()->json(['success' => false, 'message' => 'يرجى اختيار المجموعة']);
        }

        $group = Group::findOrFail($groupId);

        if (auth()->user()->isTeacher()) {
            $teacher = auth()->user()->teacher;
            if (! $teacher || ! $teacher->assignedGroups()->where('groups.id', $groupId)->exists()) {
                return response()->json(['success' => false, 'message' => 'غير مصرح لك بعرض هذه المجموعة'], 403);
            }
        }

        $weights  = $group->weights;
        $students = Student::with('user')->where('group_id', $groupId)->get();

        $studentsData = $students->map(function ($student) use ($groupId, $weights) {
            $evals = StudentEvaluation::where('group_id', $groupId)
                ->where('student_id', $student->id)->get()->keyBy('eval_number');

            $tests = MonthlyTestScore::where('group_id', $groupId)
                ->where('student_id', $student->id)->get()->keyBy('test_number');

            $finalExam = FinalExamScore::where('group_id', $groupId)
                ->where('student_id', $student->id)->first();

            $evalSum   = $evals->sum(fn ($e) =>
                $e->activity_participation + $e->behavior_discipline +
                $e->academic_improvement   + $e->homework + $e->short_tests
            );
            $evalGrade = round(($evalSum / 200) * $weights['evaluations'], 2);

            $testSum   = (float) $tests->sum('score');
            $testGrade = round(($testSum / 60) * $weights['monthly_tests'], 2);

            $finalGrade = (float) ($finalExam?->score ?? 0);

            return [
                'id'   => $student->id,
                'name' => $student->user?->name ?? '—',
                'evaluations' => $evals->map(fn ($e) => [
                    'id'                    => $e->id,
                    'activity_participation'=> $e->activity_participation,
                    'behavior_discipline'   => $e->behavior_discipline,
                    'academic_improvement'  => $e->academic_improvement,
                    'homework'              => $e->homework,
                    'short_tests'           => $e->short_tests,
                    'notes'                 => $e->notes,
                ])->toArray(),
                'monthly_tests' => $tests->map(fn ($t) => [
                    'id'    => $t->id,
                    'score' => (float) $t->score,
                    'month' => $t->month,
                    'notes' => $t->notes,
                ])->toArray(),
                'final_exam' => $finalExam ? [
                    'id'    => $finalExam->id,
                    'score' => (float) $finalExam->score,
                    'notes' => $finalExam->notes,
                ] : null,
                'grades' => [
                    'eval_sum'   => $evalSum,
                    'eval_count' => $evals->count(),
                    'eval_grade' => $evalGrade,
                    'test_sum'   => $testSum,
                    'test_grade' => $testGrade,
                    'final_grade'=> $finalGrade,
                    'total'      => round($evalGrade + $testGrade + $finalGrade, 2),
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'group'   => [
                'id'                  => $group->id,
                'name'                => $group->name,
                'grade_weights'       => $group->grade_weights,
                'final_exam_active'   => $group->final_exam_active,
                'current_eval_period' => $group->current_eval_period,
            ],
            'students' => $studentsData,
        ]);
    }

    // ─── Save Operations ──────────────────────────────────────────────────────

    public function saveEvaluationsBatch(Request $request)
    {
        $validated = $request->validate([
            'group_id'                             => 'required|exists:groups,id',
            'eval_number'                          => 'required|integer|between:1,8',
            'evaluations'                          => 'required|array',
            'evaluations.*.student_id'             => 'required|exists:students,id',
            'evaluations.*.activity_participation' => 'required|integer|between:1,5',
            'evaluations.*.behavior_discipline'    => 'required|integer|between:1,5',
            'evaluations.*.academic_improvement'   => 'required|integer|between:1,5',
            'evaluations.*.homework'               => 'required|integer|between:1,5',
            'evaluations.*.short_tests'            => 'required|integer|between:1,5',
            'evaluations.*.notes'                  => 'nullable|string',
        ]);

        $teacherId = auth()->user()->teacher?->id;

        foreach ($validated['evaluations'] as $e) {
            StudentEvaluation::updateOrCreate(
                [
                    'group_id'    => $validated['group_id'],
                    'student_id'  => $e['student_id'],
                    'eval_number' => $validated['eval_number'],
                ],
                [
                    'teacher_id'             => $teacherId,
                    'activity_participation' => $e['activity_participation'],
                    'behavior_discipline'    => $e['behavior_discipline'],
                    'academic_improvement'   => $e['academic_improvement'],
                    'homework'               => $e['homework'],
                    'short_tests'            => $e['short_tests'],
                    'notes'                  => $e['notes'] ?? null,
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'تم حفظ التقييمات بنجاح']);
    }

    public function saveTestsBatch(Request $request)
    {
        $validated = $request->validate([
            'group_id'             => 'required|exists:groups,id',
            'test_number'          => 'required|integer|between:1,3',
            'month'                => 'required|string',
            'scores'               => 'required|array',
            'scores.*.student_id'  => 'required|exists:students,id',
            'scores.*.score'       => 'required|numeric|min:0|max:20',
            'scores.*.notes'       => 'nullable|string',
        ]);

        foreach ($validated['scores'] as $s) {
            MonthlyTestScore::updateOrCreate(
                [
                    'group_id'    => $validated['group_id'],
                    'student_id'  => $s['student_id'],
                    'test_number' => $validated['test_number'],
                ],
                [
                    'month'      => $validated['month'],
                    'score'      => $s['score'],
                    'notes'      => $s['notes'] ?? null,
                    'entered_by' => auth()->id(),
                ]
            );
        }

        return response()->json(['success' => true, 'message' => 'تم حفظ درجات الاختبار بنجاح']);
    }

    public function saveFinalBatch(Request $request)
    {
        $group = Group::findOrFail($request->group_id);

        if (! $group->final_exam_active) {
            return response()->json(['success' => false, 'message' => 'لم يتم فتح الامتحان النهائي بعد من قِبل الإدارة'], 400);
        }

        $maxScore = $group->weights['final_exam'];

        $validated = $request->validate([
            'group_id'            => 'required|exists:groups,id',
            'scores'              => 'required|array',
            'scores.*.student_id' => 'required|exists:students,id',
            'scores.*.score'      => "required|numeric|min:0|max:{$maxScore}",
            'scores.*.notes'      => 'nullable|string',
        ]);

        foreach ($validated['scores'] as $s) {
            FinalExamScore::updateOrCreate(
                ['group_id' => $validated['group_id'], 'student_id' => $s['student_id']],
                ['score' => $s['score'], 'notes' => $s['notes'] ?? null, 'entered_by' => auth()->id()]
            );
        }

        return response()->json(['success' => true, 'message' => 'تم حفظ درجات الامتحان النهائي بنجاح']);
    }

    // ─── Group Grade Settings (Admin) ─────────────────────────────────────────

    public function updateGroupGradeSettings(Request $request, Group $group)
    {
        if ($group->is_archived) {
            return response()->json(['success' => false, 'message' => 'لا يمكن تعديل مجموعة مؤرشفة'], 400);
        }

        $validated = $request->validate([
            'grade_weights'               => 'required|array',
            'grade_weights.evaluations'   => 'required|integer|min:1|max:98',
            'grade_weights.monthly_tests' => 'required|integer|min:1|max:98',
            'grade_weights.final_exam'    => 'required|integer|min:1|max:98',
            'start_date'                  => 'nullable|date',
            'end_date'                    => 'nullable|date|after:start_date',
        ]);

        $w = $validated['grade_weights'];
        if ($w['evaluations'] + $w['monthly_tests'] + $w['final_exam'] !== 100) {
            return response()->json(['success' => false, 'message' => 'مجموع الأوزان يجب أن يساوي 100 درجة'], 422);
        }

        $group->update($validated);

        return response()->json(['success' => true, 'message' => 'تم تحديث إعدادات المجموعة']);
    }

    public function toggleGroupFinalExam(Group $group)
    {
        $group->update(['final_exam_active' => ! $group->final_exam_active]);
        $status = $group->final_exam_active ? 'مفتوح' : 'مغلق';
        return response()->json(['success' => true, 'message' => "الامتحان النهائي أصبح {$status}"]);
    }

    // ─── Archive ──────────────────────────────────────────────────────────────

    public function adminArchive()
    {
        return view('admin.archive');
    }

    public function archiveGroup(Group $group)
    {
        $group->update([
            'is_archived'       => true,
            'is_active'         => false,
            'final_exam_active' => false,
        ]);
        return response()->json(['success' => true, 'message' => "تم أرشفة المجموعة \"{$group->name}\" بنجاح"]);
    }

    public function getArchivedGroupsData()
    {
        $groups = Group::where('is_archived', true)
            ->orderByDesc('end_date')
            ->get(['id', 'name', 'grade_level', 'grade_weights', 'start_date', 'end_date']);

        return response()->json(['success' => true, 'groups' => $groups]);
    }

    public function getArchiveStats(Request $request)
    {
        $group   = Group::findOrFail($request->group_id);
        $weights = $group->weights;

        $lectureCount = 0;
        if ($group->start_date) {
            $endDate      = $group->end_date?->format('Y-m-d') ?? now()->format('Y-m-d');
            $lectureCount = Lecture::whereBetween('date', [$group->start_date->format('Y-m-d'), $endDate])->count();
        }

        $studentIds = FinalExamScore::where('group_id', $group->id)->pluck('student_id')->unique();
        $dist = ['excellent' => 0, 'good' => 0, 'pass' => 0, 'fail' => 0];

        foreach ($studentIds as $sid) {
            $evalSum = StudentEvaluation::where('group_id', $group->id)->where('student_id', $sid)
                ->get()->sum(fn ($e) => $e->activity_participation + $e->behavior_discipline + $e->academic_improvement + $e->homework + $e->short_tests);
            $evalGrade  = round(($evalSum / 200) * $weights['evaluations'], 2);
            $testSum    = (float) MonthlyTestScore::where('group_id', $group->id)->where('student_id', $sid)->sum('score');
            $testGrade  = round(($testSum / 60) * $weights['monthly_tests'], 2);
            $finalGrade = (float) FinalExamScore::where('group_id', $group->id)->where('student_id', $sid)->value('score');
            $total      = round($evalGrade + $testGrade + $finalGrade, 2);

            if      ($total >= 90) $dist['excellent']++;
            elseif  ($total >= 75) $dist['good']++;
            elseif  ($total >= 60) $dist['pass']++;
            else                   $dist['fail']++;
        }

        return response()->json([
            'success' => true,
            'stats'   => [
                'lecture_count'      => $lectureCount,
                'students_evaluated' => StudentEvaluation::where('group_id', $group->id)->distinct('student_id')->count('student_id'),
                'students_tested'    => MonthlyTestScore::where('group_id', $group->id)->distinct('student_id')->count('student_id'),
                'students_final'     => $studentIds->count(),
                'grade_distribution' => $dist,
            ],
        ]);
    }
}
