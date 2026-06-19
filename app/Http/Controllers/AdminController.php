<?php
namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Attendance;
use App\Models\TeacherAttendance;
use App\Models\Expense;
use App\Models\Group;
use App\Models\GroupSubject;
use App\Models\Lecture;
use App\Models\LectureSeries;
use App\Models\ParentMessage;
use App\Models\Payment;
use App\Models\SalaryPayment;
use App\Models\Setting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Employee;
use App\Models\Teacher;
use App\Services\NotificationService;
use App\Services\SeriesGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{

    public function dashboard()
    {
        // الإحصائيات الثابتة: cache لمدة 5 دقائق
        $counts = Cache::remember('admin_dashboard_counts', 300, fn () => [
            'students'   => Student::count(),
            'teachers'   => Teacher::count(),
            'groups'     => Group::count(),
            'pending'    => Admission::pending()->count(),
        ]);

        $studentsCount     = $counts['students'];
        $teachersCount     = $counts['teachers'];
        $groupsCount       = $counts['groups'];
        $pendingAdmissions = $counts['pending'];

        // المحاضرات القادمة: SELECT الأعمدة المطلوبة فقط
        $lectures = Cache::remember('admin_dashboard_lectures', 120, fn () =>
            Lecture::select('id', 'title', 'date', 'start_time', 'end_time', 'type', 'status', 'teacher_id', 'group_id')
                ->with(['teacher:id,user_id', 'teacher.user:id,name', 'group:id,name'])
                ->where('date', '>=', today())
                ->where('status', '!=', 'cancelled')
                ->orderBy('date')->orderBy('start_time')
                ->limit(100)
                ->get()
                ->map(fn ($l) => $l->toCalendarEvent())
        );

        // الإحصائيات الشهرية: cache لمدة 10 دقائق
        $monthlyStats = Cache::remember('admin_dashboard_monthly_' . now()->format('Y-m'), 600, fn () => [
            'new_students'   => Student::whereMonth('created_at', now()->month)->count(),
            'total_payments' => Payment::paid()->whereMonth('paid_date', now()->month)->sum('amount'),
            'attendance_rate'=> $this->getOverallAttendanceRate(),
        ]);

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



    /**
     * عرض صفحة إدارة المجموعات الجديدة
     */
    public function groups()
    {
        // إذا كان الطلب Ajax، أرجع البيانات
        if (request()->wantsJson() || request()->ajax()) {
            return $this->getGroupsData();
        }

// حساب الإحصائيات للواجهة
        $stats = $this->calculateGroupsStats();

// وإلا أرجع الواجهة الجديدة مع الإحصائيات
        return view('admin.groups.index', compact('stats'));

    }

    public function data()
    {
        try {
            $groups = Group::with('teachers')->get();
            $stats  = [
                'total_groups'   => Group::count(),
                'total_students' => Student::count(),
                // ...
            ];

            return response()->json([
                'success' => true,
                'groups'  => $groups,
                'stats'   => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل البيانات',
            ], 500);
        }

    }

    private function calculateGroupsStats()
    {
        $totalGroups     = Group::count();
        $totalStudents   = Group::sum('students_count');
        $fullGroups      = Group::whereRaw('students_count >= max_capacity')->count();
        $todayLectures   = Lecture::whereDate('date', today())->count();
        $availableGroups = Group::where('is_active', true)
            ->whereRaw('students_count < max_capacity')
            ->count();
        $activeGroups = Group::where('is_active', true)->count();

        return [
            'total_groups'     => $totalGroups,
            'total_students'   => $totalStudents,
            'full_groups'      => $fullGroups,
            'today_lectures'   => $todayLectures,
            'available_groups' => $availableGroups,
            'active_groups'    => $activeGroups,
            'inactive_groups'  => $totalGroups - $activeGroups,
            'occupancy_rate'   => $totalGroups > 0 ? round(($fullGroups / $totalGroups) * 100, 1) : 0,
        ];

    }

    /**
     * جلب بيانات المجموعات للواجهة الديناميكية
     */
    public function getGroupsData()
    {
        $gradeOrder = [
            'الصف الأول'      => 1,
            'الصف الثاني'     => 2,
            'الصف الثالث'     => 3,
            'الصف الرابع'     => 4,
            'الصف الخامس'     => 5,
            'الصف السادس'     => 6,
            'الصف السابع'     => 7,
            'الصف الثامن'     => 8,
            'الصف التاسع'     => 9,
            'الصف العاشر'     => 10,
            'الصف الحادي عشر' => 11,
        ];

        try {
            // pre-load today's lecture counts per group in one query
            $todayCountsByGroup = Lecture::where('date', today()->toDateString())
                ->selectRaw('group_id, count(*) as cnt')
                ->groupBy('group_id')
                ->pluck('cnt', 'group_id');

            $groups = Group::with(['teachers.user', 'subjects'])
                ->withCount('students')
                ->get()
                ->map(function ($group) use ($todayCountsByGroup, $gradeOrder) {
                    return [
                        'id'                   => $group->id,
                        'name'                 => $group->name,
                        'grade_level'          => $group->grade_level,
                        'sort_order'           => $gradeOrder[$group->grade_level] ?? 99,
                        'section'              => $group->section,
                        'section_number'       => $group->section_number,
                        'students_count'       => $group->students_count,
                        'max_capacity'         => $group->max_capacity,
                        'is_active'            => $group->is_active,
                        'description'          => $group->description,
                        'occupancy_percentage' => $group->occupancy_percentage,
                        'can_add_students'     => $group->can_add_students,
                        'full_name'            => $group->full_name,
                        'created_at'           => $group->created_at->format('Y-m-d'),
                        'teachers'             => $group->teachers->map(fn($t) => [
                            'id'    => $t->id,
                            'name'  => $t->user?->name ?? 'غير محدد',
                            'email' => $t->user?->email ?? '',
                        ]),
                        'subjects_count'       => $group->subjects->count(),
                        'today_lectures'       => $todayCountsByGroup->get($group->id, 0),
                    ];
                })
                ->sortBy([['sort_order', 'asc'], ['section_number', 'asc']])
                ->values();

            $stats = [
                'total_groups'     => $groups->count(),
                'total_students'   => $groups->sum('students_count'),
                'full_groups'      => $groups->filter(fn($g) => $g['students_count'] >= $g['max_capacity'])->count(),
                'today_lectures'   => $groups->sum('today_lectures'),
                'available_groups' => $groups->where('can_add_students', true)->count(),
            ];

            return response()->json([
                'success' => true,
                'groups'  => $groups,
                'stats'   => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب البيانات: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * إنشاء مجموعة جديدة (محسن)
     */
    public function storeGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255|unique:groups,name',
            'grade_level'  => 'required|string|max:100',
            'section'      => 'nullable|string|max:10',
            'max_capacity' => 'required|integer|min:1|max:50',
            'description'  => 'nullable|string|max:500',
            'is_active'    => 'boolean',
        ], [
            'name.required'         => 'اسم المجموعة مطلوب',
            'name.unique'           => 'اسم المجموعة موجود مسبقاً',
            'grade_level.required'  => 'المرحلة الدراسية مطلوبة',
            'max_capacity.required' => 'الحد الأقصى للطلاب مطلوب',
            'max_capacity.min'      => 'الحد الأقصى يجب أن يكون على الأقل 1',
            'max_capacity.max'      => 'الحد الأقصى لا يمكن أن يتجاوز 50 طالب',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // تحديد رقم الشعبة التلقائي
            $latestSection = Group::where('grade_level', $request->grade_level)
                ->orderBy('section_number', 'desc')
                ->first();

            $sectionNumber = $latestSection ? $latestSection->section_number + 1 : 1;

            // تحديد حرف الشعبة إذا لم يتم تحديده
            if (! $request->section) {
                $request->merge([
                    'section' => $this->getSectionLetter($sectionNumber),
                ]);
            }

            $group = Group::create([
                'name'           => $request->name,
                'grade_level'    => $request->grade_level,
                'section'        => $request->section,
                'section_number' => $sectionNumber,
                'students_count' => 0,
                'max_capacity'   => $request->max_capacity,
                'is_active'      => $request->boolean('is_active', true),
                'description'    => $request->description,
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إنشاء المجموعة بنجاح',
                    'group'   => [
                        'id'             => $group->id,
                        'name'           => $group->name,
                        'grade_level'    => $group->grade_level,
                        'section'        => $group->section,
                        'students_count' => $group->students_count,
                        'max_capacity'   => $group->max_capacity,
                        'is_active'      => $group->is_active,
                        'description'    => $group->description,
                        'teachers'       => [],
                        'created_at'     => $group->created_at->format('Y-m-d'),
                    ],
                ]);
            }

            return back()->with('success', 'تم إنشاء المجموعة "' . $group->name . '" بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ في إنشاء المجموعة: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'حدث خطأ في إنشاء المجموعة: ' . $e->getMessage());
        }
    }

    /**
     * تحديث المجموعة (محسن)
     */
    public function updateGroup(Request $request, Group $group)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255|unique:groups,name,' . $group->id,
            'grade_level'  => 'required|string|max:100',
            'section'      => 'nullable|string|max:10',
            'max_capacity' => 'required|integer|min:' . $group->students_count . '|max:50',
            'description'  => 'nullable|string|max:500',
            'is_active'    => 'boolean',
        ], [
            'name.required'    => 'اسم المجموعة مطلوب',
            'name.unique'      => 'اسم المجموعة موجود مسبقاً',
            'max_capacity.min' => 'الحد الأقصى لا يمكن أن يكون أقل من عدد الطلاب الحالي (' . $group->students_count . ')',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات غير صحيحة',
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $group->update([
                'name'         => $request->name,
                'grade_level'  => $request->grade_level,
                'section'      => $request->section,
                'max_capacity' => $request->max_capacity,
                'is_active'    => $request->boolean('is_active'),
                'description'  => $request->description,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث المجموعة بنجاح',
                    'group'   => [
                        'id'             => $group->id,
                        'name'           => $group->name,
                        'grade_level'    => $group->grade_level,
                        'section'        => $group->section,
                        'students_count' => $group->students_count,
                        'max_capacity'   => $group->max_capacity,
                        'is_active'      => $group->is_active,
                        'description'    => $group->description,
                    ],
                ]);
            }

            return back()->with('success', 'تم تحديث المجموعة بنجاح');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ في تحديث المجموعة: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'حدث خطأ في تحديث المجموعة: ' . $e->getMessage());
        }
    }

    /**
     * حذف المجموعة (محسن مع فحوصات إضافية)
     */
    public function destroyGroup(Group $group)
    {
        try {
            // التحقق من وجود طلاب في المجموعة
            if ($group->students()->count() > 0) {
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لا يمكن حذف المجموعة لوجود طلاب مسجلين فيها',
                    ], 400);
                }
                return back()->with('error', 'لا يمكن حذف مجموعة تحتوي على طلاب');
            }

            // التحقق من وجود محاضرات
            if ($group->lectures()->count() > 0) {
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لا يمكن حذف المجموعة لوجود محاضرات مرتبطة بها',
                    ], 400);
                }
                return back()->with('error', 'لا يمكن حذف مجموعة تحتوي على محاضرات');
            }

            DB::beginTransaction();

            $groupName = $group->name;

            $group->subjects()->detach();
            $group->groupSubjects()->delete();

            // حذف المجموعة
            $group->delete();

            DB::commit();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم حذف المجموعة بنجاح',
                ]);
            }

            return back()->with('success', 'تم حذف المجموعة "' . $groupName . '" بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ في حذف المجموعة: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'حدث خطأ في حذف المجموعة: ' . $e->getMessage());
        }

    }

    public function addStudentToGroup(Request $request, Group $group)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $student = Student::findOrFail($request->student_id);

            // التحقق من أن الطالب ليس في مجموعة أخرى
            if ($student->hasGroup()) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب موجود بالفعل في مجموعة أخرى',
                ], 400);
            }

            DB::beginTransaction();

            // إضافة الطالب للمجموعة
            $resultGroup = $group->addStudent($student);

            DB::commit();

            $message = $resultGroup->id === $group->id ?
            'تم إضافة الطالب بنجاح' :
            'تم إنشاء شعبة جديدة وإضافة الطالب إليها';

            return response()->json([
                'success'  => true,
                'message'  => $message,
                'group_id' => $resultGroup->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إضافة الطالب: ' . $e->getMessage(),
            ], 500);
        }

    }

    public function getAvailableStudents(Request $request)
    {
        try {
            $groupId = $request->get('group_id');
            $group   = null;

            if ($groupId) {
                $group = Group::find($groupId);
            }

            $query = Student::whereNull('group_id')->with(['user', 'admission']);

            // فلترة حسب المرحلة الدراسية إذا تم تحديد مجموعة
            if ($group) {
                $query->where(function ($q) use ($group) {
                    // طلاب من نفس المرحلة من طلبات الانتساب
                    $q->whereHas('admission', function ($subQuery) use ($group) {
                        $subQuery->where('grade', $group->grade_level)
                            ->where('status', 'approved');
                    })
                    // أو طلاب بدون طلب انتساب (مضافين مباشرة)
                        ->orWhereDoesntHave('admission');
                });
            }

            $students = $query->get()->map(function ($student) {
                return [
                    'id'             => $student->id,
                    'name'           => $student->user->name ?? 'غير محدد',
                    'email'          => $student->user->email ?? '',
                    'birth_date'     => $student->birth_date?->format('Y-m-d'),
                    'age'            => $student->age,
                    'grade_level'    => $student->admission?->grade ?? 'مضاف مباشرة',
                    'source'         => $student->admission ? 'طلب انتساب' : 'إضافة مباشرة',
                    'admission_date' => $student->admission?->created_at?->format('Y-m-d'),
                    'display_name'   => $student->user->name . ' (' . ($student->admission?->grade ?? 'مضاف مباشرة') . ')',
                ];
            });

            return response()->json([
                'success'    => true,
                'students'   => $students,
                'group_info' => $group ? [
                    'id'          => $group->id,
                    'name'        => $group->name,
                    'grade_level' => $group->grade_level,
                ] : null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب الطلاب المتاحين: ' . $e->getMessage(),
            ], 500);
        }

    }


    /**
     * جلب طلاب المجموعة
     */
    public function getGroupStudents(Group $group)
    {
        try {
            $students = $group->students()->with('user')->get()->map(function ($student) {
                return [
                    'id'              => $student->id,
                    'name'            => $student->user->name ?? 'غير محدد',
                    'email'           => $student->user->email ?? '',
                    'birth_date'      => $student->birth_date?->format('Y-m-d'),
                    'age'             => $student->age,
                    'enrollment_date' => $student->created_at->format('Y-m-d'),
                    'group_name'      => $student->group_name,
                ];
            });

            return response()->json([
                'success'  => true,
                'students' => $students,
                'group'    => [
                    'id'             => $group->id,
                    'name'           => $group->name,
                    'students_count' => $students->count(),
                    'max_capacity'   => $group->max_capacity,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب بيانات الطلاب: ' . $e->getMessage(),
            ], 500);
        }

    }

    /**
     * نقل طالب إلى مجموعة أخرى
     */
    public function moveStudentToGroup(Request $request, Group $fromGroup, Student $student)
    {
        $validator = Validator::make($request->all(), [
            'target_group_id' => 'required|exists:groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $targetGroup = Group::findOrFail($request->target_group_id);

            // التحقق من إمكانية النقل
            if (! $targetGroup->can_add_students) {
                return response()->json([
                    'success' => false,
                    'message' => 'المجموعة المستهدفة ممتلئة أو غير نشطة',
                ], 400);
            }

            if ($student->group_id !== $fromGroup->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب غير موجود في هذه المجموعة',
                ], 400);
            }

            DB::beginTransaction();

            $success = $fromGroup->transferStudentTo($student, $targetGroup);

            if (! $success) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في نقل الطالب',
                ], 400);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم نقل الطالب بنجاح',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في نقل الطالب: ' . $e->getMessage(),
            ], 500);
        }

    }

    /**
     * إزالة طالب من المجموعة
     */
    public function removeStudentFromGroup(Group $group, Student $student)
    {
        try {
            if ($student->group_id !== $group->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب غير موجود في هذه المجموعة',
                ], 400);
            }

            DB::beginTransaction();

            $group->removeStudent($student);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إزالة الطالب من المجموعة بنجاح',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إزالة الطالب: ' . $e->getMessage(),
            ], 500);
        }

    }

    public function getGroupSubjectsForLectures(Request $request)
    {
        return $this->getGroupSubjects($request);
    }

    /**
     * جلب المجموعات المتاحة لنقل الطلاب
     */
    public function getAvailableGroupsForTransfer(Request $request)
    {
        try {
            $excludeGroupId = $request->get('exclude_group_id');

            $groups = Group::where('is_active', true)
                ->whereRaw('students_count < max_capacity')
                ->when($excludeGroupId, function ($query) use ($excludeGroupId) {
                    return $query->where('id', '!=', $excludeGroupId);
                })
                ->select('id', 'name', 'grade_level', 'students_count', 'max_capacity')
                ->get()
                ->map(function ($group) {
                    return [
                        'id'             => $group->id,
                        'name'           => $group->name,
                        'grade_level'    => $group->grade_level,
                        'students_count' => $group->students_count,
                        'max_capacity'   => $group->max_capacity,
                        'display_name'   => $group->name . ' (' . $group->students_count . '/' . $group->max_capacity . ')',
                    ];
                });

            return response()->json([
                'success' => true,
                'groups'  => $groups,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب المجموعات المتاحة: ' . $e->getMessage(),
            ], 500);
        }
    }

