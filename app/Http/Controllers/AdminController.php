<?php
namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Attendance;
use App\Models\Group;
use App\Models\GroupSubject;
use App\Models\Lecture;
use App\Models\LectureSeries;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\NotificationService;
use App\Services\SeriesGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // ======================== الوظائف الموجودة (بدون تغيير) ========================

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

    // ======================== إدارة المجموعات المحسنة ========================

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
        try {
            $groups = Group::with(['students', 'teachers', 'subjects'])
                ->withCount('students')
                ->get()
                ->map(function ($group) {
                    return [
                        'id'                   => $group->id,
                        'name'                 => $group->name,
                        'grade_level'          => $group->grade_level,
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
                        'teachers'             => $group->teachers->map(function ($teacher) {
                            return [
                                'id'    => $teacher->id,
                                'name'  => $teacher->user->name ?? 'غير محدد',
                                'email' => $teacher->user->email ?? '',
                            ];
                        }),
                        'subjects_count'       => $group->subjects->count(),
                        'today_lectures'       => $group->getTodayLectures()->count(),
                    ];
                });

            // إحصائيات عامة
            $stats = [
                'total_groups'     => $groups->count(),
                'total_students'   => $groups->sum('students_count'),
                'full_groups'      => $groups->where('students_count', '>=', function ($group) {
                    return $group['max_capacity'];
                })->count(),
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

            // ✅ حذف العلاقات المتبقية فقط
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
                        // ✅ التصحيح: استخدام 'grade' بدلاً من 'grade_level'
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
                    // ✅ التصحيح: استخدام 'grade' من admission
                    'grade_level'    => $student->admission?->grade ?? 'مضاف مباشرة',
                    'source'         => $student->admission ? 'طلب انتساب' : 'إضافة مباشرة',
                    'admission_date' => $student->admission?->created_at?->format('Y-m-d'),
                    // ✅ التصحيح في display_name أيضاً
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

    // ======================== وظائف إدارة الطلاب الجديدة ========================

    /**
     * جلب طلاب المجموعة
     */
    public function getGroupStudents(Group $group)
    {
        try {
            // ✅ استخدام hasMany بدلاً من belongsToMany
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

            // ✅ التحقق من وجود الطالب في المجموعة الحالية
            if ($student->group_id !== $fromGroup->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب غير موجود في هذه المجموعة',
                ], 400);
            }

            DB::beginTransaction();

            // ✅ استخدام دالة transferStudentTo من النموذج
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
            // ✅ التحقق من وجود الطالب في المجموعة
            if ($student->group_id !== $group->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب غير موجود في هذه المجموعة',
                ], 400);
            }

            DB::beginTransaction();

            // ✅ استخدام دالة removeStudent من النموذج
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

    /**
     * جلب مواد المجموعة للمحاضرات (فقط المواد النشطة المرتبطة بالمجموعة)
     */
    public function getGroupSubjectsForLectures(Request $request)
    {
        try {
            $groupId = $request->get('group_id');

            if (! $groupId) {
                return response()->json([
                    'success' => false,
                    'message' => 'معرف المجموعة مطلوب',
                ]);
            }

            // جلب المواد المرتبطة بالمجموعة من جدول الربط
            $subjects = DB::table('group_subjects')
                ->join('subjects', 'group_subjects.subject_id', '=', 'subjects.id')
                ->where('group_subjects.group_id', $groupId)
                ->where('group_subjects.is_active', true)
                ->select(
                    'subjects.id',
                    'subjects.name',
                    'subjects.name as display_name',
                    'subjects.description'
                )
                ->get();

            // لوغ للتصحيح
            Log::info('Group subjects loaded', [
                'group_id'       => $groupId,
                'subjects_count' => $subjects->count(),
            ]);

            return response()->json([
                'success'  => true,
                'subjects' => $subjects,
                'message'  => "تم تحميل {$subjects->count()} مادة للمجموعة",
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
            $groupId = $request->get('group_id');

            if (! $groupId) {
                return response()->json([
                    'success' => false,
                    'message' => 'معرف المجموعة مطلوب',
                ]);
            }

            // جلب المواد المرتبطة بالمجموعة من جدول الربط
            $subjects = DB::table('group_subjects')
                ->join('subjects', 'group_subjects.subject_id', '=', 'subjects.id')
                ->where('group_subjects.group_id', $groupId)
                ->where('group_subjects.is_active', true)
                ->select(
                    'subjects.id',
                    'subjects.name',
                    'subjects.name as display_name',
                    'subjects.description'
                )
                ->get();

            Log::info('Group subjects loaded', [
                'group_id'       => $groupId,
                'subjects_count' => $subjects->count(),
            ]);

            return response()->json([
                'success'  => true,
                'subjects' => $subjects,
                'message'  => "تم تحميل {$subjects->count()} مادة للمجموعة",
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

    // ======================== بقية الوظائف الموجودة ========================

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

    private function createNewSectionForGrade($gradeLevel)
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

        return back()->with('success', 'تم حفظ الإعدادات بنجاح');
    }

    public function clearData(Request $request)
    {
        try {
            DB::transaction(function () {
                Attendance::truncate();
                Lecture::truncate();
                Payment::truncate();
                Student::truncate();
                Teacher::truncate();
                Admission::truncate();
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
                Attendance::truncate();
                Lecture::truncate();
                Payment::truncate();
                Student::truncate();
                Teacher::truncate();
                Admission::truncate();

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
            'total_expected' => Student::count() * 1000,
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
            'payment_method' => 'cash',
        ]);

        NotificationService::notifyPaymentReceived($payment);

        return back()->with('success', 'تم تحديث حالة الدفع وإرسال إشعار لولي الأمر');
    }

    // public function createLecture(Request $request)
    // {
    //     $request->validate([
    //         'title'       => 'required|string|max:255',
    //         'date'        => 'required|date|after_or_equal:today',
    //         'start_time'  => 'required|date_format:H:i',
    //         'end_time'    => 'required|date_format:H:i|after:start_time',
    //         'teacher_id'  => 'required|exists:teachers,id',
    //         'group_id'    => 'required|exists:groups,id',
    //         'description' => 'nullable|string|max:1000',
    //     ]);

    //     $lecture = Lecture::create($request->all());

    //     NotificationService::notifyNewLecture($lecture);

    //     return back()->with('success', 'تم إضافة المحاضرة وإرسال إشعارات للطلاب وأولياء الأمور');
    // }

    // ========== إدارة المحاضرات والجدولة ==========

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
            $view  = $request->get('view', 'month');

            // ✅ التحقق من وجود محاضرات أولاً
            $lecturesCount = Lecture::count();
            if ($lecturesCount === 0) {
                return response()->json([]);
            }

            $query = Lecture::with(['teacher.user', 'group', 'subject']);

            if ($start && $end) {
                $query->whereBetween('date', [$start, $end]);
            }

            $lectures = $query->get()->map(function ($lecture) {
                // ✅ معالجة آمنة للأوقات
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
                    // ✅ إصلاح ألوان ثابتة بدلاً من دوال مفقودة
                    'backgroundColor' => $this->getEventColor($lecture->type ?? 'lecture'),
                    'borderColor'     => $this->getEventBorderColor($lecture->status ?? 'scheduled'),
                    'extendedProps'   => [
                        'type'           => $lecture->type ?? 'lecture',
                        'status'         => $lecture->status ?? 'scheduled',
                        'teacher_name'   => $lecture->teacher && $lecture->teacher->user ? $lecture->teacher->user->name : 'غير محدد',
                        'group_name'     => $lecture->group ? $lecture->group->name : 'غير محدد',
                        'subject_name'   => $lecture->subject ? $lecture->subject->name : '',
                        'students_count' => $lecture->group ? $lecture->group->students_count : 0,
                        'series_id'      => $lecture->series_id ?? null,
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

            // ✅ إرجاع مصفوفة فارغة بدلاً من 500
            return response()->json([]);
        }

        // return \App\Http\Resources\LectureResourceCalendar::fetchFiltered($request);

    }

    /**
     * API بيانات تقويم الـ Dashboard المحسن - النسخة النهائية
     */
    public function getDashboardCalendarData(Request $request)
    {
        try {
            $start = $request->get('start');
            $end   = $request->get('end');

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
            } catch (\Exception $e) {
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
    private function formatLectureTime($time)
    {
        if (! $time) {
            return '09:00';
        }

// إذا كان datetime كامل
        if (is_string($time) && strlen($time) > 8) {
            try {
                $carbonTime = \Carbon\Carbon::parse($time);
                return $carbonTime->format('H:i');
            } catch (\Exception $e) {
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
 * تحديد أولوية المحاضرة للـ Dashboard
 */
    private function getDashboardLecturePriority($lecture)
    {
        $now             = now();
        $lectureDateTime = \Carbon\Carbon::parse($lecture->date . ' ' . $lecture->start_time);

        // محاضرة في نفس اليوم
        if ($lectureDateTime->isToday()) {
            return 'high';
        }

        // محاضرة غداً
        if ($lectureDateTime->isTomorrow()) {
            return 'medium';
        }

        // محاضرة خلال الأسبوع
        if ($lectureDateTime->diffInDays($now) <= 7) {
            return 'normal';
        }

        return 'low';
    }

/**
 * الحصول على لون المادة للـ Dashboard
 */
    private function getDashboardSubjectColor($subject)
    {
        $colors = [
            'رياضيات' => '#3b82f6',
            'فيزياء'  => '#ef4444',
            'كيمياء'  => '#10b981',
            'عربي'    => '#f59e0b',
            'إنجليزي' => '#8b5cf6',
            'تاريخ'   => '#06b6d4',
            'جغرافيا' => '#84cc16',
            'أحياء'   => '#f97316',
            'علوم'    => '#ec4899',
            'حاسوب'   => '#f97316',
            'إسلامية' => '#10b981',
            'فرنسي'   => '#ec4899',
            'default' => '#6366f1',
        ];

        return $colors[$subject] ?? $colors['default'];
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
                    'total'    => Lecture::whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$thisMonth])->count(),
                    'teachers' => Lecture::whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$thisMonth])
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

    private function getEventColor($type)
    {
        return match ($type) {
            'final_exam' => '#DC3545',
            'exam'       => '#EE8100',
            'review'     => '#28A745',
            'activity'   => '#FFC107',
            default      => '#2778E5'
        };
    }

    private function getEventBorderColor($status)
    {
        return match ($status) {
            'cancelled'   => '#6C757D',
            'rescheduled' => '#6F42C1',
            'completed'   => '#198754',
            default       => '#2778E5'
        };
    }

/**
 * جلب بيانات المحاضرات للجدول
 */
    public function getLecturesData(Request $request)
    {
        try {
            $lectures = Lecture::with(['teacher.user', 'group', 'subject'])
                ->when($request->get('date_from'), function ($query) use ($request) {
                    return $query->whereDate('date', '>=', $request->get('date_from'));
                })
                ->when($request->get('date_to'), function ($query) use ($request) {
                    return $query->whereDate('date', '<=', $request->get('date_to'));
                })
                ->when($request->get('group_id'), function ($query) use ($request) {
                    return $query->where('group_id', $request->get('group_id'));
                })
                ->when($request->get('teacher_id'), function ($query) use ($request) {
                    return $query->where('teacher_id', $request->get('teacher_id'));
                })
                ->orderBy('date', 'desc')
                ->orderBy('start_time', 'asc')
                ->get()
                ->map(function ($lecture) {
                    return [
                        'id'               => $lecture->id,
                        'title'            => $lecture->title,
                        'date'             => $lecture->date->format('Y-m-d'),
                        // ✅ إصلاح معالجة الوقت
                        'start_time'       => $lecture->start_time ?
                        (is_string($lecture->start_time) ?
                            $lecture->start_time :
                            $lecture->start_time->format('H:i')) : '00:00',
                        'end_time'         => $lecture->end_time ?
                        (is_string($lecture->end_time) ?
                            $lecture->end_time :
                            $lecture->end_time->format('H:i')) : '23:59',
                        // ✅ إصلاح معالجة العلاقات
                        'teacher_name'     => $lecture->teacher && $lecture->teacher->user ?
                        $lecture->teacher->user->name : 'غير محدد',
                        'group_name'       => $lecture->group ? $lecture->group->name : 'غير محدد',
                        'subject_name'     => $lecture->subject ? $lecture->subject->name : '',
                        'students_count'   => $lecture->group ? $lecture->group->students_count : 0,
                        'status'           => $lecture->status ?? 'scheduled',
                        'type'             => $lecture->type ?? 'lecture',
                        'series_id'        => $lecture->series_id ?? null,
                        'description'      => $lecture->description,
                        'attendance_count' => $lecture->attendance()->where('status', 'present')->count(),
                    ];
                });

            return response()->json([
                'success'  => true,
                'lectures' => $lectures,
            ]);

        } catch (\Exception $e) {
            // ✅ إضافة لوغ للتصحيح
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

            // ✅ التعديل هنا: تحديد المدة لـ 4 أشهر بدلاً من سنة
            $startDate = Carbon::parse($request->start_date);
            $endDate   = $request->end_date ? Carbon::parse($request->end_date) : $startDate->copy()->addMonths(4); // 4 أشهر بدلاً من 52 أسبوع

            // إنشاء السلسلة
            $series = LectureSeries::create([
                'title'      => $request->title,
                'start_date' => $startDate,
                'end_date'   => $endDate, // ✅ الآن محدود بـ 4 أشهر
                'start_time' => $request->start_time,
                'end_time'   => $request->end_time,
                'teacher_id' => $request->teacher_id,
                'group_id'   => $request->group_id,
                'subject_id' => $request->subject_id,
            ]);

            // إضافة أيام الأسبوع للسلسلة
            foreach ($request->days as $day) {
                $series->days()->create(['day_of_week' => $day]);
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

// ========== وظائف مساعدة ==========

/**
 * التحقق من تضارب الأوقات
 */
    private function checkTimeConflicts($teacherId, $date, $startTime, $endTime, $excludeLectureId = null)
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
 * تحديد لون المحاضرة حسب النوع
 */
    private function getLectureColor($lecture)
    {
        switch ($lecture->type) {
            case 'final_exam':
                return '#DC3545'; // أحمر للامتحانات النهائية
            case 'exam':
                return '#EE8100'; // برتقالي للامتحانات
            case 'review':
                return '#28A745'; // أخضر للمراجعة
            case 'activity':
                return '#FFC107'; // أصفر للأنشطة
            default:
                return '#2778E5'; // أزرق للمحاضرات العادية
        }
    }

/**
 * تحديد لون حدود المحاضرة حسب الحالة
 */
    private function getLectureBorderColor($lecture)
    {
        switch ($lecture->status) {
            case 'cancelled':
                return '#6C757D'; // رمادي للملغاة
            case 'rescheduled':
                return '#6F42C1'; // بنفسجي للمؤجلة
            case 'completed':
                return '#198754'; // أخضر داكن للمكتملة
            default:
                return $this->getLectureColor($lecture);
        }
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

                // طريقة 2: جلب المدرسين بناء على المواد المرتبطة بالمجموعة (إذا كنت تريد تخصيص أكثر)
                /*
            $teachers = DB::table('teachers')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->join('group_subjects', function($join) use ($groupId) {
                    $join->on('teachers.specialization_subject_id', '=', 'group_subjects.subject_id')
                         ->where('group_subjects.group_id', '=', $groupId);
                })
                ->where('users.is_active', true)
                ->select(
                    'teachers.id',
                    'users.name',
                    'users.email',
                    'teachers.specialization',
                    DB::raw('CONCAT(users.name, CASE WHEN teachers.specialization IS NOT NULL THEN CONCAT(" (", teachers.specialization, ")") ELSE "" END) as display_name')
                )
                ->distinct()
                ->get();
            */
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
            // ✅ استخدام whereNotNull بدلاً من scope قد لا يكون موجود
            $series = Lecture::whereNotNull('series_id')
                ->where(function ($query) {
                    // السلاسل النشطة = لها محاضرات قادمة أو غير مكتملة
                    $query->where('date', '>=', today())
                        ->orWhere('status', '!=', 'completed');
                })
                ->with(['teacher.user', 'group', 'subject'])
                ->select('series_id', 'title', 'teacher_id', 'group_id', 'subject_id', 'date')
                ->distinct('series_id')
                ->get()
                ->map(function ($lecture) {
                    $seriesLectures = Lecture::where('series_id', $lecture->series_id)->get();

                    // ✅ معالجة آمنة للتواريخ
                    $dates      = $seriesLectures->pluck('date')->filter();
                    $weeksCount = 0;
                    if ($dates->count() > 1) {
                        $maxDate = $dates->max();
                        $minDate = $dates->min();
                        if ($maxDate && $minDate) {
                            $weeksCount = \Carbon\Carbon::parse($maxDate)->diffInWeeks(\Carbon\Carbon::parse($minDate));
                        }
                    }

                    return [
                        'series_id'          => $lecture->series_id,
                        'title'              => $lecture->title ?? 'سلسلة بدون عنوان',
                        // ✅ معالجة آمنة للعلاقات
                        'teacher_name'       => $lecture->teacher && $lecture->teacher->user
                            ? $lecture->teacher->user->name
                            : 'غير محدد',
                        'group_name'         => $lecture->group ? $lecture->group->name : 'غير محدد',
                        'subject_name'       => $lecture->subject ? $lecture->subject->name : '',
                        'total_lectures'     => $seriesLectures->count(),
                        'completed_lectures' => $seriesLectures->where('status', 'completed')->count(),
                        'remaining_lectures' => $seriesLectures->where('date', '>', today())->count(),
                        'weeks_count'        => $weeksCount,
                    ];
                });

            return response()->json([
                'success' => true,
                'series'  => $series,
                'count'   => $series->count(), // ✅ إضافة عدد للتصحيح
            ]);

        } catch (\Exception $e) {
            // ✅ إضافة تفاصيل أكثر للتصحيح
            Log::error('Error in getActiveSeries', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب السلاسل النشطة: ' . $e->getMessage(),
                'series'  => [], // ✅ إرجاع مصفوفة فارغة
            ], 500);
        }
    }

    // ======================== وظائف مساعدة ========================

    /**
     * تحديد حرف الشعبة
     */
    private function getSectionLetter($sectionNumber)
    {
        $letters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و', 'ز', 'ح', 'ط', 'ي'];
        return $letters[($sectionNumber - 1) % count($letters)];

    }

    /**
     * حساب معدل الحضور العام
     */
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