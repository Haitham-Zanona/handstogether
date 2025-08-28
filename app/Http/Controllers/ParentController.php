<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Payment;

class ParentController extends Controller
{
    public function dashboard()
    {
        $children = auth()->user()->children()
            ->with(['group', 'payments', 'attendance'])
            ->get();

        $stats = [
            'total_children'        => $children->count(),
            'pending_payments'      => Payment::whereIn('student_id', $children->pluck('id'))
                ->pending()
                ->count(),
            'this_month_attendance' => $this->getChildrenAttendanceThisMonth($children),
        ];

        return view('parent.dashboard', compact('children', 'stats'));
    }

    public function attendance()
    {
        $children = auth()->user()->children()
            ->with(['user', 'group', 'attendance.lecture'])
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
        $children = auth()->user()->children()->with('group.lectures.teacher.user')->get();

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
        $children = auth()->user()->children()->get();

        $payments = Payment::whereIn('student_id', $children->pluck('id'))
            ->with('student.user')
            ->latest()
            ->paginate(20);

        $paymentStats = [
            'total_paid'    => Payment::whereIn('student_id', $children->pluck('id'))
                ->paid()
                ->sum('amount'),
            'pending_count' => Payment::whereIn('student_id', $children->pluck('id'))
                ->pending()
                ->count(),
            'overdue_count' => Payment::whereIn('student_id', $children->pluck('id'))
                ->where('month', '<', now()->format('Y-m'))
                ->unpaid()
                ->count(),
        ];

        return view('parent.payments', compact('children', 'payments', 'paymentStats'));
    }

    private function getChildrenAttendanceThisMonth($children)
    {
        $totalLectures = 0;
        $presentCount  = 0;

        foreach ($children as $child) {
            $monthlyAttendance = $child->getMonthlyAttendance();
            $totalLectures += $monthlyAttendance->flatten()->count();
            $presentCount += $monthlyAttendance->get('present', collect())->count();
        }

        return $totalLectures > 0 ? round(($presentCount / $totalLectures) * 100, 2) : 0;
    }
}