// إضافة هذه الدوال إلى AdminController

/**
 * جلب جميع المواد المتاحة
 */
    public function getAvailableSubjects(Request $request)
    {
        try {
            $gradeLevel = $request->get('grade_level');

            $query = Subject::active();

            if ($gradeLevel) {
                $query->where('grade_level', $gradeLevel);
            }

            $subjects = $query->get()->map(function ($subject) {
                return [
                    'id'           => $subject->id,
                    'name'         => $subject->name,
                    'grade_level'  => $subject->grade_level,
                    'description'  => $subject->description,
                    'display_name' => $subject->name . ' - ' . $subject->grade_level, // إضافة جديدة
                ];
            });

            return response()->json([
                'success'  => true,
                'subjects' => $subjects,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب المواد: ' . $e->getMessage(),
            ], 500);
        }

    }

/**
 * جلب مواد المجموعة مع تفاصيل المدرسين
 */
    public function getGroupSubjects(Request $request)
    {
        try {
            // Accept group ID from route model binding (/groups/{group}/subjects)
            // OR from query string (?group_id=X) used by the for-lectures endpoint
            $routeGroup = $request->route('group');
            if ($routeGroup instanceof Group) {
                $groupId = $routeGroup->id;
            } elseif (is_numeric($routeGroup)) {
                $groupId = (int) $routeGroup;
            } else {
                $groupId = (int) $request->get('group_id');
            }

            if (! $groupId) {
                return response()->json([
                    'success' => false,
                    'message' => 'معرف المجموعة مطلوب',
                ]);
            }

            // مواد المجموعة مع معلومات المدرس
            $groupSubjects = DB::table('group_subjects')
                ->join('subjects', 'group_subjects.subject_id', '=', 'subjects.id')
                ->leftJoin('teachers', 'group_subjects.teacher_id', '=', 'teachers.id')
                ->leftJoin('users', 'teachers.user_id', '=', 'users.id')
                ->where('group_subjects.group_id', $groupId)
                ->select(
                    'group_subjects.id as group_subject_id',
                    'group_subjects.is_active',
                    'group_subjects.teacher_id',
                    'subjects.id as subject_id',
                    'subjects.name as subject_name',
                    'subjects.description',
                    DB::raw('users.name as teacher_name')
                )
                ->get()
                ->map(fn($row) => [
                    'id'           => $row->group_subject_id,
                    'subject_id'   => $row->subject_id,
                    'subject_name' => $row->subject_name,
                    'description'  => $row->description,
                    'teacher_id'   => $row->teacher_id,
                    'teacher_name' => $row->teacher_name ?? 'غير محدد',
                    'is_active'    => (bool) $row->is_active,
                ]);

            // المواد غير المضافة للمجموعة بعد
            $assignedIds        = DB::table('group_subjects')->where('group_id', $groupId)->pluck('subject_id');
            $availableSubjects  = Subject::whereNotIn('id', $assignedIds)->get()->map(fn($s) => [
                'id'           => $s->id,
                'name'         => $s->name,
                'display_name' => $s->name,
            ]);

            // جميع المدرسين النشطين
            $availableTeachers = Teacher::with('user')
                ->whereHas('user', fn($q) => $q->where('is_active', true))
                ->get()
                ->map(fn($t) => [
                    'id'   => $t->id,
                    'name' => $t->user?->name ?? 'غير محدد',
                ]);

            return response()->json([
                'success'            => true,
                // للمحاضرات (القديم)
                'subjects'           => $groupSubjects->map(fn($s) => [
                    'id'           => $s['subject_id'],
                    'name'         => $s['subject_name'],
                    'display_name' => $s['subject_name'],
                ]),
                // للمجموعات modal
                'group_subjects'     => $groupSubjects,
                'available_subjects' => $availableSubjects,
                'available_teachers' => $availableTeachers,
                'message'            => "تم تحميل {$groupSubjects->count()} مادة للمجموعة",
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading group subjects', [
                'group_id' => $request->get('group_id'),
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }
    }

/**
 * إضافة مادة للمجموعة
 */
    public function addSubjectToGroup(Request $request, Group $group)
    {
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'is_active'  => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // التحقق من أن المادة غير مرتبطة بالمجموعة مسبقاً
            $exists = GroupSubject::where('group_id', $group->id)
                ->where('subject_id', $request->subject_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'المادة مرتبطة بالمجموعة مسبقاً',
                ], 400);
            }

            DB::beginTransaction();

            $groupSubject = GroupSubject::create([
                'group_id'   => $group->id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $request->teacher_id,
                'schedule'   => null, // بدون جدولة
                'is_active'  => $request->boolean('is_active', true),
            ]);

            DB::commit();

            // إرجاع البيانات المحدثة
            $groupSubject->load(['subject', 'teacher.user']);

            return response()->json([
                'success'       => true,
                'message'       => 'تم إضافة المادة للمجموعة بنجاح',
                'group_subject' => [
                    'id'           => $groupSubject->id,
                    'subject_id'   => $groupSubject->subject_id,
                    'subject_name' => $groupSubject->subject_name,
                    'teacher_id'   => $groupSubject->teacher_id,
                    'teacher_name' => $groupSubject->teacher_name,
                    'is_active'    => $groupSubject->is_active,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إضافة المادة: ' . $e->getMessage(),
            ], 500);
        }

    }

/**
 * تحديث مادة في المجموعة
 */
    public function updateGroupSubject(Request $request, Group $group, GroupSubject $groupSubject)
    {
        $validator = Validator::make($request->all(), [
            'teacher_id' => 'nullable|exists:teachers,id',
            'is_active'  => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // التحقق من أن GroupSubject ينتمي للمجموعة الصحيحة
            if ($groupSubject->group_id !== $group->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'المادة غير مرتبطة بهذه المجموعة',
                ], 400);
            }

            DB::beginTransaction();

            $groupSubject->update([
                'teacher_id' => $request->teacher_id,
                'is_active'  => $request->boolean('is_active', $groupSubject->is_active),
            ]);

            DB::commit();

            $groupSubject->load(['subject', 'teacher.user']);

            return response()->json([
                'success'       => true,
                'message'       => 'تم تحديث المادة بنجاح',
                'group_subject' => [
                    'id'           => $groupSubject->id,
                    'subject_id'   => $groupSubject->subject_id,
                    'subject_name' => $groupSubject->subject_name,
                    'teacher_id'   => $groupSubject->teacher_id,
                    'teacher_name' => $groupSubject->teacher_name,
                    'is_active'    => $groupSubject->is_active,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحديث المادة: ' . $e->getMessage(),
            ], 500);
        }

    }

/**
 * إزالة مادة من المجموعة
 */
    public function removeSubjectFromGroup(Group $group, GroupSubject $groupSubject)
    {
        try {
            // التحقق من أن GroupSubject ينتمي للمجموعة الصحيحة
            if ($groupSubject->group_id !== $group->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'المادة غير مرتبطة بهذه المجموعة',
                ], 400);
            }

            DB::beginTransaction();

            $subjectName = $groupSubject->subject_name;
            $groupSubject->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "تم إزالة مادة {$subjectName} من المجموعة بنجاح",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إزالة المادة: ' . $e->getMessage(),
            ], 500);
        }

    }

