<?php
namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Attendance;
use App\Models\Group;
use App\Models\Lecture;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\AcademyNotification;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $studentsCount     = Student::count();
        $teachersCount     = Teacher::count();
        $groupsCount       = Group::count();
        $pendingAdmissions = Admission::pending()->count();

        // Get upcoming lectures for calendar
        $lectures = Lecture::with(['teacher.user', 'group'])
            ->where('date', '>=', today())
            ->get()
            ->map(fn($lecture) => $lecture->toCalendarEvent());

        // Get recent statistics
        $monthlyStats = [
            'new_students'    => Student::whereMonth('created_at', now()->month)->count(),
            'total_payments'  => Payment::paid()->whereMonth('created_at', now()->month)->sum('amount'),
            'attendance_rate' => $this->getOverallAttendanceRate(),
        ];

        return view('admin.dashboard', compact(
            'studentsCount', 'teachersCount', 'groupsCount',
            'pendingAdmissions', 'lectures', 'monthlyStats'
        ));
    }

    public function admissions()
    {
        $admissions = Admission::latest()->paginate(20);
        $groups     = Group::all();

        return view('admin.admissions', compact('admissions', 'groups'));
    }

    public function approveAdmission(Request $request, Admission $admission)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        try {
            $student = $admission->convertToStudent($request->group_id);

            // Send notification to parent
            $student->parent->notify(new AcademyNotification(
                'تم قبول طلب انتساب ' . $student->user->name . ' بنجاح',
                route('parent.dashboard')
            ));

            return back()->with('success', 'تم قبول طلب الانتساب بنجاح');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectAdmission(Admission $admission)
    {
        $admission->update(['status' => 'rejected']);
        return back()->with('success', 'تم رفض طلب الانتساب');
    }

    public function groups()
    {
        $groups = Group::withCount('students')->get();
        return view('admin.groups', compact('groups'));
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            'name'        => 'required|max:255',
            'description' => 'nullable',
        ]);

        Group::create($request->all());
        return back()->with('success', 'تم إنشاء المجموعة بنجاح');
    }

    public function attendance()
    {
        $groups = Group::with(['students.user', 'students.attendance' => function ($q) {
            $q->whereHas('lecture', function ($query) {
                $query->whereMonth('date', now()->month);
            });
        }])->get();

        $attendanceStats = [
            'total_lectures'   => Lecture::whereMonth('date', now()->month)->count(),
            'total_attendance' => Attendance::whereHas('lecture', function ($q) {
                $q->whereMonth('date', now()->month);
            })->count(),
            'present_count'    => Attendance::present()->whereHas('lecture', function ($q) {
                $q->whereMonth('date', now()->month);
            })->count(),
        ];

        return view('admin.attendance', compact('groups', 'attendanceStats'));
    }

    public function payments()
    {
        $currentMonth = now()->format('Y-m');

        $payments = Payment::with(['student.user', 'student.parent'])
            ->where('month', $currentMonth)
            ->latest()
            ->paginate(20);

        $paymentStats = [
            'total_expected' => Student::count() * 1000, // Assuming 1000 per student
            'total_paid'     => Payment::paid()->where('month', $currentMonth)->sum('amount'),
            'pending_count'  => Payment::pending()->where('month', $currentMonth)->count(),
            'overdue_count'  => Payment::where('month', '<', $currentMonth)->unpaid()->count(),
        ];

        return view('admin.payments', compact('payments', 'paymentStats', 'currentMonth'));
    }

    public function markPaymentAsPaid(Payment $payment)
    {
        $payment->update([
            'status'         => 'paid',
            'paid_date'      => now(),
            'payment_method' => 'cash', // Default, can be changed
        ]);

        // Notify parent
        $payment->student->parent->notify(new AcademyNotification(
            'تم استلام دفعة ' . $payment->formatted_month . ' للطالب ' . $payment->student->user->name,
            route('parent.payments')
        ));

        return back()->with('success', 'تم تحديث حالة الدفع');
    }

    private function getOverallAttendanceRate()
    {
        $totalLectures = Lecture::whereMonth('date', now()->month)->count();
        if ($totalLectures === 0) {
            return 0;
        }

        $totalStudents      = Student::count();
        $expectedAttendance = $totalLectures * $totalStudents;

        if ($expectedAttendance === 0) {
            return 0;
        }

        $actualAttendance = Attendance::present()->whereHas('lecture', function ($q) {
            $q->whereMonth('date', now()->month);
        })->count();

        return round(($actualAttendance / $expectedAttendance) * 100, 2);
    }
}
