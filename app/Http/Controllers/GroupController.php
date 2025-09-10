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

        // وإلا أرجع الواجهة الجديدة
        return view('admin.groups.index');
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
            if ($group->students_count > 0) {
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

            // حذف العلاقات أولاً
            $group->subjects()->detach();
            $group->students()->detach();

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

    // ======================== وظائف إدارة الطلاب الجديدة ========================

    /**
     * جلب طلاب المجموعة
     */
    public function getGroupStudents(Group $group)
    {
        try {
            $students = $group->students()->with('user')->get()->map(function ($student) {
                return [
                    'id'               => $student->id,
                    'name'             => $student->user->name ?? 'غير محدد',
                    'email'            => $student->user->email ?? '',
                    'student_id'       => $student->student_id,
                    'phone'            => $student->phone,
                    'birth_date'       => $student->birth_date,
                    'address'          => $student->address,
                    'enrollment_date'  => $student->created_at->format('Y-m-d'),
                    'is_active'        => $student->is_active,
                    'pivot_created_at' => $student->pivot->created_at ?? null,
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

            // التحقق من وجود الطالب في المجموعة الحالية
            if (! $fromGroup->students()->where('student_id', $student->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب غير موجود في هذه المجموعة',
                ], 400);
            }

            DB::beginTransaction();

            // إزالة الطالب من المجموعة الحالية
            $fromGroup->students()->detach($student->id);
            $fromGroup->removeStudent();

            // إضافة الطالب للمجموعة الجديدة
            $targetGroup->students()->attach($student->id, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $targetGroup->addStudent();

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
            // التحقق من وجود الطالب في المجموعة
            if (! $group->students()->where('student_id', $student->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب غير موجود في هذه المجموعة',
                ], 400);
            }

            DB::beginTransaction();

            // إزالة الطالب من المجموعة
            $group->students()->detach($student->id);
            $group->removeStudent();

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
     * البحث السريع عن الطلاب
     */
    public function quickSearchStudents(Request $request)
    {
        $search         = $request->get('q', '');
        $excludeGroupId = $request->get('exclude_group_id');

        if (strlen($search) < 2) {
            return response()->json([
                'success'  => true,
                'students' => [],
            ]);
        }

        try {
            $query = Student::with('user')
                ->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                })
                ->orWhere('student_id', 'like', '%' . $search . '%')
                ->orWhere('phone', 'like', '%' . $search . '%');

            // استبعاد الطلاب المسجلين في مجموعة معينة
            if ($excludeGroupId) {
                $query->whereDoesntHave('groups', function ($q) use ($excludeGroupId) {
                    $q->where('group_id', $excludeGroupId);
                });
            }

            $students = $query->limit(20)->get()->map(function ($student) {
                return [
                    'id'         => $student->id,
                    'name'       => $student->user->name ?? 'غير محدد',
                    'student_id' => $student->student_id,
                    'phone'      => $student->phone,
                    'email'      => $student->user->email ?? '',
                    'avatar'     => $student->user->avatar ?? null,
                ];
            });

            return response()->json([
                'success'  => true,
                'students' => $students,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في البحث: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * إضافة طالب للمجموعة
     */
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

            // التحقق من إمكانية إضافة الطالب
            if (! $group->can_add_students) {
                return response()->json([
                    'success' => false,
                    'message' => 'المجموعة ممتلئة أو غير نشطة',
                ], 400);
            }

            // التحقق من عدم وجود الطالب في المجموعة مسبقاً
            if ($group->students()->where('student_id', $student->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'الطالب مسجل في هذه المجموعة مسبقاً',
                ], 400);
            }

            DB::beginTransaction();

            // إضافة الطالب للمجموعة
            $group->students()->attach($student->id, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // تحديث عدد الطلاب
            $group->increment('students_count');

            DB::commit();

            // إرجاع بيانات الطالب المضاف
            $studentData = [
                'id'              => $student->id,
                'name'            => $student->user->name ?? 'غير محدد',
                'email'           => $student->user->email ?? '',
                'student_id'      => $student->student_id,
                'phone'           => $student->phone,
                'birth_date'      => $student->birth_date,
                'address'         => $student->address,
                'enrollment_date' => now()->format('Y-m-d'),
                'is_active'       => $student->is_active,
            ];

            return response()->json([
                'success'   => true,
                'message'   => 'تم إضافة الطالب للمجموعة بنجاح',
                'student'   => $studentData,
                'new_count' => $group->fresh()->students_count,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إضافة الطالب: ' . $e->getMessage(),
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

    // ======================== بقية الوظائف الموجودة ========================

    public function approveAdmission(Request $request, Admission $admission)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        try {
            $student = $admission->convertToStudent($request->group_id);
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
        NotificationService::notifyLowAttendance(75);

        $lowAttendanceCount = Student::get()->filter(function ($student) {
            return $student->getAttendancePercentage() < 75;
        })->count();

        return back()->with('success', "تم إرسال تنبيهات نسبة الحضور المنخفض لـ {$lowAttendanceCount} ولي أمر");
    }

    // ======================== وظائف مساعدة ========================

    /**
     * تحديد حرف الشعبة
     */
    private function getSectionLetter($sectionNumber)
    {
        $letters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و', 'ز', 'ح', 'ط', 'ي', 'ك', 'ل', 'م', 'ن'];
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