/**
 * نسخ مواد من مجموعة لأخرى
 */
    public function copySubjectsBetweenGroups(Request $request, Group $sourceGroup)
    {
        $validator = Validator::make($request->all(), [
            'target_group_id' => 'required|exists:groups,id',
            'subject_ids'     => 'required|array',
            'subject_ids.*'   => 'exists:subjects,id',
            'copy_teachers'   => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $targetGroup = Group::findOrFail($request->target_group_id);

            DB::beginTransaction();

            $copiedCount  = 0;
            $skippedCount = 0;

            foreach ($request->subject_ids as $subjectId) {
                // التحقق من عدم وجود المادة في المجموعة المستهدفة
                $exists = GroupSubject::where('group_id', $targetGroup->id)
                    ->where('subject_id', $subjectId)
                    ->exists();

                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                // جلب بيانات المادة من المجموعة المصدر
                $sourceGroupSubject = GroupSubject::where('group_id', $sourceGroup->id)
                    ->where('subject_id', $subjectId)
                    ->first();

                if (! $sourceGroupSubject) {
                    continue;
                }

                // إنشاء المادة في المجموعة المستهدفة
                GroupSubject::create([
                    'group_id'   => $targetGroup->id,
                    'subject_id' => $subjectId,
                    'teacher_id' => $request->boolean('copy_teachers') ? $sourceGroupSubject->teacher_id : null,
                    'schedule'   => null, // بدون جدولة
                    'is_active'  => true,
                ]);

                $copiedCount++;
            }

            DB::commit();

            $message = "تم نسخ {$copiedCount} مادة بنجاح";
            if ($skippedCount > 0) {
                $message .= " (تم تخطي {$skippedCount} مادة موجودة مسبقاً)";
            }

            return response()->json([
                'success'       => true,
                'message'       => $message,
                'copied_count'  => $copiedCount,
                'skipped_count' => $skippedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في نسخ المواد: ' . $e->getMessage(),
            ], 500);
        }

    }


    public function approveAdmission(Request $request, Admission $admission)
    {
        $request->validate([
            'group_id' => 'nullable|exists:groups,id',
        ]);

        try {
            DB::beginTransaction();

            if ($request->group_id) {
                // الآدمن اختار مجموعة محددة
                $group = Group::findOrFail($request->group_id);
            } else {
                // اختيار تلقائي للمجموعة المناسبة
                $group = Group::getAvailableGroups($admission->grade_level)->first();

                if (! $group) {
                    // إنشاء شعبة جديدة
                    $group = $this->createNewSectionForGrade($admission->grade_level);
                }
            }

            // تحويل الطلب إلى طالب
            $student = $admission->convertToStudent();

            // إضافة الطالب للمجموعة
            $group->addStudent($student);

            DB::commit();

            return back()->with('success', 'تم قبول طلب الانتساب وإضافة الطالب للمجموعة');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطأ في معالجة الطلب: ' . $e->getMessage());
        }

    }

    private function createNewSectionForGrade(string $gradeLevel)
    {
        // منطق إنشاء شعبة جديدة
        $latestSection = Group::where('grade_level', $gradeLevel)
            ->orderBy('section_number', 'desc')
            ->first();

        $newSectionNumber = $latestSection ? $latestSection->section_number + 1 : 1;
        $sectionLetter    = $this->getSectionLetter($newSectionNumber);

        return Group::create([
            'name'           => $gradeLevel . ' - الشعبة ' . $sectionLetter,
            'grade_level'    => $gradeLevel,
            'section'        => $sectionLetter,
            'section_number' => $newSectionNumber,
            'students_count' => 0,
            'max_capacity'   => 20,
            'is_active'      => true,
            'description'    => 'شعبة جديدة لطلاب ' . $gradeLevel,
        ]);
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

        return back()->with('success', 'تم رفض طلب الانتساب');
    }

    public function staff()
    {
        return view('admin.staff');
    }

    public function getTeachersData()
    {
        $teachers = Teacher::with(['user', 'assignedGroups'])->get()->map(fn($t) => [
            'id'              => $t->id,
            'name'            => $t->user->name ?? '—',
            'national_id'     => $t->user->national_id ?? '—',
            'birth_date'      => $t->user->birth_date?->format('Y-m-d') ?? '',
            'specializations' => $t->specializations ?? [],
            'account_type'    => $t->account_type ?? '',
            'account_number'  => $t->account_number ?? '',
            'hire_date'       => $t->hire_date?->format('Y-m-d') ?? '',
            'salary'          => $t->salary ? (float) $t->salary : null,
            'groups'          => $t->assignedGroups->map(fn($g) => ['id' => $g->id, 'name' => $g->name])->values(),
            'is_active'       => $t->user->is_active ?? true,
        ]);

        return response()->json(['success' => true, 'teachers' => $teachers]);
    }

    public function getGroupsForStaff()
    {
        $groups = Group::select('id', 'name', 'grade_level')->where('is_active', true)->orderBy('name')->get();
        return response()->json(['success' => true, 'groups' => $groups]);
    }

    public function storeTeacher(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'national_id'       => 'required|string|size:9|unique:users,national_id',
            'birth_date'        => 'required|date',
            'specializations'   => 'required|array|min:1',
            'specializations.*' => 'string',
            'groups'            => 'nullable|array',
            'groups.*'          => 'exists:groups,id',
            'account_type'      => 'nullable|string|in:bank_of_palestine,pal_pay,jawwal_pay',
            'account_number'    => 'nullable|string|max:50',
            'hire_date'         => 'nullable|date',
            'salary'            => 'nullable|numeric|min:0',
        ]);

        $birthDate = Carbon::parse($validated['birth_date']);
        $password  = $birthDate->format('dmY');

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['national_id'] . '@teacher.local',
            'national_id'       => $validated['national_id'],
            'birth_date'        => $validated['birth_date'],
            'password'          => Hash::make($password),
            'role'              => 'teacher',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $teacher = Teacher::create([
            'user_id'         => $user->id,
            'specializations' => $validated['specializations'],
            'account_type'    => $validated['account_type'] ?? null,
            'account_number'  => $validated['account_number'] ?? null,
            'hire_date'       => $validated['hire_date'] ?? null,
            'salary'          => $validated['salary'] ?? null,
        ]);

        if (! empty($validated['groups'])) {
            $teacher->assignedGroups()->sync($validated['groups']);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء حساب المدرس بنجاح',
            'teacher' => [
                'id'              => $teacher->id,
                'name'            => $user->name,
                'national_id'     => $user->national_id,
                'birth_date'      => $user->birth_date->format('Y-m-d'),
                'specializations' => $teacher->specializations,
                'account_type'    => $teacher->account_type,
                'account_number'  => $teacher->account_number,
                'hire_date'       => $teacher->hire_date?->format('Y-m-d') ?? '',
                'salary'          => $teacher->salary ? (float) $teacher->salary : null,
                'groups'          => $teacher->assignedGroups()->get()->map(fn($g) => ['id' => $g->id, 'name' => $g->name])->values(),
                'is_active'       => true,
            ],
        ], 201);
    }

    public function updateTeacher(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'national_id'       => ['required', 'string', 'size:9', Rule::unique('users', 'national_id')->ignore($teacher->user_id)],
            'birth_date'        => 'required|date',
            'specializations'   => 'required|array|min:1',
            'specializations.*' => 'string',
            'groups'            => 'nullable|array',
            'groups.*'          => 'exists:groups,id',
            'account_type'      => 'nullable|string|in:bank_of_palestine,pal_pay,jawwal_pay',
            'account_number'    => 'nullable|string|max:50',
            'hire_date'         => 'nullable|date',
            'salary'            => 'nullable|numeric|min:0',
        ]);

        $birthDate = Carbon::parse($validated['birth_date']);
        $password  = $birthDate->format('dmY');

        $teacher->user->update([
            'name'        => $validated['name'],
            'email'       => $validated['national_id'] . '@teacher.local',
            'national_id' => $validated['national_id'],
            'birth_date'  => $validated['birth_date'],
            'password'    => Hash::make($password),
        ]);

        $teacher->update([
            'specializations' => $validated['specializations'],
            'account_type'    => $validated['account_type'] ?? null,
            'account_number'  => $validated['account_number'] ?? null,
            'hire_date'       => $validated['hire_date'] ?? null,
            'salary'          => $validated['salary'] ?? null,
        ]);

        $teacher->assignedGroups()->sync($validated['groups'] ?? []);

        return response()->json(['success' => true, 'message' => 'تم تحديث بيانات المدرس بنجاح']);
    }

    public function destroyTeacher(Teacher $teacher)
    {
        $teacher->user()->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف حساب المدرس']);
    }

    public function getEmployeesData()
    {
        $employees = Employee::with('user')->get()->map(fn($e) => [
            'id'             => $e->id,
            'name'           => $e->user->name ?? '—',
            'national_id'    => $e->user->national_id ?? '—',
            'birth_date'     => $e->user->birth_date?->format('Y-m-d') ?? '',
            'job_title'      => $e->job_title,
            'hire_date'      => $e->hire_date?->format('Y-m-d') ?? '',
            'salary'         => $e->salary ? (float) $e->salary : null,
            'account_type'   => $e->account_type ?? '',
            'account_number' => $e->account_number ?? '',
            'is_active'      => $e->user->is_active ?? true,
        ]);

        return response()->json(['success' => true, 'employees' => $employees]);
    }

    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'national_id'    => 'required|string|size:9|unique:users,national_id',
            'birth_date'     => 'required|date',
            'job_title'      => 'required|string|max:100',
            'hire_date'      => 'nullable|date',
            'salary'         => 'nullable|numeric|min:0',
            'account_type'   => 'nullable|string|in:bank_of_palestine,pal_pay,jawwal_pay',
            'account_number' => 'nullable|string|max:50',
        ]);

        $password = Carbon::parse($validated['birth_date'])->format('dmY');

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['national_id'] . '@staff.local',
            'national_id'       => $validated['national_id'],
            'birth_date'        => $validated['birth_date'],
            'password'          => Hash::make($password),
            'role'              => 'employee',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $employee = Employee::create([
            'user_id'        => $user->id,
            'job_title'      => $validated['job_title'],
            'hire_date'      => $validated['hire_date'] ?? null,
            'salary'         => $validated['salary'] ?? null,
            'account_type'   => $validated['account_type'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'تم إنشاء حساب الموظف بنجاح',
            'employee' => [
                'id'             => $employee->id,
                'name'           => $user->name,
                'national_id'    => $user->national_id,
                'birth_date'     => $user->birth_date->format('Y-m-d'),
                'job_title'      => $employee->job_title,
                'hire_date'      => $employee->hire_date?->format('Y-m-d') ?? '',
                'salary'         => $employee->salary ? (float) $employee->salary : null,
                'account_type'   => $employee->account_type,
                'account_number' => $employee->account_number,
                'is_active'      => true,
            ],
        ], 201);
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'national_id'    => ['required', 'string', 'size:9', Rule::unique('users', 'national_id')->ignore($employee->user_id)],
            'birth_date'     => 'required|date',
            'job_title'      => 'required|string|max:100',
            'hire_date'      => 'nullable|date',
            'salary'         => 'nullable|numeric|min:0',
            'account_type'   => 'nullable|string|in:bank_of_palestine,pal_pay,jawwal_pay',
            'account_number' => 'nullable|string|max:50',
        ]);

        $password = Carbon::parse($validated['birth_date'])->format('dmY');

        $employee->user->update([
            'name'        => $validated['name'],
            'email'       => $validated['national_id'] . '@staff.local',
            'national_id' => $validated['national_id'],
            'birth_date'  => $validated['birth_date'],
            'password'    => Hash::make($password),
        ]);

        $employee->update([
            'job_title'      => $validated['job_title'],
            'hire_date'      => $validated['hire_date'] ?? null,
            'salary'         => $validated['salary'] ?? null,
            'account_type'   => $validated['account_type'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'تم تحديث بيانات الموظف بنجاح']);
    }

    public function destroyEmployee(Employee $employee)
    {
        $employee->user()->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف حساب الموظف']);
    }

    public function parentMessages()
    {
        $messages = ParentMessage::with(['parent', 'student.user', 'student.group'])
            ->latest()
            ->paginate(20);

        $unreadCount = ParentMessage::unread()->count();

        return view('admin.messages', compact('messages', 'unreadCount'));
    }

    public function markMessageRead($id)
    {
        $message = ParentMessage::findOrFail($id);
        $message->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function settings()
    {
        $academySettings = [
            'academy_name'           => Setting::get('academy_name', 'الأكاديمية التعليمية'),
            'academy_email'          => Setting::get('academy_email', 'info@academy.edu'),
            'academy_phone'          => Setting::get('academy_phone', ''),
            'academy_address'        => Setting::get('academy_address', ''),
            'monthly_fee'            => Setting::get('monthly_fee', 0),
            'working_hours'          => Setting::get('working_hours', ''),
            'academic_year_end_date' => Setting::get('academic_year_end_date'),
        ];

        $systemSettings = [
            'auto_notifications'  => (bool) Setting::get('auto_notifications', 1),
            'email_notifications' => (bool) Setting::get('email_notifications', 1),
            'sms_notifications'   => (bool) Setting::get('sms_notifications', 0),
            'attendance_reminder' => (bool) Setting::get('attendance_reminder', 1),
            'payment_reminder'    => (bool) Setting::get('payment_reminder', 1),
        ];

        return view('admin.settings', compact('academySettings', 'systemSettings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'academy_name'           => 'required|string|max:255',
            'academy_email'          => 'required|email',
            'academy_phone'          => 'required|string|max:20',
            'monthly_fee'            => 'required|numeric|min:0',
            'academic_year_end_date' => 'nullable|date',
        ]);

        $keys = [
            'academy_name', 'academy_email', 'academy_phone',
            'academy_address', 'monthly_fee', 'working_hours',
            'academic_year_end_date',
            'auto_notifications', 'attendance_reminder', 'payment_reminder',
        ];
        foreach ($keys as $key) {
            Setting::set($key, $request->input($key));
        }

        return back()->with('success', 'تم حفظ الإعدادات بنجاح');
    }

    public function clearData()
    {
        try {
            DB::transaction(function () {
                Attendance::query()->delete();
                Lecture::query()->delete();
                Payment::query()->delete();
                Student::query()->delete();
                Teacher::query()->delete();
                Admission::query()->delete();
                Group::query()->delete();
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

    public function resetSystem()
    {
        try {
            DB::transaction(function () {
                Attendance::query()->delete();
                Lecture::query()->delete();
                Payment::query()->delete();
                Student::query()->delete();
                Teacher::query()->delete();
                Admission::query()->delete();
                Group::query()->delete();

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

    public function attendance()
    {
        $groups = Group::select('id', 'name')->orderBy('name')->get();
        return view('admin.attendance', compact('groups'));
    }

    public function getAttendanceData(Request $request)
    {
        try {
            $month   = $request->get('month', now()->format('Y-m'));
            $groupId = $request->get('group_id');

            // Lectures for this month that are not cancelled and already past/today
            $lectureIds = Lecture::whereYear('date', substr($month, 0, 4))->whereMonth('date', (int) substr($month, 5, 2))
                ->whereNotIn('status', ['cancelled'])
                ->where('date', '<=', today())
                ->pluck('id');

            $totalLectures = $lectureIds->count();

            // Students — optionally filtered by group
            $studentsQuery = Student::with('user')
                ->when($groupId, fn($q) => $q->where('group_id', $groupId))
                ->whereNotNull('group_id');

            $students = $studentsQuery->get();

            // Bulk attendance: one query for all students/lectures
            $allAttendance = $totalLectures > 0
                ? Attendance::whereIn('lecture_id', $lectureIds)
                    ->whereIn('student_id', $students->pluck('id'))
                    ->selectRaw('student_id, status, count(*) as cnt')
                    ->groupBy('student_id', 'status')
                    ->get()
                    ->groupBy('student_id')
                : collect();

            $studentsData = $students->map(function ($student) use ($totalLectures, $allAttendance) {
                $records      = $allAttendance->get($student->id, collect());
                $presentCount = $records->firstWhere('status', 'present')?->cnt ?? 0;
                $lateCount    = $records->firstWhere('status', 'late')?->cnt    ?? 0;
                $absentCount  = $records->firstWhere('status', 'absent')?->cnt  ?? 0;
                $positiveRate = $totalLectures > 0
                    ? round(($presentCount + $lateCount) / $totalLectures * 100, 1)
                    : 0;

                return [
                    'id'             => $student->id,
                    'name'           => $student->user->name ?? 'غير محدد',
                    'group_id'       => $student->group_id,
                    'total_lectures' => $totalLectures,
                    'present'        => $presentCount,
                    'late'           => $lateCount,
                    'absent'         => $absentCount,
                    'not_recorded'   => max(0, $totalLectures - $presentCount - $lateCount - $absentCount),
                    'rate'           => $positiveRate,
                    'low_attendance' => $positiveRate < 75,
                ];
            });

            $totalStudents = $studentsData->count();
            $avgRate       = $totalStudents > 0
                ? round($studentsData->avg('rate'), 1)
                : 0;

            return response()->json([
                'success'  => true,
                'summary'  => [
                    'total_students'      => $totalStudents,
                    'total_lectures'      => $totalLectures,
                    'avg_rate'            => $avgRate,
                    'low_attendance_count' => $studentsData->where('low_attendance', true)->count(),
                    'present_total'       => $studentsData->sum('present'),
                    'absent_total'        => $studentsData->sum('absent'),
                    'late_total'          => $studentsData->sum('late'),
                ],
                'students' => $studentsData->values(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب بيانات الحضور: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getStudentAttendanceDetail(Request $request, $studentId)
    {
        try {
            $month   = $request->get('month', now()->format('Y-m'));
            $student = Student::with('user')->findOrFail($studentId);

            $lectures = Lecture::whereYear('date', substr($month, 0, 4))->whereMonth('date', (int) substr($month, 5, 2))
                ->where('group_id', $student->group_id)
                ->whereNotIn('status', ['cancelled'])
                ->orderBy('date')
                ->orderBy('start_time')
                ->get();

            $attendanceByLecture = Attendance::where('student_id', $studentId)
                ->whereIn('lecture_id', $lectures->pluck('id'))
                ->pluck('status', 'lecture_id');

            $records = $lectures->map(fn($lecture) => [
                'lecture_id'   => $lecture->id,
                'title'        => $lecture->title,
                'date'         => $lecture->date->format('Y-m-d'),
                'start_time'   => is_string($lecture->start_time)
                    ? $lecture->start_time
                    : $lecture->start_time?->format('H:i'),
                'status'       => $attendanceByLecture->get($lecture->id, 'not_recorded'),
                'is_past'      => $lecture->date->lte(today()),
            ]);

            return response()->json([
                'success' => true,
                'student' => [
                    'id'   => $student->id,
                    'name' => $student->user->name ?? 'غير محدد',
                ],
                'records' => $records,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب تفاصيل الطالب: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getLectureAttendanceStudents(Lecture $lecture)
    {
        $students = $lecture->group?->students()->with('user')->get() ?? collect();

        $existing = Attendance::where('lecture_id', $lecture->id)
            ->pluck('status', 'student_id');

        return response()->json([
            'success'           => true,
            'lecture'           => [
                'id'         => $lecture->id,
                'title'      => $lecture->title,
                'date'       => $lecture->date->format('Y-m-d'),
                'group_name' => $lecture->group?->name ?? '—',
            ],
            'students'          => $students->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->user?->name ?? 'غير محدد',
            ]),
            'existing_statuses' => $existing,
        ]);
    }

    public function storeLectureAttendance(Request $request, Lecture $lecture)
    {
        if ($lecture->date->gt(today())) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تسجيل حضور لمحاضرة مستقبلية',
            ], 400);
        }

        $validated = $request->validate([
            'attendance'   => 'required|array',
            'attendance.*' => 'required|in:present,absent,late',
        ]);

        foreach ($validated['attendance'] as $studentId => $status) {
            Attendance::updateOrCreate(
                ['student_id' => $studentId, 'lecture_id' => $lecture->id],
                ['status' => $status]
            );
        }

        $count = count($validated['attendance']);

        return response()->json([
            'success' => true,
            'message' => "تم حفظ الحضور بنجاح لـ {$count} طالب",
        ]);
    }

    public function notifyStudentLowAttendance($studentId)
    {
        try {
            $student = Student::with(['user', 'parent'])->findOrFail($studentId);

            if (! $student->parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد ولي أمر مرتبط بهذا الطالب',
                ], 400);
            }

            $rate = $student->getAttendancePercentage();

            NotificationService::notifyLowAttendanceForStudent($student, $rate);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال التنبيه لولي أمر ' . ($student->user->name ?? ''),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إرسال التنبيه: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function payments()
    {
        $groups = Group::select('id', 'name')->where('is_active', true)->orderBy('name')->get();
        return view('admin.payments', compact('groups'));
    }

    public function getDuePayments(Request $request)
    {
        $month   = $request->get('month', now()->format('Y-m'));
        $groupId = $request->get('group_id');
        $status  = $request->get('status', 'unpaid');
        $type    = $request->get('type', 'monthly');

        $payments = Payment::with(['student.user', 'student.admission'])
            ->where('month', $month)
            ->when($type && $type !== 'all',   fn($q) => $q->where('type', $type))
            ->when($status && $status !== 'all', fn($q) => $q->where('status', $status))
            ->when($groupId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('group_id', $groupId)))
            ->get()
            ->map(fn($p) => $this->formatPayment($p));

        // Stats cover all monthly payments for the selected month/group (ignore status/type filter)
        $allMonthly = Payment::where('month', $month)
            ->where('type', 'monthly')
            ->when($groupId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('group_id', $groupId)))
            ->get();

        $stats = [
            'total_expected' => (float) $allMonthly->sum('amount'),
            'total_paid'     => (float) $allMonthly->where('status', 'paid')->sum('amount'),
            'remaining'      => (float) $allMonthly->whereIn('status', ['unpaid', 'pending'])->sum('amount'),
            'paid_count'     => $allMonthly->where('status', 'paid')->count(),
            'unpaid_count'   => $allMonthly->where('status', 'unpaid')->count(),
            'overdue_count'  => $allMonthly->where('status', 'unpaid')
                ->filter(fn($p) => $p->is_overdue)->count(),
        ];

        return response()->json(['success' => true, 'payments' => $payments, 'stats' => $stats]);
    }

    private function formatPayment(Payment $payment): array
    {
        return [
            'id'             => $payment->id,
            'student_id'     => $payment->student_id,
            'student_name'   => $payment->student?->user?->name ?? 'غير محدد',
            'parent_name'    => $payment->student?->admission?->parent_name ?? '',
            'phone'          => $payment->student?->admission?->father_phone
                ?? $payment->student?->admission?->phone ?? '',
            'group_id'       => $payment->student?->group_id,
            'amount'         => $payment->amount,
            'month'          => $payment->month,
            'type'           => $payment->type ?? 'monthly',
            'status'         => $payment->status,
            'due_date'       => $payment->due_date?->format('Y-m-d'),
            'paid_date'      => $payment->paid_date?->format('Y-m-d'),
            'payment_method' => $payment->payment_method,
            'account_name'   => $payment->account_name,
            'notes'          => $payment->notes,
            'is_overdue'     => $payment->is_overdue,
        ];
    }

    public function recordPayment(Request $request, Payment $payment)
    {
        if ($payment->status === 'paid') {
            return response()->json(['success' => false, 'message' => 'هذه الدفعة مسجلة مدفوعة مسبقاً'], 400);
        }

        $request->validate([
            'payment_method' => 'required|in:cash,bank_transfer,check',
            'account_name'   => 'required|string|max:255',
            'paid_date'      => 'nullable|date',
            'notes'          => 'nullable|string|max:500',
        ], [
            'payment_method.required' => 'يرجى اختيار طريقة الدفع',
            'account_name.required'   => 'يرجى إدخال اسم الحساب (من دفع المبلغ)',
        ]);

        $payment->update([
            'status'         => 'paid',
            'payment_method' => $request->payment_method,
            'account_name'   => $request->account_name,
            'paid_date'      => $request->paid_date ?? today()->toDateString(),
            'notes'          => $request->notes,
        ]);

        $payment->load(['student.user', 'student.parent']);
        NotificationService::notifyPaymentReceived($payment);

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدفعة بنجاح وإرسال إشعار لولي الأمر',
            'payment' => $this->formatPayment($payment->fresh()->load(['student.user', 'student.admission'])),
        ]);
    }

    public function updatePaymentData(Request $request, Payment $payment)
    {
        $request->validate([
            'amount'   => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'notes'    => 'nullable|string|max:500',
        ]);

        $data = [];
        if ($request->filled('amount'))   $data['amount']   = $request->amount;
        if ($request->filled('due_date')) $data['due_date'] = $request->due_date;
        if ($request->has('notes'))       $data['notes']    = $request->notes;

        if (!empty($data)) {
            $payment->update($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات الدفعة بنجاح',
            'payment' => $this->formatPayment($payment->fresh()->load(['student.user', 'student.admission'])),
        ]);
    }

    public function destroyPayment(Payment $payment)
    {
        if ($payment->status === 'paid') {
            return response()->json(['success' => false, 'message' => 'لا يمكن حذف دفعة مسجلة كمدفوعة'], 400);
        }

        $payment->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف الدفعة بنجاح']);
    }

    public function addCustomPayment(Request $request)
    {
        $request->validate([
            'student_id'     => 'required|exists:students,id',
            'amount'         => 'required|numeric|min:0.01',
            'type'           => 'required|in:monthly,admission_fee,educational_bundle',
            'month'          => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'due_date'       => 'nullable|date',
            'status'         => 'required|in:paid,unpaid,pending',
            'payment_method' => 'nullable|in:cash,bank_transfer,check',
            'account_name'   => 'nullable|string|max:255',
            'notes'          => 'nullable|string|max:500',
        ], [
            'student_id.required' => 'يرجى اختيار طالب',
            'amount.required'     => 'يرجى إدخال المبلغ',
            'amount.min'          => 'يجب أن يكون المبلغ أكبر من صفر',
            'month.required'      => 'يرجى تحديد الشهر',
        ]);

        $exists = Payment::where('student_id', $request->student_id)
            ->where('month', $request->month)
            ->where('type', $request->type)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'يوجد دفعة من نفس النوع لهذا الطالب في هذا الشهر',
            ], 400);
        }

        $data = [
            'student_id' => $request->student_id,
            'amount'     => $request->amount,
            'type'       => $request->type,
            'month'      => $request->month,
            'due_date'   => $request->due_date,
            'status'     => $request->status,
            'notes'      => $request->notes,
        ];

        if ($request->status === 'paid') {
            $data['payment_method'] = $request->payment_method;
            $data['account_name']   = $request->account_name;
            $data['paid_date']      = today()->toDateString();
        }

        $payment = Payment::create($data);
        $payment->load(['student.user', 'student.admission', 'student.parent']);

        if ($request->status === 'paid') {
            NotificationService::notifyPaymentReceived($payment);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة الدفعة بنجاح',
            'payment' => $this->formatPayment($payment),
        ], 201);
    }

    public function searchStudentsForPayment(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (mb_strlen($q) < 4) {
            return response()->json(['success' => true, 'students' => []]);
        }

        $students = Student::with(['user', 'admission'])
            ->whereHas('user', fn($query) => $query
                ->where('name', 'LIKE', "%{$q}%")
                ->orWhere('national_id', 'LIKE', "%{$q}%")
            )
            ->limit(10)
            ->get()
            ->map(fn($s) => [
                'id'          => $s->id,
                'name'        => $s->user?->name ?? 'غير محدد',
                'national_id' => $s->user?->national_id ?? '',
                'group_id'    => $s->group_id,
                'monthly_fee' => $s->admission?->monthly_fee ?? 0,
            ]);

        return response()->json(['success' => true, 'students' => $students]);
    }

    public function getGroupsForPayments()
    {
        $groups = Group::select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'groups' => $groups]);
    }

    public function getPaymentHistory(Request $request)
    {
        $studentId = $request->get('student_id');
        $monthFrom = $request->get('month_from');
        $monthTo   = $request->get('month_to');
        $status    = $request->get('status', 'all');
        $type      = $request->get('type', 'all');

        $payments = Payment::with(['student.user', 'student.admission'])
            ->when($studentId, fn($q) => $q->where('student_id', $studentId))
            ->when($monthFrom, fn($q) => $q->where('month', '>=', $monthFrom))
            ->when($monthTo,   fn($q) => $q->where('month', '<=', $monthTo))
            ->when($status && $status !== 'all', fn($q) => $q->where('status', $status))
            ->when($type   && $type   !== 'all', fn($q) => $q->where('type', $type))
            ->orderBy('month', 'desc')
            ->orderBy('id', 'desc')
            ->limit(500)
            ->get();

        $mapped = $payments->map(fn($p) => $this->formatPayment($p));

        $stats = [
            'total_count'   => $payments->count(),
            'total_amount'  => (float) $payments->sum('amount'),
            'paid_amount'   => (float) $payments->where('status', 'paid')->sum('amount'),
            'unpaid_amount' => (float) $payments->whereIn('status', ['unpaid', 'pending'])->sum('amount'),
            'paid_count'    => $payments->where('status', 'paid')->count(),
            'unpaid_count'  => $payments->whereIn('status', ['unpaid', 'pending'])->count(),
        ];

        return response()->json(['success' => true, 'payments' => $mapped, 'stats' => $stats]);
    }

    public function exportPayments(Request $request)
    {
        $studentId = $request->get('student_id');
        $monthFrom = $request->get('month_from');
        $monthTo   = $request->get('month_to');
        $status    = $request->get('status', 'all');
        $type      = $request->get('type', 'all');

        $payments = Payment::with(['student.user', 'student.admission'])
            ->when($studentId, fn($q) => $q->where('student_id', $studentId))
            ->when($monthFrom, fn($q) => $q->where('month', '>=', $monthFrom))
            ->when($monthTo,   fn($q) => $q->where('month', '<=', $monthTo))
            ->when($status && $status !== 'all', fn($q) => $q->where('status', $status))
            ->when($type   && $type   !== 'all', fn($q) => $q->where('type', $type))
            ->orderBy('month', 'desc')
            ->get();

        $filename = 'payments_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($payments) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for Excel Arabic

            fputcsv($handle, ['الطالب', 'ولي الأمر', 'الهاتف', 'الشهر', 'النوع', 'المبلغ (ش.ج)', 'الحالة', 'تاريخ الاستحقاق', 'تاريخ الدفع', 'طريقة الدفع', 'اسم الحساب', 'ملاحظات']);

            $typeMap   = ['monthly' => 'شهري', 'admission_fee' => 'رسوم انتساب', 'educational_bundle' => 'حزمة تعليمية'];
            $statusMap = ['paid' => 'مدفوع', 'unpaid' => 'غير مدفوع', 'pending' => 'في الانتظار'];

            foreach ($payments as $p) {
                fputcsv($handle, [
                    $p->student?->user?->name ?? '',
                    $p->student?->admission?->parent_name ?? '',
                    $p->student?->admission?->father_phone ?? $p->student?->admission?->phone ?? '',
                    $p->month,
                    $typeMap[$p->type ?? 'monthly'] ?? ($p->type ?? ''),
                    number_format((float) $p->amount, 2),
                    $statusMap[$p->status] ?? ($p->status ?? ''),
                    $p->due_date?->format('Y-m-d') ?? '',
                    $p->paid_date?->format('Y-m-d') ?? '',
                    $p->payment_method ?? '',
                    $p->account_name ?? '',
                    $p->notes ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function sendPaymentReminder(Payment $payment, Request $request)
    {
        if ($payment->status === 'paid') {
            return response()->json(['success' => false, 'message' => 'هذه الدفعة مدفوعة بالفعل'], 400);
        }

        $graceDays = (int) $request->input('grace_days', 0);
        $graceDays = max(0, min(4, $graceDays));

        $payment->load(['student.user', 'student.parent']);
        $parent      = $payment->student?->parent;
        $studentName = $payment->student?->user?->name ?? 'الطالب';

        if (! $parent) {
            return response()->json(['success' => false, 'message' => 'لا يوجد ولي أمر مرتبط بهذا الطالب'], 400);
        }

        $graceText = match ($graceDays) {
            0       => 'اليوم',
            1       => 'خلال يوم واحد',
            2       => 'خلال يومين',
            default => "خلال {$graceDays} أيام",
        };

        $parent->notify(new \App\Notifications\AcademyNotification(
            "تذكير: دفعة {$payment->formatted_month} للطالب {$studentName} بمبلغ {$payment->amount} ش.ج، المطلوب السداد {$graceText}",
            route('parent.payments'),
            'warning'
        ));

        $payment->update([
            'reminder_grace_days'   => $graceDays,
            'last_reminder_sent_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "تم إرسال تذكير لولي أمر {$studentName}",
        ]);
    }

    public function sendFilteredReminders(Request $request)
    {
        $month   = $request->get('month', now()->format('Y-m'));
        $groupId = $request->get('group_id');

        $payments = Payment::with(['student.user', 'student.parent'])
            ->where('month', $month)
            ->where('status', 'unpaid')
            ->when($groupId, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('group_id', $groupId)))
            ->get();

        $sent = 0;
        foreach ($payments as $payment) {
            $parent      = $payment->student?->parent;
            $studentName = $payment->student?->user?->name ?? 'الطالب';

            if ($parent) {
                $parent->notify(new \App\Notifications\AcademyNotification(
                    "تذكير: دفعة {$payment->formatted_month} للطالب {$studentName} بمبلغ {$payment->amount} ش.ج لم تُسدَّد بعد",
                    route('parent.payments'),
                    'warning'
                ));
                $sent++;
            }
        }

        NotificationService::notifyRole(
            'admin',
            "تم إرسال {$sent} تذكير دفع لشهر {$month}",
            route('admin.payments'),
            'info'
        );

        return response()->json([
            'success' => true,
            'message' => "تم إرسال {$sent} تذكير من أصل {$payments->count()} دفعة غير مسددة",
            'sent'    => $sent,
            'total'   => $payments->count(),
        ]);
    }

    public function getFinancialReport()
    {
        $monthNames = [
            '01' => 'يناير', '02' => 'فبراير', '03' => 'مارس',    '04' => 'أبريل',
            '05' => 'مايو',  '06' => 'يونيو',  '07' => 'يوليو',   '08' => 'أغسطس',
            '09' => 'سبتمبر','10' => 'أكتوبر', '11' => 'نوفمبر',  '12' => 'ديسمبر',
        ];

        $now       = now();
        $yearMonth = $now->format('Y-m');
        $rangeStart = $now->copy()->subMonths(11)->startOfMonth()->toDateString();

        $isPgsql = DB::getDriverName() === 'pgsql';

        // ── 1 query: كل المدفوعات الـ paid في آخر 12 شهراً مُجمَّعة بالشهر ──
        $paidYm = $isPgsql ? "TO_CHAR(paid_date, 'YYYY-MM')" : "DATE_FORMAT(paid_date, '%Y-%m')";
        $paidByMonth = Payment::paid()
            ->where('paid_date', '>=', $rangeStart)
            ->selectRaw("$paidYm as ym, SUM(amount) as total")
            ->groupByRaw($paidYm)
            ->pluck('total', 'ym');

        // ── 1 query: كل المدفوعات غير المسددة مُجمَّعة بالشهر ──
        $unpaidByMonth = Payment::unpaid()
            ->where('month', '>=', $rangeStart)
            ->selectRaw('month as ym, SUM(amount) as total')
            ->groupBy('month')
            ->pluck('total', 'ym');

        // ── 1 query: مصاريف مُجمَّعة بالشهر ──
        $expenseYm = $isPgsql ? "TO_CHAR(expense_date, 'YYYY-MM')" : "DATE_FORMAT(expense_date, '%Y-%m')";
        $expensesByMonth = Expense::where('expense_date', '>=', $rangeStart)
            ->selectRaw("$expenseYm as ym, SUM(amount) as total")
            ->groupByRaw($expenseYm)
            ->pluck('total', 'ym');

        // ── 1 query: رواتب مُجمَّعة بالشهر ──
        $salaryYm = $isPgsql ? "TO_CHAR(payment_date, 'YYYY-MM')" : "DATE_FORMAT(payment_date, '%Y-%m')";
        $salariesByMonth = SalaryPayment::where('payment_date', '>=', $rangeStart)
            ->selectRaw("$salaryYm as ym, SUM(amount) as total")
            ->groupByRaw($salaryYm)
            ->pluck('total', 'ym');

        // ── 1 query: المتأخرات كلها ──
        $overdueRows = Payment::unpaid()
            ->where('month', '<', $yearMonth)
            ->selectRaw('SUM(amount) as total, COUNT(*) as cnt')
            ->first();

        // ── 1 query: إيرادات اليوم ──
        $todayRevenue = (float) Payment::paid()->whereDate('paid_date', today())->sum('amount');

        // ── 1 query: توزيع حسب النوع لهذا الشهر ──
        $typeLabels = ['monthly' => 'شهري', 'admission_fee' => 'رسوم انتساب', 'educational_bundle' => 'حزمة تعليمية'];
        $byType = Payment::paid()
            ->whereYear('paid_date', $now->year)
            ->whereMonth('paid_date', $now->month)
            ->selectRaw('type, SUM(amount) as total, COUNT(*) as cnt')
            ->groupBy('type')
            ->get()
            ->map(fn($r) => ['type' => $r->type, 'label' => $typeLabels[$r->type] ?? $r->type, 'total' => (float)$r->total, 'count' => $r->cnt])
            ->values();

        // ── 1 query: أعلى الطلاب متأخرة (مع sub-query للاسم) ──
        $topUnpaidRaw = Payment::unpaid()
            ->selectRaw('student_id, SUM(amount) as total_unpaid, COUNT(*) as months_count')
            ->groupBy('student_id')
            ->orderByDesc('total_unpaid')
            ->limit(10)
            ->get();
        $studentIds = $topUnpaidRaw->pluck('student_id')->toArray();
        $studentNames = \App\Models\Student::with('user:id,name')
            ->whereIn('id', $studentIds)
            ->get(['id'])
            ->pluck('user.name', 'id');
        $topUnpaid = $topUnpaidRaw->map(fn($p) => [
            'student_name' => $studentNames[$p->student_id] ?? 'غير محدد',
            'total_unpaid' => (float) $p->total_unpaid,
            'months_count' => $p->months_count,
        ])->values();

        // ── بناء القيم من البيانات المُجمَّعة (بدون queries إضافية) ──
        $monthRevenue      = (float) ($paidByMonth[$yearMonth]    ?? 0);
        $unpaidThisMonth   = (float) ($unpaidByMonth[$yearMonth]  ?? 0);
        $expensesThisMonth = (float) ($expensesByMonth[$yearMonth]?? 0);
        $salariesThisMonth = (float) ($salariesByMonth[$yearMonth]?? 0);
        $netThisMonth      = $monthRevenue - $expensesThisMonth - $salariesThisMonth;
        $overdueAmount     = (float) ($overdueRows->total ?? 0);
        $overdueCount      = (int)   ($overdueRows->cnt   ?? 0);

        // ── Monthly Trend (آخر 6 أشهر) ──
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $d    = $now->copy()->subMonths($i);
            $ym   = $d->format('Y-m');
            $monthlyTrend[] = [
                'month'  => $ym,
                'label'  => ($monthNames[$d->format('m')] ?? '') . ' ' . $d->format('Y'),
                'amount' => (float) ($paidByMonth[$ym] ?? 0),
            ];
        }

        // ── Monthly History (آخر 12 شهراً) ──
        $monthlyHistory = [];
        for ($i = 11; $i >= 0; $i--) {
            $d   = $now->copy()->subMonths($i);
            $ym  = $d->format('Y-m');
            $rev = (float) ($paidByMonth[$ym]    ?? 0);
            $exp = (float) ($expensesByMonth[$ym]?? 0);
            $sal = (float) ($salariesByMonth[$ym]?? 0);
            $monthlyHistory[] = [
                'month'    => $ym,
                'label'    => ($monthNames[$d->format('m')] ?? '') . ' ' . $d->format('Y'),
                'revenue'  => $rev,
                'unpaid'   => (float) ($unpaidByMonth[$ym] ?? 0),
                'expenses' => $exp,
                'salaries' => $sal,
                'net'      => $rev - $exp - $sal,
            ];
        }

        return response()->json([
            'success'               => true,
            'today_revenue'         => $todayRevenue,
            'month_revenue'         => $monthRevenue,
            'unpaid_this_month'     => $unpaidThisMonth,
            'overdue_amount'        => $overdueAmount,
            'overdue_count'         => $overdueCount,
            'expenses_this_month'   => $expensesThisMonth,
            'salaries_this_month'   => $salariesThisMonth,
            'net_this_month'        => $netThisMonth,
            'monthly_trend'         => $monthlyTrend,
            'monthly_history'       => $monthlyHistory,
            'by_type'               => $byType,
            'top_unpaid'            => $topUnpaid,
        ]);
    }

    public function markPaymentAsPaid(Payment $payment)
    {
        $payment->update([
            'status'         => 'paid',
            'paid_date'      => now(),
            'payment_method' => 'cash',
        ]);

        NotificationService::notifyPaymentReceived($payment);

        return back()->with('success', 'تم تحديث حالة الدفع وإرسال إشعار لولي الأمر');
    }

    // ══════════════════════════════════════════════
    //  المصروفات
    // ══════════════════════════════════════════════

    public function getExpenses(Request $request)
    {
        $year  = $request->year  ?? now()->year;
        $month = $request->month ?? now()->month;

        $query = Expense::byMonth($year, $month)->orderByDesc('expense_date');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $expenses = $query->get()->map(fn($e) => [
            'id'           => $e->id,
            'category'     => $e->category,
            'description'  => $e->description,
            'amount'       => (float) $e->amount,
            'expense_date' => $e->expense_date->toDateString(),
            'notes'        => $e->notes,
        ]);

        $total = $expenses->sum('amount');

        return response()->json([
            'success'    => true,
            'expenses'   => $expenses,
            'total'      => $total,
            'categories' => Expense::$defaultCategories,
        ]);
    }

    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'category'     => 'required|string|max:100',
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string|max:500',
        ]);

        $expense = Expense::create(array_merge($data, ['created_by' => auth()->id()]));

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة المصروف بنجاح',
            'expense' => [
                'id'           => $expense->id,
                'category'     => $expense->category,
                'description'  => $expense->description,
                'amount'       => (float) $expense->amount,
                'expense_date' => $expense->expense_date->toDateString(),
                'notes'        => $expense->notes,
            ],
        ]);
    }

    public function updateExpense(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'category'     => 'required|string|max:100',
            'description'  => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'notes'        => 'nullable|string|max:500',
        ]);

        $expense->update($data);

        return response()->json(['success' => true, 'message' => 'تم تحديث المصروف']);
    }

    public function destroyExpense(Expense $expense)
    {
        $expense->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف المصروف']);
    }

    // ══════════════════════════════════════════════
    //  الرواتب
    // ══════════════════════════════════════════════

    public function getSalaryData(Request $request)
    {
        $untilDate = $request->until_date ?? Setting::get('academic_year_end_date');

        $teachers = Teacher::with('user')
            ->whereNotNull('salary')
            ->where('salary', '>', 0)
            ->get()
            ->map(function ($t) use ($untilDate) {
                $cycle = $t->getCurrentCycleInfo($untilDate);
                return [
                    'id'        => $t->id,
                    'type'      => 'teacher',
                    'name'      => $t->user?->name ?? '—',
                    'salary'    => (float) $t->salary,
                    'hire_date' => $t->hire_date?->toDateString(),
                    'cycle'     => $cycle,
                ];
            });

        $employees = Employee::with('user')
            ->whereNotNull('salary')
            ->where('salary', '>', 0)
            ->get()
            ->map(function ($e) use ($untilDate) {
                $cycle = $e->getCurrentCycleInfo($untilDate);
                return [
                    'id'        => $e->id,
                    'type'      => 'employee',
                    'name'      => $e->user?->name ?? '—',
                    'salary'    => (float) $e->salary,
                    'hire_date' => $e->hire_date?->toDateString(),
                    'cycle'     => $cycle,
                ];
            });

        return response()->json([
            'success'               => true,
            'staff'                 => $teachers->merge($employees)->values(),
            'until_date'            => $untilDate,
            'academic_year_end_date'=> Setting::get('academic_year_end_date'),
        ]);
    }

    public function storeSalaryPayment(Request $request)
    {
        $data = $request->validate([
            'payable_type'     => 'required|in:teacher,employee',
            'payable_id'       => 'required|integer',
            'days_worked'      => 'required|integer|min:1|max:31',
            'daily_rate'       => 'required|numeric|min:0',
            'amount'           => 'required|numeric|min:0',
            'cycle_start_date' => 'required|date',
            'cycle_end_date'   => 'required|date',
            'payment_date'     => 'required|date',
            'payment_method'   => 'nullable|string|max:50',
            'notes'            => 'nullable|string|max:500',
        ]);

        $modelClass = $data['payable_type'] === 'teacher'
            ? 'App\\Models\\Teacher'
            : 'App\\Models\\Employee';

        SalaryPayment::create([
            'payable_type'     => $modelClass,
            'payable_id'       => $data['payable_id'],
            'amount'           => $data['amount'],
            'daily_rate'       => $data['daily_rate'],
            'days_worked'      => $data['days_worked'],
            'cycle_start_date' => $data['cycle_start_date'],
            'cycle_end_date'   => $data['cycle_end_date'],
            'payment_date'     => $data['payment_date'],
            'payment_method'   => $data['payment_method'] ?? null,
            'notes'            => $data['notes'] ?? null,
            'created_by'       => auth()->id(),
        ]);

        return response()->json(['success' => true, 'message' => 'تم تسجيل دفع الراتب بنجاح']);
    }

    public function getSalaryPayments(Request $request)
    {
        $year  = $request->year  ?? now()->year;
        $month = $request->month ?? now()->month;

        $payments = SalaryPayment::with(['payable.user'])
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->orderByDesc('payment_date')
            ->get()
            ->map(fn($p) => [
                'id'               => $p->id,
                'name'             => $p->payable_name,
                'role'             => $p->payable_role,
                'amount'           => (float) $p->amount,
                'days_worked'      => $p->days_worked,
                'daily_rate'       => (float) $p->daily_rate,
                'cycle_start_date' => $p->cycle_start_date->toDateString(),
                'payment_date'     => $p->payment_date->toDateString(),
                'payment_method'   => $p->payment_method,
            ]);

        return response()->json([
            'success'  => true,
            'payments' => $payments,
            'total'    => $payments->sum('amount'),
        ]);
    }


