<?php
namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Attendance;
use App\Models\Group;
use App\Models\Lecture;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

            // استخدام NotificationService بدلاً من الإرسال المباشر
            NotificationService::notifyAdmissionApproved($student);

            return back()->with('success', 'تم قبول طلب الانتساب بنجاح وإرسال إشعار لولي الأمر');
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في معالجة الطلب: ' . $e->getMessage());
        }
    }

    public function rejectAdmission(Request $request, Admission $admission)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $admission->update([
            'status'      => 'rejected',
            'notes'       => $request->rejection_reason,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        // إشعار ولي الأمر بالرفض
        if ($admission->email) {
            // يمكن إضافة إشعار بريد إلكتروني هنا
        }

        return back()->with('success', 'تم رفض طلب الانتساب');
    }

    public function reports()
    {
        // إحصائيات عامة
        $stats = [
            'total_students'            => Student::count(),
            'total_teachers'            => Teacher::count(),
            'total_groups'              => Group::count(),
            'total_lectures_this_month' => Lecture::whereMonth('date', now()->month)->count(),
        ];

        // تقارير الحضور
        $attendanceStats = [
            'total_attendance' => Attendance::whereHas('lecture', function ($q) {
                $q->whereMonth('date', now()->month);
            })->count(),
            'present_count'    => Attendance::present()->whereHas('lecture', function ($q) {
                $q->whereMonth('date', now()->month);
            })->count(),
        ];

        // تقارير المدفوعات
        $paymentStats = [
            'total_paid'       => Payment::paid()->whereMonth('created_at', now()->month)->sum('amount'),
            'pending_payments' => Payment::pending()->count(),
            'overdue_payments' => Payment::unpaid()->where('month', '<', now()->format('Y-m'))->count(),
        ];

        return view('admin.reports', compact('stats', 'attendanceStats', 'paymentStats'));

    }

    public function settings()
    {
        $academySettings = [
            'academy_name'    => 'الأكاديمية التعليمية',
            'academy_email'   => 'info@academy.edu',
            'academy_phone'   => '+970-599-123456',
            'academy_address' => 'نابلس، فلسطين',
            'monthly_fee'     => 1000,
            'working_hours'   => 'الأحد - الخميس: 8:00 ص - 4:00 م',
        ];

        // إعدادات النظام
        $systemSettings = [
            'auto_notifications'  => true,
            'email_notifications' => true,
            'sms_notifications'   => false,
            'attendance_reminder' => true,
            'payment_reminder'    => true,
        ];

        return view('admin.settings', compact('academySettings', 'systemSettings'));

    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'academy_name'  => 'required|string|max:255',
            'academy_email' => 'required|email',
            'academy_phone' => 'required|string|max:20',
            'monthly_fee'   => 'required|numeric|min:0',
        ]);

        // في التطبيق الحقيقي، احفظ في config أو settings table
        // هنا سنعرض رسالة نجاح فقط

        return back()->with('success', 'تم حفظ الإعدادات بنجاح');
    }

    public function clearData(Request $request)
    {
        try {
            // حذف جميع البيانات باستثناء المستخدمين الأساسيين
            DB::transaction(function () {
                // حذف الحضور
                Attendance::truncate();

                // حذف المحاضرات
                Lecture::truncate();

                // حذف المدفوعات
                Payment::truncate();

                // حذف الطلاب
                Student::truncate();

                // حذف المعلمين
                Teacher::truncate();

                // حذف طلبات الانتساب
                Admission::truncate();

                // إعادة تعيين المجموعات (اختياري)
                Group::truncate();
            });

            return response()->json([
                'success' => true,
                'message' => 'تم مسح جميع البيانات بنجاح',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء مسح البيانات: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function resetSystem(Request $request)
    {
        try {
            DB::transaction(function () {
                // حذف جميع البيانات
                Attendance::truncate();
                Lecture::truncate();
                Payment::truncate();
                Student::truncate();
                Teacher::truncate();
                Admission::truncate();

                // إعادة إنشاء المجموعات الافتراضية
                Group::truncate();
                Group::create([
                    'name'        => 'المجموعة الأولى',
                    'description' => 'مجموعة افتراضية للطلاب الجدد',
                ]);

                Group::create([
                    'name'        => 'المجموعة الثانية',
                    'description' => 'مجموعة افتراضية للطلاب المتقدمين',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'تم إعادة تعيين النظام بنجاح',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إعادة تعيين النظام: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function groups()
    {
        $groups = Group::withCount('students')->orderBy('name')->get();
        return view('admin.groups', compact('groups'));
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            'name'        => 'required|max:255|unique:groups,name',
            'description' => 'nullable|string|max:1000',
        ]);

        $group = Group::create($request->all());

        return back()->with('success', 'تم إنشاء المجموعة "' . $group->name . '" بنجاح');
    }

    public function updateGroup(Request $request, Group $group)
    {
        $request->validate([
            'name'        => 'required|max:255|unique:groups,name,' . $group->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $group->update($request->all());

        return back()->with('success', 'تم تحديث المجموعة بنجاح');
    }

    public function destroyGroup(Group $group)
    {
        if ($group->students()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف مجموعة تحتوي على طلاب');
        }

        if ($group->lectures()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف مجموعة تحتوي على محاضرات');
        }

        $groupName = $group->name;
        $group->delete();

        return back()->with('success', 'تم حذف المجموعة "' . $groupName . '" بنجاح');
    }

    public function getCalendarData()
    {

        $lectures = Lecture::with(['teacher.user', 'group'])
            ->get()
            ->map(function ($lecture) {
                return [
                    'id'                 => $lecture->id,
                    'title'              => $lecture->title,
                    'date'               => $lecture->date->format('Y-m-d'),
                    'start_time'         => $lecture->start_time->format('H:i'),
                    'end_time'           => $lecture->end_time->format('H:i'),
                    'description'        => $lecture->description ?? '',
                    'teacher'            => [
                        'user' => [
                            'name' => $lecture->teacher->user->name,
                        ],
                    ],
                    'group'              => [
                        'id'   => $lecture->group->id,
                        'name' => $lecture->group->name,
                    ],
                    'is_today'           => $lecture->is_today,
                    'has_started'        => $lecture->has_started,
                    'attendance_summary' => $lecture->getAttendanceSummary(),
                ];
            });

        return response()->json($lectures);

    }

    public function attendance()
    {
        $groups = Group::with([
            'students.user',
            'students.attendance' => function ($q) {
                $q->whereHas('lecture', function ($query) {
                    $query->whereMonth('date', now()->month);
                });
            },
        ])->get();

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
            'total_expected' => Student::count() * 1000, // افتراض 1000 لكل طالب
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
            'payment_method' => 'cash', // يمكن تعديله ليكون من الطلب
        ]);

        // استخدام NotificationService
        NotificationService::notifyPaymentReceived($payment);

        return back()->with('success', 'تم تحديث حالة الدفع وإرسال إشعار لولي الأمر');
    }

    public function createLecture(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'date'        => 'required|date|after_or_equal:today',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'teacher_id'  => 'required|exists:teachers,id',
            'group_id'    => 'required|exists:groups,id',
            'description' => 'nullable|string|max:1000',
        ]);

        $lecture = Lecture::create($request->all());

        // إشعار الطلاب وأولياء الأمور
        NotificationService::notifyNewLecture($lecture);

        return back()->with('success', 'تم إضافة المحاضرة وإرسال إشعارات للطلاب وأولياء الأمور');
    }

    public function bulkPaymentReminder()
    {
        NotificationService::notifyOverduePayments();

        $overdueCount = Payment::where('status', 'unpaid')
            ->where('month', '<', now()->format('Y-m'))
            ->count();

        return back()->with('success', "تم إرسال تذكيرات الدفع لـ {$overdueCount} ولي أمر");
    }

    public function lowAttendanceReport()
    {
        NotificationService::notifyLowAttendance(75); // أقل من 75%

        $lowAttendanceCount = Student::get()->filter(function ($student) {
            return $student->getAttendancePercentage() < 75;
        })->count();

        return back()->with('success', "تم إرسال تنبيهات نسبة الحضور المنخفض لـ {$lowAttendanceCount} ولي أمر");
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