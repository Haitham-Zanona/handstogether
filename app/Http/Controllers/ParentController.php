<?php
namespace App\Http\Controllers;

use App\Models\FinalExamScore;
use App\Models\MonthlyTestScore;
use App\Models\ParentMessage;
use App\Models\Payment;
use App\Models\StudentEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParentController extends Controller
{
    public function dashboard()
    {
        $children = auth()->user()->children()
            ->with(['user:id,name', 'group:id,name'])
            ->get();

        $childIds = $children->pluck('id');

        // استعلام واحد لكل الإحصائيات بدل 3 استعلامات منفصلة
        $pendingPayments = $childIds->isEmpty() ? 0 :
            Payment::whereIn('student_id', $childIds)->pending()->count();

        $stats = [
            'total_children'        => $children->count(),
            'pending_payments'      => $pendingPayments,
            'this_month_attendance' => $this->getChildrenAttendanceThisMonth($childIds),
        ];

        return view('parent.dashboard', compact('children', 'stats'));
    }

    public function attendance()
    {
        $children = auth()->user()->children()
            ->with(['user:id,name', 'group:id,name'])
            ->get();

        $attendanceData = $children->mapWithKeys(function ($child) {
            return [$child->id => [
                'name'                  => $child->user->name,
                'group'                 => $child->group->name ?? 'غير محدد',
                'monthly_attendance'    => $child->getMonthlyAttendance(),
                'attendance_percentage' => $child->getAttendancePercentage(),
            ]];
        });

        return view('parent.attendance', compact('children', 'attendanceData'));
    }

    public function schedule()
    {
        $children = auth()->user()->children()
            ->with(['user:id,name', 'group:id,name'])
            ->get();

        $schedules = $children->mapWithKeys(function ($child) {
            return [$child->id => [
                'name'              => $child->user->name,
                'upcoming_lectures' => $child->getUpcomingLectures(),
            ]];
        });

        return view('parent.schedule', compact('children', 'schedules'));
    }

    public function payments()
    {
        $childIds = auth()->user()->children()->pluck('id');

        // استعلام واحد للدفعات مع paginate
        $payments = Payment::whereIn('student_id', $childIds)
            ->with(['student:id,user_id', 'student.user:id,name'])
            ->latest('created_at')
            ->paginate(20);

        // استعلام واحد لكل الإحصائيات باستخدام selectRaw
        $paymentStats = Payment::whereIn('student_id', $childIds)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END), 0) as total_paid,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'unpaid' AND month < ? THEN 1 ELSE 0 END) as overdue_count
            ", [now()->format('Y-m')])
            ->first();

        $children = auth()->user()->children()->with('user:id,name')->get();

        return view('parent.payments', compact('children', 'payments', 'paymentStats'));
    }

    public function grades()
    {
        $children = auth()->user()->children()->with(['group:id,name,grade_weights', 'user:id,name'])->get();
        $childIds = $children->pluck('id');

        // تحميل كل الدرجات دفعة واحدة بدل N+1
        $allEvals  = StudentEvaluation::whereIn('student_id', $childIds)->get()->groupBy('student_id');
        $allTests  = MonthlyTestScore::whereIn('student_id', $childIds)->get()->groupBy('student_id');
        $allFinals = FinalExamScore::whereIn('student_id', $childIds)->get()->keyBy('student_id');

        $gradesData = $children->mapWithKeys(function ($child) use ($allEvals, $allTests, $allFinals) {
            $group   = $child->group;
            $weights = $group?->weights ?? ['evaluations' => 40, 'monthly_tests' => 30, 'final_exam' => 30];

            $evals     = ($allEvals[$child->id] ?? collect())->keyBy('eval_number');
            $tests     = ($allTests[$child->id] ?? collect())->keyBy('test_number');
            $finalExam = $allFinals[$child->id] ?? null;

            $evalSum    = $evals->sum(fn ($e) =>
                $e->activity_participation + $e->behavior_discipline +
                $e->academic_improvement + $e->homework + $e->short_tests
            );
            $evalGrade  = $evals->count() > 0 ? round(($evalSum / (200 * $evals->count())) * $weights['evaluations'], 2) : 0;
            $testGrade  = $tests->count() > 0 ? round(($tests->sum('score') / (20 * $tests->count())) * $weights['monthly_tests'], 2) : 0;
            $finalGrade = (float) ($finalExam?->score ?? 0);

            return [$child->id => [
                'name'        => $child->user->name,
                'group'       => $group?->name ?? 'غير محدد',
                'evaluations' => $evals,
                'tests'       => $tests,
                'final_exam'  => $finalExam,
                'eval_grade'  => $evalGrade,
                'test_grade'  => $testGrade,
                'final_grade' => $finalGrade,
                'total'       => round($evalGrade + $testGrade + $finalGrade, 2),
                'weights'     => $weights,
            ]];
        });

        return view('parent.grades', compact('children', 'gradesData'));
    }

    public function messages()
    {
        $children = auth()->user()->children()->with('user:id,name')->get();
        $messages = ParentMessage::where('parent_user_id', auth()->id())
            ->with(['student:id,user_id', 'student.user:id,name'])
            ->latest()
            ->paginate(20);

        return view('parent.messages', compact('children', 'messages'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'message'    => 'required|string|min:5|max:1000',
        ]);

        $children = auth()->user()->children()->pluck('id');
        if (! $children->contains($request->student_id)) {
            return back()->withErrors(['student_id' => 'غير مصرح']);
        }

        $msg = ParentMessage::create([
            'parent_user_id' => auth()->id(),
            'student_id'     => $request->student_id,
            'message'        => $request->message,
        ]);

        // إشعار الأدمن - SELECT الأعمدة المطلوبة فقط
        $adminUsers = \App\Models\User::select('id')->where('role', 'admin')->get();
        foreach ($adminUsers as $admin) {
            $admin->notify(new \App\Notifications\ParentMessageNotification($msg));
        }

        return back()->with('success', 'تم إرسال رسالتك بنجاح. سيتواصل معك فريق الإدارة قريباً.');
    }

    private function getChildrenAttendanceThisMonth($childIds): float
    {
        if ($childIds->isEmpty()) {
            return 0;
        }

        $month = now()->format('Y-m');

        // استعلام واحد بدل N استعلامات
        $result = DB::selectOne("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count
            FROM attendance a
            INNER JOIN lectures l ON l.id = a.lecture_id
            WHERE a.student_id IN (" . implode(',', $childIds->toArray()) . ")
            AND l.date BETWEEN ? AND ?
        ", [substr($month, 0, 7) . '-01', date('Y-m-t', strtotime(substr($month, 0, 7) . '-01'))]);

        $total = $result->total ?? 0;
        return $total > 0 ? round((($result->present_count ?? 0) / $total) * 100, 2) : 0;
    }
}