/**
 * عرض صفحة المحاضرات الرئيسية
 */
    public function lecturesIndex()
    {
        // إحصائيات سريعة
        $stats = [
            'total_lectures'      => Lecture::count(),
            'today_lectures'      => Lecture::whereDate('date', today())->count(),
            'this_week_lectures'  => Lecture::whereBetween('date', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),
            'this_month_lectures' => Lecture::whereMonth('date', now()->month)->count(),
            'active_series'       => $this->getActiveSeriesCount(),
            'upcoming_exams'      => $this->getUpcomingExams()->count(),
        ];

        return view('admin.lectures.index', compact('stats'));
    }

/**
 * جلب بيانات المحاضرات للتقويم
 */
    public function getCalendarData(Request $request)
    {
        try {
            $start = $request->get('start');
            $end   = $request->get('end');

            // تحقق من وجود محاضرات أولاً
            $lecturesCount = Lecture::count();
            if ($lecturesCount === 0) {
                return response()->json([]);
            }

            $query = Lecture::with(['teacher.user', 'group', 'subject']);

            if ($start && $end) {
                $query->whereBetween('date', [$start, $end]);
            }

            $lectures = $query->get()->map(function (Lecture $lecture) {
                $startTime = '09:00:00';
                $endTime   = '10:30:00';

                if ($lecture->start_time) {
                    $startTime = is_string($lecture->start_time) ?
                    $lecture->start_time . ':00' :
                    $lecture->start_time->format('H:i:s');
                }

                if ($lecture->end_time) {
                    $endTime = is_string($lecture->end_time) ?
                    $lecture->end_time . ':00' :
                    $lecture->end_time->format('H:i:s');
                }

                return [
                    'id'              => $lecture->id,
                    'title'           => $lecture->title ?? 'محاضرة بدون عنوان',
                    'start'           => $lecture->date->format('Y-m-d') . 'T' . $startTime,
                    'end'             => $lecture->date->format('Y-m-d') . 'T' . $endTime,
                    'backgroundColor' => $lecture->getTypeColor(),
                    'borderColor'     => $lecture->getStatusColor(),
                    'extendedProps'   => [
                        'type'           => $lecture->type ?? 'lecture',
                        'status'         => $lecture->status ?? 'scheduled',
                        'teacher_name'   => $lecture->teacher?->user?->name ?? 'غير محدد',
                        'group_name'     => $lecture->group?->name ?? 'غير محدد',
                        'subject_name'   => $lecture->subject?->name ?? '',
                        'subject_id'     => $lecture->subject_id,
                        'start_time'     => $startTime,
                        'end_time'       => $endTime,
                        'students_count' => $lecture->group?->students_count ?? 0,
                        'series_id'      => $lecture->series_id,
                        'description'    => $lecture->description ?? '',
                    ],
                ];
            });

            return response()->json($lectures);

        } catch (\Exception $e) {
            Log::error('Error in getCalendarData', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ]);

            return response()->json([]);
        }

    }

    /**
     * API بيانات تقويم الـ Dashboard المحسن - النسخة النهائية
     */
    public function getDashboardCalendarData()
    {
        try {
            $lecturesCount = Lecture::count();
            if ($lecturesCount === 0) {
                return response()->json([
                    'lectures' => [],
                    'stats'    => [
                        'total_lectures'    => 0,
                        'today_lectures'    => 0,
                        'upcoming_lectures' => 0,
                        'subjects_count'    => 0,
                    ],
                    'message'  => 'لا توجد محاضرات في النظام',
                ]);
            }

            // استعلام بسيط بدون فلتر تاريخ أولاً
            $query = Lecture::query();

            // إضافة العلاقات تدريجياً
            try {
                $query->with(['subject', 'teacher.user', 'group']);
            } catch (\Exception) {
                // تجاهل أخطاء العلاقات
            }

            $lectures = $query->orderBy('date')->get();

            $formattedLectures = $lectures->map(function ($lecture) {
                return [
                    'id'             => $lecture->id,
                    'title'          => $lecture->title ?? 'محاضرة',
                    'subject'        => optional($lecture->subject)->name ?? 'مادة عامة',
                    'date'           => $lecture->date->format('Y-m-d'),
                    'start_time'     => $this->formatLectureTime($lecture->start_time),
                    'end_time'       => $this->formatLectureTime($lecture->end_time),
                    'teacher'        => optional($lecture->teacher->user ?? null)->name ?? 'غير محدد',
                    'group'          => optional($lecture->group)->name ?? 'مجموعة عامة',
                    'students_count' => optional($lecture->group)->students_count ?? 0,
                    'is_today'       => $lecture->date->isToday(),
                ];
            });

            $stats = [
                'total_lectures'    => $formattedLectures->count(),
                'today_lectures'    => $formattedLectures->where('is_today', true)->count(),
                'upcoming_lectures' => $formattedLectures->count(),
                'subjects_count'    => $formattedLectures->pluck('subject')->unique()->count(),
            ];

            return response()->json([
                'lectures'        => $formattedLectures,
                'stats'           => $stats,
                'database_source' => true,
                'last_updated'    => now()->format('H:i'),
            ]);

        } catch (\Exception $e) {
            Log::error('خطأ في Dashboard Calendar: ' . $e->getMessage());

            return response()->json([
                'error'    => 'خطأ: ' . $e->getMessage(),
                'lectures' => [],
                'stats'    => ['total_lectures' => 0, 'today_lectures' => 0, 'upcoming_lectures' => 0, 'subjects_count' => 0],
            ], 500);
        }

    }

