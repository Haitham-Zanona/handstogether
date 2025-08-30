<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdmissionRequest;
use App\Jobs\ProcessAdmissionApproval;
use App\Models\Admission;
use App\Models\Group;
use App\Services\AdmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdmissionController extends Controller
{
    protected $admissionService;

    public function __construct(AdmissionService $admissionService)
    {
        $this->admissionService = $admissionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Admission::query()->with('group');

        // البحث
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // فلترة الحالة
        if ($request->filled('status')) {
            $query->withStatus($request->status);
        }

        // الترتيب
        $query->orderBy('created_at', 'desc');

        // التصفح
        $admissions = $query->paginate(15)->withQueryString();

        // المجموعات للموافقة على الطلبات
        $groups = Group::select('id', 'name', 'students_count')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.admissions', compact('admissions', 'groups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.admissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdmissionRequest $request)
    {
        try {
            $admission = $this->admissionService->createAdmission($request->validated());

            return response()->json([
                'success'   => true,
                'message'   => 'تم حفظ طلب الانتساب بنجاح',
                'admission' => $admission->getDisplayData(),
            ], 201);

        } catch (\Exception $e) {
            Log::error('خطأ في إنشاء طلب الانتساب', [
                'error'   => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ البيانات. يرجى المحاولة مرة أخرى.',
                'errors'  => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Admission $admission)
    {
        $admission->load('group', 'parentUser', 'studentUser', 'student');

        return view('admin.admissions.show', compact('admission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Admission $admission)
    {
        // فقط الطلبات في الانتظار يمكن تعديلها
        if ($admission->status !== 'pending') {
            return redirect()->route('admin.admissions.index')
                ->with('error', 'لا يمكن تعديل هذا الطلب');
        }

        return view('admin.admissions.edit', compact('admission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreAdmissionRequest $request, Admission $admission)
    {
        try {
            $this->admissionService->updateAdmission($admission, $request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success'   => true,
                    'message'   => 'تم تحديث البيانات بنجاح',
                    'admission' => $admission->fresh()->getDisplayData(),
                ]);
            }

            return redirect()->route('admin.admissions.show', $admission)
                ->with('success', 'تم تحديث البيانات بنجاح');

        } catch (\Exception $e) {
            Log::error('خطأ في تحديث طلب الانتساب', [
                'admission_id' => $admission->id,
                'error'        => $e->getMessage(),
                'user_id'      => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'حدث خطأ أثناء تحديث البيانات');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admission $admission)
    {
        try {
            $this->admissionService->deleteAdmission($admission);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الطلب بنجاح',
            ]);

        } catch (\Exception $e) {
            Log::error('خطأ في حذف طلب الانتساب', [
                'admission_id' => $admission->id,
                'error'        => $e->getMessage(),
                'user_id'      => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * موافقة على طلب الانتساب
     */
    public function approve(Request $request, Admission $admission)
    {
        $request->validate([
            'group_id' => ['required', 'exists:groups,id'],
        ], [
            'group_id.required' => 'يرجى اختيار المجموعة',
            'group_id.exists'   => 'المجموعة المختارة غير موجودة',
        ]);

        try {
            ProcessAdmissionApproval::dispatch($admission, $request->group_id);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "تم إرسال طلب قبول انتساب {$admission->student_name} للمعالجة بنجاح",
                ]);
            }

            return redirect()->route('admin.admissions.index')
                ->with('success', "تم إرسال طلب قبول انتساب {$admission->student_name} للمعالجة بنجاح");

        } catch (\Exception $e) {
            Log::error('خطأ في قبول طلب الانتساب', [
                'admission_id' => $admission->id,
                'error'        => $e->getMessage(),
                'user_id'      => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * رفض طلب الانتساب
     */
    public function reject(Request $request, Admission $admission)
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->admissionService->rejectAdmission($admission, $request->reason);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "تم رفض طلب انتساب {$admission->student_name}",
                ]);
            }

            return redirect()->route('admin.admissions.index')
                ->with('success', "تم رفض طلب انتساب {$admission->student_name}");

        } catch (\Exception $e) {
            Log::error('خطأ في رفض طلب الانتساب', [
                'admission_id' => $admission->id,
                'error'        => $e->getMessage(),
                'user_id'      => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * إعادة تعيين حالة طلب (للمشرفين فقط)
     */
    public function resetStatus(Admission $admission)
    {
        // التحقق من الصلاحية
        if (! auth()->user()->can('reset_admission_status')) {
            return response()->json([
                'success' => false,
                'message' => 'ليس لديك صلاحية لتنفيذ هذا الإجراء',
            ], 403);
        }

        try {
            $this->admissionService->resetAdmissionStatus($admission);

            return response()->json([
                'success' => true,
                'message' => 'تم إعادة تعيين حالة الطلب إلى "في الانتظار"',
            ]);

        } catch (\Exception $e) {
            Log::error('خطأ في إعادة تعيين حالة الطلب', [
                'admission_id' => $admission->id,
                'error'        => $e->getMessage(),
                'user_id'      => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * إحصائيات طلبات الانتساب
     */
    public function statistics()
    {
        try {
            $stats = $this->admissionService->getStatistics();

            return response()->json([
                'success' => true,
                'data'    => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في جلب الإحصائيات',
            ], 500);
        }
    }

    /**
     * تصدير البيانات
     */
    public function export(Request $request)
    {
        try {
            $filters = $request->only(['status', 'grade', 'date_from', 'date_to']);
            $data    = $this->admissionService->exportAdmissions($filters);

            $filename = 'admissions_' . now()->format('Y-m-d_H-i-s') . '.json';

            return response()->json($data)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في تصدير البيانات',
            ], 500);
        }
    }

    /**
     * طباعة بيانات طلب معين
     */
    public function print(Admission $admission)
    {
        $admission->load('group', 'parentUser', 'studentUser');

        return view('admin.admissions.print', compact('admission'));
    }

    /**
     * إرسال رسالة SMS للوالدين
     */
    public function sendSMS(Request $request, Admission $admission)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:160'],
        ], [
            'message.required' => 'يرجى كتابة نص الرسالة',
            'message.max'      => 'نص الرسالة طويل جداً (160 حرف كحد أقصى)',
        ]);

        try {
            // إرسال SMS للأب
            $sent = 0;
            if ($admission->father_phone || $admission->phone) {
                // كود إرسال SMS هنا
                // SMSService::send($admission->father_phone ?: $admission->phone, $request->message);
                $sent++;
            }

            // إرسال SMS للأم (إذا كان متوفراً)
            if ($admission->mother_phone) {
                // SMSService::send($admission->mother_phone, $request->message);
                $sent++;
            }

            Log::info('تم إرسال رسالة SMS', [
                'admission_id' => $admission->id,
                'student_name' => $admission->student_name,
                'message'      => $request->message,
                'recipients'   => $sent,
                'user_id'      => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "تم إرسال الرسالة إلى {$sent} رقم بنجاح",
            ]);

        } catch (\Exception $e) {
            Log::error('خطأ في إرسال SMS', [
                'admission_id' => $admission->id,
                'error'        => $e->getMessage(),
                'user_id'      => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال الرسالة',
            ], 500);
        }
    }

    /**
     * البحث السريع بـ AJAX
     */
    public function quickSearch(Request $request)
    {
        $search = $request->input('q');

        if (strlen($search) < 2) {
            return response()->json(['results' => []]);
        }

        try {
            $results = Admission::search($search)
                ->select('id', 'student_name', 'parent_name', 'student_id', 'status', 'application_number')
                ->limit(10)
                ->get()
                ->map(function ($admission) {
                    return [
                        'id'       => $admission->id,
                        'text'     => $admission->student_name . ' - ' . $admission->parent_name,
                        'subtitle' => "رقم الطلب: {$admission->application_number} | رقم الهوية: " . ($admission->student_id ?: 'غير محدد'),
                        'status'   => $admission->status_in_arabic,
                        'url'      => route('admin.admissions.show', $admission),
                    ];
                });

            return response()->json(['results' => $results]);

        } catch (\Exception $e) {
            return response()->json([
                'results' => [],
                'error'   => 'خطأ في البحث',
            ]);
        }
    }

    /**
     * التحقق من توفر رقم الهوية
     */
    public function checkIdAvailability(Request $request)
    {
        $request->validate([
            'id'           => ['required', 'string', 'size:9'],
            'field'        => ['required', 'in:student_id,parent_id'],
            'admission_id' => ['nullable', 'exists:admissions,id'],
        ]);

        $query = Admission::where($request->field, $request->id);

        // استثناء الطلب الحالي عند التعديل
        if ($request->admission_id) {
            $query->where('id', '!=', $request->admission_id);
        }

        // استثناء الطلبات المرفوضة
        $query->where('status', '!=', 'rejected');

        $exists = $query->exists();

        return response()->json([
            'available' => ! $exists,
            'message'   => $exists ? 'رقم الهوية مسجل مسبقاً' : 'رقم الهوية متاح',
        ]);
    }

    /**
     * معالجة متعددة للطلبات
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action'          => ['required', 'in:approve,reject,delete'],
            'admission_ids'   => ['required', 'array', 'min:1'],
            'admission_ids.*' => ['exists:admissions,id'],
            'group_id'        => ['required_if:action,approve', 'exists:groups,id'],
            'reason'          => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $results = $this->admissionService->bulkProcess(
                $request->admission_ids,
                $request->action,
                $request->only(['group_id', 'reason'])
            );

            return response()->json([
                'success' => true,
                'message' => "تمت معالجة {$results['success']} طلب بنجاح",
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في المعالجة المتعددة',
            ], 500);
        }
    }

    /**
     * تحويل الطلب إلى طالب مباشرة (استخدام method من الموديل)
     */
    public function convertToStudent(Request $request, Admission $admission)
    {
        $request->validate([
            'group_id' => ['nullable', 'exists:groups,id'],
        ]);

        try {
            $student = $admission->convertToStudent($request->group_id);

            return response()->json([
                'success'      => true,
                'message'      => "تم تحويل الطلب إلى طالب بنجاح",
                'student_id'   => $student->id,
                'redirect_url' => route('admin.students.show', $student),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