/**
 * تنسيق وقت المحاضرة
 */
    private function formatLectureTime(mixed $time): string
    {
        if (! $time) {
            return '09:00';
        }

// إذا كان datetime كامل
        if (is_string($time) && strlen($time) > 8) {
            try {
                $carbonTime = \Carbon\Carbon::parse($time);
                return $carbonTime->format('H:i');
            } catch (\Exception) {
                return '09:00';
            }
        }

// إذا كان time فقط
        if (is_string($time)) {
            return substr($time, 0, 5); // أخذ الساعة والدقيقة فقط
        }

        if ($time instanceof \DateTime) {
            return $time->format('H:i');
        }

        return '09:00';

    }



/**
 * إحصائيات سريعة للـ Dashboard (دالة إضافية اختيارية)
 */
    public function getDashboardQuickStats()
    {
        try {
            $today     = now()->format('Y-m-d');
            $thisWeek  = [now()->startOfWeek()->format('Y-m-d'), now()->endOfWeek()->format('Y-m-d')];
            $thisMonth = now()->format('Y-m');

            $stats = [
                'today' => [
                    'total'     => Lecture::whereDate('date', $today)->count(),
                    'completed' => Lecture::whereDate('date', $today)
                        ->where('status', 'completed')->count(),
                    'upcoming'  => Lecture::whereDate('date', $today)
                        ->whereTime('start_time', '>', now()->format('H:i:s'))
                        ->count(),
                ],

                'week'  => [
                    'total'    => Lecture::whereBetween('date', $thisWeek)->count(),
                    'subjects' => Lecture::whereBetween('date', $thisWeek)
                        ->with('subject')
                        ->get()
                        ->pluck('subject.name')
                        ->filter()
                        ->unique()
                        ->count(),
                ],

                'month' => [
                    'total'    => Lecture::whereYear('date', substr($thisMonth, 0, 4))->whereMonth('date', (int) substr($thisMonth, 5, 2))->count(),
                    'teachers' => Lecture::whereYear('date', substr($thisMonth, 0, 4))->whereMonth('date', (int) substr($thisMonth, 5, 2))
                        ->with('teacher')
                        ->get()
                        ->pluck('teacher.id')
                        ->filter()
                        ->unique()
                        ->count(),
                ],
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('خطأ في إحصائيات Dashboard السريعة', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'today' => ['total' => 0, 'completed' => 0, 'upcoming' => 0],
                'week'  => ['total' => 0, 'subjects' => 0],
                'month' => ['total' => 0, 'teachers' => 0],
            ]);
        }
    }


/**
 * جلب بيانات المحاضرات للجدول
 */
    public function getLecturesData(Request $request)
    {
        try {
            $lectures = Lecture::with(['teacher.user', 'group', 'subject'])
                ->withCount(['attendance as attendance_count' => fn($q) => $q->where('status', 'present')])
                ->when($request->get('date_from'), fn($q) => $q->whereDate('date', '>=', $request->get('date_from')))
                ->when($request->get('date_to'),   fn($q) => $q->whereDate('date', '<=', $request->get('date_to')))
                ->when($request->get('group_id'),   fn($q) => $q->where('group_id', $request->get('group_id')))
                ->when($request->get('teacher_id'), fn($q) => $q->where('teacher_id', $request->get('teacher_id')))
                ->when(! $request->get('date_from') && ! $request->get('date_to'),
                    fn($q) => $q->where('date', '>=', now()->subMonths(2))
                )
                ->orderBy('date', 'desc')
                ->orderBy('start_time', 'asc')
                ->limit(500)
                ->get()
                ->map(function ($lecture) {
                    return [
                        'id'               => $lecture->id,
                        'title'            => $lecture->title,
                        'date'             => $lecture->date->format('Y-m-d'),
                        'start_time'       => $lecture->start_time
                            ? (\is_string($lecture->start_time) ? $lecture->start_time : $lecture->start_time->format('H:i'))
                            : '00:00',
                        'end_time'         => $lecture->end_time
                            ? (\is_string($lecture->end_time) ? $lecture->end_time : $lecture->end_time->format('H:i'))
                            : '23:59',
                        'teacher_name'     => $lecture->teacher?->user?->name ?? 'غير محدد',
                        'group_name'       => $lecture->group?->name ?? 'غير محدد',
                        'subject_name'     => $lecture->subject?->name ?? '',
                        'students_count'   => $lecture->group?->students_count ?? 0,
                        'status'           => $lecture->status ?? 'scheduled',
                        'type'             => $lecture->type ?? 'lecture',
                        'series_id'        => $lecture->series_id,
                        'description'      => $lecture->description,
                        'attendance_count' => $lecture->attendance_count,
                    ];
                });

            return response()->json([
                'success'  => true,
                'lectures' => $lectures,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getLecturesData', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب بيانات المحاضرات: ' . $e->getMessage(),
            ], 500);
        }

    }

/**
 * إنشاء محاضرة جديدة
 */
    public function storeLecture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'date'        => 'required|date|after_or_equal:today',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'teacher_id'  => 'required|exists:teachers,id',
            'group_id'    => 'required|exists:groups,id',
            'subject_id'  => 'nullable|exists:subjects,id',
            'type'        => 'in:lecture,exam,review,activity',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // التحقق من تضارب الأوقات
            $conflicts = $this->checkTimeConflicts(
                $request->teacher_id,
                $request->date,
                $request->start_time,
                $request->end_time
            );

            if ($conflicts->count() > 0) {
                return response()->json([
                    'success'   => false,
                    'message'   => 'يوجد تضارب في الأوقات مع محاضرات أخرى للمدرس',
                    'conflicts' => $conflicts,
                ], 400);
            }

            $lecture = Lecture::create([
                'title'       => $request->title,
                'date'        => $request->date,
                'start_time'  => $request->start_time,
                'end_time'    => $request->end_time,
                'teacher_id'  => $request->teacher_id,
                'group_id'    => $request->group_id,
                'subject_id'  => $request->subject_id,
                'type'        => $request->type ?? 'lecture',
                'status'      => 'scheduled',
                'description' => $request->description,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء المحاضرة بنجاح',
                'lecture' => $lecture->load(['teacher.user', 'group', 'subject']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إنشاء المحاضرة: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateLecture(Request $request, Lecture $lecture)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'date'        => 'required|date',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'teacher_id'  => 'required|exists:teachers,id',
            'group_id'    => 'required|exists:groups,id',
            'subject_id'  => 'nullable|exists:subjects,id',
            'type'        => 'in:lecture,exam,review,activity',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $conflicts = $this->checkTimeConflicts(
                $request->teacher_id,
                $request->date,
                $request->start_time,
                $request->end_time,
                $lecture->id
            );

            if ($conflicts->count() > 0) {
                return response()->json([
                    'success'   => false,
                    'message'   => 'يوجد تضارب في الأوقات مع محاضرات أخرى للمدرس',
                    'conflicts' => $conflicts,
                ], 400);
            }

            $lecture->update([
                'title'       => $request->title,
                'date'        => $request->date,
                'start_time'  => $request->start_time,
                'end_time'    => $request->end_time,
                'teacher_id'  => $request->teacher_id,
                'group_id'    => $request->group_id,
                'subject_id'  => $request->subject_id,
                'type'        => $request->type ?? $lecture->type,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث المحاضرة بنجاح',
                'lecture' => $lecture->fresh()->load(['teacher.user', 'group', 'subject']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحديث المحاضرة: ' . $e->getMessage(),
            ], 500);
        }
    }

/**
 * إنشاء سلسلة محاضرات متكررة
 */
    // public function createLectureSeries(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'title'       => 'required|string|max:255',
    //         'start_date'  => 'required|date|after_or_equal:today',
    //         'start_time'  => 'required|date_format:H:i',
    //         'end_time'    => 'required|date_format:H:i|after:start_time',
    //         'teacher_id'  => 'required|exists:teachers,id',
    //         'group_id'    => 'required|exists:groups,id',
    //         'subject_id'  => 'nullable|exists:subjects,id',
    //         'days'        => 'required|array|min:1',
    //         'days.*'      => 'in:0,1,2,3,4,5,6', // أيام الأسبوع
    //         'description' => 'nullable|string|max:1000',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'بيانات غير صحيحة',
    //             'errors'  => $validator->errors(),
    //         ], 422);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // إنشاء معرف فريد للسلسلة
    //         $seriesId = 'series_' . time() . '_' . $request->group_id;

    //         // إنشاء المحاضرات للأسابيع القادمة (حتى 52 أسبوع)
    //         $startDate       = Carbon::parse($request->start_date);
    //         $createdLectures = [];

    //         for ($week = 0; $week < 52; $week++) {
    //             foreach ($request->days as $dayOfWeek) {
    //                 $lectureDate = $startDate->copy()->addWeeks($week)->startOfWeek()->addDays($dayOfWeek);

    //                 // تخطي التواريخ السابقة
    //                 if ($lectureDate->lt(now()->startOfDay())) {
    //                     continue;
    //                 }

    //                 // التحقق من تضارب الأوقات
    //                 $conflicts = $this->checkTimeConflicts(
    //                     $request->teacher_id,
    //                     $lectureDate->format('Y-m-d'),
    //                     $request->start_time,
    //                     $request->end_time
    //                 );

    //                 if ($conflicts->count() === 0) {
    //                     $lecture = Lecture::create([
    //                         'title'       => $request->title,
    //                         'date'        => $lectureDate->format('Y-m-d'),
    //                         'start_time'  => $request->start_time,
    //                         'end_time'    => $request->end_time,
    //                         'teacher_id'  => $request->teacher_id,
    //                         'group_id'    => $request->group_id,
    //                         'subject_id'  => $request->subject_id,
    //                         'type'        => 'lecture',
    //                         'status'      => 'scheduled',
    //                         'series_id'   => $seriesId,
    //                         'description' => $request->description,
    //                     ]);

    //                     $createdLectures[] = $lecture;
    //                 }
    //             }
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success'        => true,
    //             'message'        => "تم إنشاء سلسلة من {count($createdLectures)} محاضرة بنجاح",
    //             'series_id'      => $seriesId,
    //             'lectures_count' => count($createdLectures),
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'حدث خطأ في إنشاء السلسلة: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function createLectureSeries(Request $request, SeriesGenerator $generator)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'start_date'  => 'required|date|after_or_equal:today',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'teacher_id'  => 'required|exists:teachers,id',
            'group_id'    => 'required|exists:groups,id',
            'subject_id'  => 'nullable|exists:subjects,id',
            'days'        => 'required|array|min:1',
            'days.*'      => 'in:0,1,2,3,4,5,6',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $startDate = Carbon::parse($request->start_date);
            $endDate   = $request->end_date ? Carbon::parse($request->end_date) : $startDate->copy()->addMonths(4); // 4 أشهر بدلاً من 52 أسبوع

            // إنشاء السلسلة
            $series = LectureSeries::create([
                'title'      => $request->title,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'start_time' => $request->start_time,
                'end_time'   => $request->end_time,
                'teacher_id' => $request->teacher_id,
                'group_id'   => $request->group_id,
                'subject_id' => $request->subject_id,
            ]);

            // إضافة أيام الأسبوع للسلسلة — تحويل الرقم إلى اسم يوم (0=sunday ... 6=saturday)
            $dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            foreach ($request->days as $day) {
                $series->days()->create(['day_of_week' => $dayNames[(int) $day]]);
            }

            // توليد المحاضرات باستخدام Service
            $generator->generateLectures($series);

            DB::commit();

            return response()->json([
                'success'   => true,
                'message'   => 'تم إنشاء سلسلة المحاضرات لمدة 4 أشهر والمحاضرات المرتبطة بها بنجاح',
                'series_id' => $series->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating lecture series', [
                'error'        => $e->getMessage(),
                'request_data' => $request->all(),
                'trace'        => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إنشاء السلسلة: ' . $e->getMessage(),
            ], 500);
        }
    }

/**
 * إنشاء امتحان نهائي (ينهي السلسلة)
 */
    public function createFinalExam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'series_id'   => 'required|string',
            'title'       => 'required|string|max:255',
            'date'        => 'required|date|after:today',
            'start_time'  => 'required|date_format:H:i',
            'duration'    => 'required|integer|min:30|max:480', // بالدقائق
            'teacher_id'  => 'required|exists:teachers,id',
            'group_id'    => 'required|exists:groups,id',
            'subject_id'  => 'nullable|exists:subjects,id',
            'room'        => 'nullable|string|max:100',
            'total_marks' => 'required|integer|min:1|max:1000',
            'notes'       => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // حساب وقت انتهاء الامتحان
            $startTime = Carbon::parse($request->start_time);
            $endTime   = $startTime->copy()->addMinutes($request->duration);

            // إنشاء الامتحان النهائي
            $finalExam = Lecture::create([
                'title'       => $request->title,
                'date'        => $request->date,
                'start_time'  => $request->start_time,
                'end_time'    => $endTime->format('H:i'),
                'teacher_id'  => $request->teacher_id,
                'group_id'    => $request->group_id,
                'subject_id'  => $request->subject_id,
                'type'        => 'final_exam',
                'status'      => 'scheduled',
                'series_id'   => $request->series_id,
                'room'        => $request->room,
                'total_marks' => $request->total_marks,
                'description' => $request->notes,
            ]);

            // حذف المحاضرات المتبقية في السلسلة بعد تاريخ الامتحان
            Lecture::where('series_id', $request->series_id)
                ->where('date', '>', $request->date)
                ->where('type', '!=', 'final_exam')
                ->delete();

            // تحديث حالة السلسلة لتصبح "مكتملة"
            Lecture::where('series_id', $request->series_id)
                ->update(['series_status' => 'completed']);

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'تم إنشاء الامتحان النهائي وإنهاء السلسلة بنجاح',
                'final_exam' => $finalExam->load(['teacher.user', 'group', 'subject']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إنشاء الامتحان النهائي: ' . $e->getMessage(),
            ], 500);
        }
    }

/**
 * تأجيل محاضرة
 */
    public function rescheduleLecture(Request $request, Lecture $lecture)
    {
        $validator = Validator::make($request->all(), [
            'new_date'       => 'required|date|after_or_equal:today',
            'new_start_time' => 'required|date_format:H:i',
            'new_end_time'   => 'required|date_format:H:i|after:new_start_time',
            'reason'         => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // التحقق من تضارب الأوقات الجديدة
            $conflicts = $this->checkTimeConflicts(
                $lecture->teacher_id,
                $request->new_date,
                $request->new_start_time,
                $request->new_end_time,
                $lecture->id
            );

            if ($conflicts->count() > 0) {
                return response()->json([
                    'success'   => false,
                    'message'   => 'يوجد تضارب في الأوقات الجديدة',
                    'conflicts' => $conflicts,
                ], 400);
            }

            // حفظ البيانات القديمة
            $oldData = [
                'date'       => $lecture->date,
                'start_time' => $lecture->start_time,
                'end_time'   => $lecture->end_time,
            ];

            // تحديث المحاضرة
            $lecture->update([
                'date'                => $request->new_date,
                'start_time'          => $request->new_start_time,
                'end_time'            => $request->new_end_time,
                'status'              => 'rescheduled',
                'reschedule_reason'   => $request->reason,
                'reschedule_old_data' => json_encode($oldData),
                'rescheduled_at'      => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تأجيل المحاضرة بنجاح',
                'lecture' => $lecture->fresh()->load(['teacher.user', 'group', 'subject']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تأجيل المحاضرة: ' . $e->getMessage(),
            ], 500);
        }
    }

/**
 * إلغاء محاضرة
 */
    public function cancelLecture(Request $request, Lecture $lecture)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى تحديد سبب الإلغاء',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $lecture->update([
                'status'              => 'cancelled',
                'cancellation_reason' => $request->reason,
                'cancelled_at'        => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء المحاضرة بنجاح',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إلغاء المحاضرة: ' . $e->getMessage(),
            ], 500);
        }
    }


/**
 * التحقق من تضارب الأوقات
 */
    private function checkTimeConflicts(int $teacherId, string $date, string $startTime, string $endTime, ?int $excludeLectureId = null)
    {
        return Lecture::where('teacher_id', $teacherId)
            ->whereDate('date', $date)
            ->where('status', '!=', 'cancelled')
            ->when($excludeLectureId, function ($query) use ($excludeLectureId) {
                return $query->where('id', '!=', $excludeLectureId);
            })
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($subQuery) use ($startTime, $endTime) {
                        $subQuery->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });
    }


/**
 * عدد السلاسل النشطة
 */
    private function getActiveSeriesCount()
    {
        return Lecture::whereNotNull('series_id')
            ->where('series_status', '!=', 'completed')
            ->distinct('series_id')
            ->count();
    }

/**
 * الامتحانات القادمة
 */
    private function getUpcomingExams()
    {
        return Lecture::where('type', 'final_exam')
            ->where('date', '>=', today())
            ->orderBy('date')
            ->limit(5);
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
        NotificationService::notifyLowAttendance(75);

        $lowAttendanceCount = Student::get()->filter(function ($student) {
            return $student->getAttendancePercentage() < 75;
        })->count();

        return back()->with('success', "تم إرسال تنبيهات نسبة الحضور المنخفض لـ {$lowAttendanceCount} ولي أمر");
    }

    public function getStudentEditData(Student $student)
    {
        $student->load(['user', 'admission']);

        $admission = $student->admission;

        return response()->json([
            'success' => true,
            'student' => [
                'id'                => $student->id,
                'name'              => $student->user?->name ?? '',
                'email'             => $student->user?->email ?? '',
                'national_id'       => $student->user?->national_id ?? $admission?->student_id ?? '',
                'birth_date'        => $student->birth_date?->format('Y-m-d') ?? '',
                'has_admission'     => ! is_null($admission),
                'admission_id'      => $admission?->id,
                'parent_name'       => $admission?->parent_name ?? '',
                'parent_national_id'=> $admission?->parent_id ?? '',
                'parent_job'        => $admission?->parent_job ?? '',
                'father_phone'      => $admission?->father_phone ?? '',
                'mother_phone'      => $admission?->mother_phone ?? '',
                'address'           => $admission?->address ?? '',
                'grade'             => $admission?->grade ?? '',
                'academic_level'    => $admission?->academic_level ?? '',
                'monthly_fee'       => $admission?->monthly_fee ?? '',
                'study_start_date'  => $admission?->study_start_date?->format('Y-m-d') ?? '',
            ],
        ]);
    }

    public function updateStudent(Request $request, Student $student)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'national_id'        => 'nullable|string|max:20',
            'birth_date'         => 'nullable|date',
            'parent_name'        => 'nullable|string|max:255',
            'parent_national_id' => 'nullable|string|max:20',
            'parent_job'         => 'nullable|string|max:255',
            'father_phone'       => 'nullable|string|max:20',
            'mother_phone'       => 'nullable|string|max:20',
            'address'            => 'nullable|string|max:500',
            'grade'              => 'nullable|string|max:100',
            'academic_level'     => 'nullable|string|max:100',
            'monthly_fee'        => 'nullable|numeric|min:0',
            'study_start_date'   => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // تحديث بيانات المستخدم
            $student->user?->update([
                'name'        => $request->name,
                'national_id' => $request->national_id,
            ]);

            // تحديث بيانات الطالب
            $student->update([
                'birth_date' => $request->birth_date ?: null,
            ]);

            // تحديث طلب الانتساب إن وُجد
            if ($student->admission) {
                $student->admission->update([
                    'student_name'     => $request->name,
                    'parent_name'      => $request->parent_name,
                    'parent_id'        => $request->parent_national_id,
                    'parent_job'       => $request->parent_job,
                    'father_phone'     => $request->father_phone,
                    'mother_phone'     => $request->mother_phone,
                    'address'          => $request->address,
                    'grade'            => $request->grade,
                    'academic_level'   => $request->academic_level,
                    'monthly_fee'      => $request->monthly_fee ?: $student->admission->monthly_fee,
                    'study_start_date' => $request->study_start_date ?: $student->admission->study_start_date,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'تم تحديث بيانات الطالب بنجاح']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * جلب المدرسين المتاحين
     */
    public function getAvailableTeachers(Request $request)
    {
        try {
            $groupId = $request->get('group_id');

            if (! $groupId) {
                // جلب جميع المدرسين إذا لم تكن هناك مجموعة محددة
                $teachers = Teacher::with('user')
                    ->whereHas('user', function ($query) {
                        $query->where('is_active', true);
                    })
                    ->get()
                    ->map(function ($teacher) {
                        return [
                            'id'             => $teacher->id,
                            'name'           => $teacher->user->name,
                            'email'          => $teacher->user->email,
                            'specialization' => $teacher->specialization,
                            'display_name'   => $teacher->user->name . ($teacher->specialization ? ' (' . $teacher->specialization . ')' : ''),
                        ];
                    });
            } else {
                // جلب المدرسين المتاحين للمجموعة
                // يمكن تخصيص هذا المنطق حسب احتياجاتك

                // طريقة 1: جلب جميع المدرسين (يمكن تخصيصها)
                $teachers = Teacher::with('user')
                    ->whereHas('user', function ($query) {
                        $query->where('is_active', true);
                    })
                    ->get()
                    ->map(function ($teacher) {
                        return [
                            'id'             => $teacher->id,
                            'name'           => $teacher->user->name,
                            'email'          => $teacher->user->email,
                            'specialization' => $teacher->specialization,
                            'display_name'   => $teacher->user->name . ($teacher->specialization ? ' (' . $teacher->specialization . ')' : ''),
                        ];
                    });

            }

            // لوغ للتصحيح
            Log::info('Teachers loaded', [
                'group_id'       => $groupId,
                'teachers_count' => $teachers->count(),
            ]);

            return response()->json([
                'success'  => true,
                'teachers' => $teachers,
                'message'  => "تم تحميل {$teachers->count()} مدرس",
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading teachers', [
                'group_id' => $request->get('group_id'),
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage(),
            ], 500);
        }

    }

/**
 * جلب السلاسل النشطة
 */
    public function getActiveSeries()
    {
        try {
            $ft = fn($t) => $t instanceof Carbon ? $t->format('H:i') : substr((string) $t, 0, 5);

            $seriesList = LectureSeries::where('status', 'active')
                ->whereHas('lectures', fn($q) => $q->where('date', '>=', today()))
                ->with(['teacher.user', 'group', 'subject', 'days'])
                ->withCount([
                    'lectures as total_lectures',
                    'lectures as completed_lectures' => fn($q) => $q->where('status', 'completed'),
                    'lectures as remaining_lectures' => fn($q) => $q->where('date', '>=', today()),
                ])
                ->get()
                ->map(function ($s) use ($ft) {
                    $weeksCount = $s->start_date && $s->end_date
                        ? $s->start_date->diffInWeeks($s->end_date)
                        : 0;

                    return [
                        'series_id'          => $s->id,
                        'title'              => $s->title,
                        'teacher_id'         => $s->teacher_id,
                        'teacher_name'       => $s->teacher?->user?->name ?? 'غير محدد',
                        'group_id'           => $s->group_id,
                        'group_name'         => $s->group?->name ?? 'غير محدد',
                        'subject_id'         => $s->subject_id,
                        'subject_name'       => $s->subject?->name ?? '',
                        'start_time'         => $ft($s->start_time),
                        'end_time'           => $ft($s->end_time),
                        'start_date'         => $s->start_date->format('Y-m-d'),
                        'end_date'           => $s->end_date?->format('Y-m-d'),
                        'description'        => $s->description ?? '',
                        'days'               => $s->days->pluck('day_of_week')->toArray(),
                        'total_lectures'     => $s->total_lectures,
                        'completed_lectures' => $s->completed_lectures,
                        'remaining_lectures' => $s->remaining_lectures,
                        'weeks_count'        => $weeksCount,
                    ];
                });

            return response()->json(['success' => true, 'series' => $seriesList]);

        } catch (\Exception $e) {
            Log::error('Error in getActiveSeries', ['error' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب السلاسل النشطة: ' . $e->getMessage(),
                'series'  => [],
            ], 500);
        }
    }

    public function getSeriesDetails($id)
    {
        try {
            $ft = fn($t) => $t instanceof Carbon ? $t->format('H:i') : substr((string) $t, 0, 5);

            $s = LectureSeries::with(['teacher.user', 'group', 'subject', 'days'])
                ->findOrFail($id);

            $futureLectures = Lecture::where('series_id', $id)
                ->where('date', '>=', today())
                ->where('status', 'scheduled')
                ->count();

            return response()->json([
                'success' => true,
                'series'  => [
                    'id'                   => $s->id,
                    'title'                => $s->title,
                    'start_time'           => $ft($s->start_time),
                    'end_time'             => $ft($s->end_time),
                    'start_date'           => $s->start_date->format('Y-m-d'),
                    'end_date'             => $s->end_date?->format('Y-m-d'),
                    'teacher_id'           => $s->teacher_id,
                    'teacher_name'         => $s->teacher?->user?->name ?? '',
                    'group_id'             => $s->group_id,
                    'group_name'           => $s->group?->name ?? '',
                    'subject_id'           => $s->subject_id,
                    'subject_name'         => $s->subject?->name ?? '',
                    'description'          => $s->description ?? '',
                    'days'                 => $s->days->pluck('day_of_week')->toArray(),
                    'future_lectures_count' => $futureLectures,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'لم يتم العثور على السلسلة'], 404);
        }
    }

    public function updateSeries(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
            'end_date'    => 'nullable|date',
            'teacher_id'  => 'required|exists:teachers,id',
            'subject_id'  => 'nullable|exists:subjects,id',
            'description' => 'nullable|string|max:1000',
            'days'        => 'required|array|min:1',
            'days.*'      => 'in:0,1,2,3,4,5,6',
            'regenerate'  => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $series   = LectureSeries::with('days')->findOrFail($id);
            $oldDays  = $series->days->pluck('day_of_week')->sort()->values()->toArray();
            $newDays  = collect($request->days)->map(fn($d) => (string) $d)->sort()->values()->toArray();
            $daysChanged = $oldDays !== $newDays;

            $updateData = [
                'title'       => $request->title,
                'start_time'  => $request->start_time,
                'end_time'    => $request->end_time,
                'teacher_id'  => $request->teacher_id,
                'subject_id'  => $request->subject_id,
                'description' => $request->description,
            ];
            if ($request->filled('end_date')) {
                $updateData['end_date'] = $request->end_date;
            }
            $series->update($updateData);

            if ($daysChanged) {
                $series->days()->delete();
                foreach ($request->days as $day) {
                    $series->days()->create(['day_of_week' => (string) $day]);
                }
            }

            if ($daysChanged && $request->boolean('regenerate', false)) {
                Lecture::where('series_id', $id)
                    ->where('date', '>=', today())
                    ->where('status', 'scheduled')
                    ->delete();

                $series->refresh();
                if (! $series->end_date || $series->end_date->lt(today())) {
                    $series->update(['end_date' => today()->addMonths(4)]);
                    $series->refresh();
                }
                app(SeriesGenerator::class)->generateFromDate($series, today());
                $newCount = Lecture::where('series_id', $id)->where('date', '>=', today())->count();
                $message  = "تم تحديث السلسلة وإعادة توليد {$newCount} محاضرة مستقبلية";
            } else {
                $updatedCount = Lecture::where('series_id', $id)
                    ->where('date', '>=', today())
                    ->where('status', 'scheduled')
                    ->update([
                        'title'      => $request->title,
                        'start_time' => $request->start_time,
                        'end_time'   => $request->end_time,
                        'teacher_id' => $request->teacher_id,
                        'subject_id' => $request->subject_id,
                    ]);
                $message = "تم تحديث السلسلة و{$updatedCount} محاضرة مستقبلية";
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating series', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage()], 500);
        }
    }

    public function endLectureSeries($id)
    {
        try {
            DB::beginTransaction();

            $series = LectureSeries::findOrFail($id);
            $series->update(['status' => 'completed']);

            $cancelledCount = Lecture::where('series_id', $id)
                ->where('date', '>=', today())
                ->where('status', 'scheduled')
                ->update([
                    'status'               => 'cancelled',
                    'cancellation_reason'  => 'إنهاء السلسلة',
                ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "تم إنهاء السلسلة وإلغاء {$cancelledCount} محاضرة مستقبلية",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error ending series', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }



    /**
     * تحديد حرف الشعبة
     */
    private function getSectionLetter(int $sectionNumber): string
    {
        $letters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و', 'ز', 'ح', 'ط', 'ي'];
        return $letters[($sectionNumber - 1) % count($letters)];

    }

    /**
     * حساب معدل الحضور العام
     */
    private function getOverallAttendanceRate(): float
    {
        $month = now()->format('Y-m');

        // استعلام واحد بدل 3: JOIN بين attendance و lectures
        $start = now()->startOfMonth()->format('Y-m-d');
        $end   = now()->endOfMonth()->format('Y-m-d');

        $result = DB::selectOne("
            SELECT
                COUNT(DISTINCT l.id) as total_lectures,
                COUNT(DISTINCT s.id) as total_students,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count
            FROM lectures l
            CROSS JOIN students s
            LEFT JOIN attendance a ON a.lecture_id = l.id AND a.student_id = s.id
            WHERE l.date BETWEEN ? AND ?
        ", [$start, $end]);

        $expected = ($result->total_lectures ?? 0) * ($result->total_students ?? 0);
        if ($expected === 0) {
            return 0;
        }

        return round((($result->present_count ?? 0) / $expected) * 100, 2);
    }

    // ========== حضور وغياب المدرسين ==========

    public function teacherAttendance()
    {
        return view('admin.teacher-attendance');
    }

    public function getTeacherAttendanceData(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $month = $request->get('month');

        $teachers = Teacher::with(['user:id,name,national_id,is_active'])
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->get();

        $teacherIds = $teachers->pluck('id');

        if ($month) {
            [$year, $mon] = explode('-', $month);
            $workDays = $this->countWorkDays((int)$year, (int)$mon);

            // استعلام واحد بـ GROUP BY بدل تحميل كل الصفوف في PHP
            $attendanceStats = TeacherAttendance::whereYear('date', $year)
                ->whereMonth('date', $mon)
                ->whereIn('teacher_id', $teacherIds)
                ->selectRaw('teacher_id, status, COUNT(*) as cnt')
                ->groupBy('teacher_id', 'status')
                ->get()
                ->groupBy('teacher_id');

            $data = $teachers->map(function ($teacher) use ($attendanceStats, $workDays) {
                $stats    = $attendanceStats->get($teacher->id, collect());
                $getCount = fn($s) => (int) ($stats->firstWhere('status', $s)?->cnt ?? 0);
                $present  = $getCount('present');
                $late     = $getCount('late');
                return [
                    'id'          => $teacher->id,
                    'name'        => $teacher->user->name ?? '—',
                    'national_id' => $teacher->user->national_id ?? '—',
                    'present'     => $present,
                    'absent'      => $getCount('absent'),
                    'late'        => $late,
                    'excuse'      => $getCount('excuse'),
                    'work_days'   => $workDays,
                    'rate'        => $workDays > 0
                        ? round((($present + $late) / $workDays) * 100)
                        : 0,
                ];
            });

            return response()->json([
                'success'   => true,
                'mode'      => 'month',
                'data'      => $data,
                'work_days' => $workDays,
            ]);
        }

        // وضع اليوم — نجلب فقط سجلات المدرسين النشطين
        $existing = TeacherAttendance::whereDate('date', $date)
            ->whereIn('teacher_id', $teacherIds)
            ->select(['id', 'teacher_id', 'status', 'check_in_time', 'notes'])
            ->get()
            ->keyBy('teacher_id');

        $data = $teachers->map(function ($teacher) use ($existing) {
            $rec = $existing->get($teacher->id);
            return [
                'id'            => $teacher->id,
                'name'          => $teacher->user->name ?? '—',
                'national_id'   => $teacher->user->national_id ?? '—',
                'status'        => $rec?->status ?? null,
                'check_in_time' => $rec?->check_in_time ?? null,
                'notes'         => $rec?->notes ?? null,
                'attendance_id' => $rec?->id ?? null,
            ];
        });

        $stats = [
            'total'   => $teachers->count(),
            'present' => $existing->where('status', 'present')->count(),
            'absent'  => $existing->where('status', 'absent')->count(),
            'late'    => $existing->where('status', 'late')->count(),
            'excuse'  => $existing->where('status', 'excuse')->count(),
            'unmarked'=> $teachers->count() - $existing->count(),
        ];

        return response()->json(['success' => true, 'mode' => 'day', 'data' => $data, 'stats' => $stats]);
    }

    public function saveTeacherAttendance(Request $request)
    {
        $validated = $request->validate([
            'date'                   => 'required|date',
            'records'                => 'required|array',
            'records.*.teacher_id'   => 'required|exists:teachers,id',
            'records.*.status'       => 'required|in:present,absent,late,excuse',
            'records.*.check_in_time'=> 'nullable|date_format:H:i',
            'records.*.notes'        => 'nullable|string|max:500',
        ]);

        $adminId = Auth::id();
        $now     = now();
        $date    = $validated['date'];

        $rows = collect($validated['records'])->map(fn($rec) => [
            'teacher_id'     => $rec['teacher_id'],
            'date'           => $date,
            'status'         => $rec['status'],
            'check_in_time'  => $rec['check_in_time'] ?? null,
            'notes'          => $rec['notes'] ?? null,
            'recorded_by'    => $adminId,
            'created_at'     => $now,
            'updated_at'     => $now,
        ])->toArray();

        // استعلام واحد بدل N×2 استعلام
        TeacherAttendance::upsert(
            $rows,
            ['teacher_id', 'date'],
            ['status', 'check_in_time', 'notes', 'recorded_by', 'updated_at']
        );

        return response()->json(['success' => true, 'message' => 'تم حفظ سجل الحضور بنجاح']);
    }

    public function saveOneTeacherAttendance(Request $request)
    {
        $validated = $request->validate([
            'teacher_id'    => 'required|exists:teachers,id',
            'date'          => 'required|date',
            'status'        => 'required|in:present,absent,late,excuse',
            'check_in_time' => 'nullable|date_format:H:i',
            'notes'         => 'nullable|string|max:500',
        ]);

        $record = TeacherAttendance::updateOrCreate(
            ['teacher_id' => $validated['teacher_id'], 'date' => $validated['date']],
            [
                'status'        => $validated['status'],
                'check_in_time' => $validated['check_in_time'] ?? null,
                'notes'         => $validated['notes'] ?? null,
                'recorded_by'   => Auth::id(),
            ]
        );

        return response()->json([
            'success'       => true,
            'message'       => 'تم تحديث الحضور',
            'attendance_id' => $record->id,
        ]);
    }

    // أسبوع العمل: الأحد–الخميس (عطلة الجمعة والسبت)
    private function countWorkDays(int $year, int $month): int
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();
        $count = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if (!in_array($d->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY])) {
                $count++;
            }
        }
        return $count;
    }
}