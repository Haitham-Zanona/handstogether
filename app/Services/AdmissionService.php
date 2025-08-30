<?php
namespace App\Services;

use App\Jobs\SendAdmissionNotification;
use App\Models\Admission;
use App\Models\Group;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdmissionService
{
    /**
     * إنشاء طلب انتساب جديد
     */
    public function createAdmission(array $data): Admission
    {
        return DB::transaction(function () use ($data) {
            // توليد رقم الطلب إذا لم يكن موجوداً
            if (empty($data['application_number'])) {
                $data['application_number'] = Admission::generateApplicationNumber();
            }

            $admission = Admission::create($data);

            // تسجيل العملية
            Log::info('تم إنشاء طلب انتساب جديد', [
                'admission_id'       => $admission->id,
                'student_name'       => $admission->student_name,
                'application_number' => $admission->application_number,
            ]);

            return $admission;
        });
    }

    /**
     * قبول طلب الانتساب
     */
    public function approveAdmission(Admission $admission, int $groupId): bool
    {
        return DB::transaction(function () use ($admission, $groupId) {
            // التحقق من حالة الطلب
            if ($admission->status !== 'pending') {
                throw new \Exception('هذا الطلب تم معالجته مسبقاً');
            }

            $group = Group::findOrFail($groupId);

            // التحقق من سعة المجموعة
            if ($group->students_count >= $group->max_capacity) {
                throw new \Exception('المجموعة ممتلئة');
            }

            // تحديث حالة الطلب
            $admission->update([
                'status'   => 'approved',
                'group_id' => $groupId,
            ]);

            // تحديث عدد الطلاب في المجموعة
            $group->increment('students_count');

            // إنشاء سجل طالب جديد
            $student = $this->createStudentFromAdmission($admission);

            // إرسال إشعار للوالدين
            SendAdmissionNotification::dispatch($admission, 'approved');

            // تسجيل العملية
            Log::info('تم قبول طلب الانتساب', [
                'admission_id' => $admission->id,
                'student_id'   => $student->id,
                'group_name'   => $group->name,
            ]);

            return true;
        });
    }

    /**
     * رفض طلب الانتساب
     */
    public function rejectAdmission(Admission $admission, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($admission, $reason) {
            // التحقق من حالة الطلب
            if ($admission->status !== 'pending') {
                throw new \Exception('هذا الطلب تم معالجته مسبقاً');
            }

            // تحديث حالة الطلب
            $admission->update([
                'status'           => 'rejected',
                'rejection_reason' => $reason,
                'group_id'         => null,
            ]);

            // إرسال إشعار الرفض
            SendAdmissionNotification::dispatch($admission, 'rejected', $reason);

            // تسجيل العملية
            Log::info('تم رفض طلب الانتساب', [
                'admission_id' => $admission->id,
                'student_name' => $admission->student_name,
                'reason'       => $reason,
            ]);

            return true;
        });
    }

    /**
     * إنشاء سجل طالب من طلب الانتساب
     */
    private function createStudentFromAdmission(Admission $admission): Student
    {
        return Student::create([
            'admission_id'    => $admission->id,
            'name'            => $admission->student_name,
            'student_id'      => $admission->student_id,
            'birth_date'      => $admission->birth_date,
            'grade'           => $admission->grade,
            'academic_level'  => $admission->academic_level,
            'parent_name'     => $admission->parent_name,
            'parent_id'       => $admission->parent_id,
            'parent_job'      => $admission->parent_job,
            'father_phone'    => $admission->father_phone,
            'mother_phone'    => $admission->mother_phone,
            'address'         => $admission->address,
            'group_id'        => $admission->group_id,
            'monthly_fee'     => $admission->monthly_fee,
            'enrollment_date' => $admission->study_start_date,
            'status'          => 'active',
        ]);
    }

    /**
     * إرسال إشعار الموافقة
     */
    private function sendApprovalNotification(Admission $admission): void
    {
        try {
            // إرسال SMS
            if ($admission->father_phone) {
                $message = "تم قبول طلب انتساب {$admission->student_name}. مرحباً بكم في مدرستنا!";
                $this->sendSMS($admission->father_phone, $message);
            }

            if ($admission->mother_phone) {
                $message = "تم قبول طلب انتساب {$admission->student_name}. مرحباً بكم في مدرستنا!";
                $this->sendSMS($admission->mother_phone, $message);
            }

        } catch (\Exception $e) {
            Log::warning('فشل إرسال إشعار الموافقة', [
                'admission_id' => $admission->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    /**
     * إرسال إشعار الرفض
     */
    private function sendRejectionNotification(Admission $admission, ?string $reason = null): void
    {
        try {
            $message = "نأسف لإبلاغكم برفض طلب انتساب {$admission->student_name}";
            if ($reason) {
                $message .= ". السبب: {$reason}";
            }

            // إرسال SMS
            if ($admission->father_phone) {
                $this->sendSMS($admission->father_phone, $message);
            }

            if ($admission->mother_phone) {
                $this->sendSMS($admission->mother_phone, $message);
            }

        } catch (\Exception $e) {
            Log::warning('فشل إرسال إشعار الرفض', [
                'admission_id' => $admission->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    /**
     * إرسال رسالة SMS
     */
    private function sendSMS(string $phone, string $message): bool
    {
        // هنا يمكنك تنفيذ منطق إرسال SMS
        // مثال باستخدام خدمة معينة:

        try {
            // SMSGateway::send($phone, $message);

            Log::info('تم إرسال رسالة SMS', [
                'phone'   => $phone,
                'message' => $message,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('فشل إرسال SMS', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * حذف طلب انتساب (مع جميع البيانات المرتبطة)
     */
    public function deleteAdmission(Admission $admission): bool
    {
        return DB::transaction(function () use ($admission) {
            // فقط الطلبات في الانتظار يمكن حذفها
            if ($admission->status !== 'pending') {
                throw new \Exception('لا يمكن حذف هذا الطلب');
            }

            $studentName = $admission->student_name;
            $admission->delete();

            Log::info('تم حذف طلب الانتساب', [
                'student_name' => $studentName,
            ]);

            return true;
        });
    }

    /**
     * تحديث طلب انتساب
     */
    public function updateAdmission(Admission $admission, array $data): bool
    {
        return DB::transaction(function () use ($admission, $data) {
            // فقط الطلبات في الانتظار يمكن تعديلها
            if ($admission->status !== 'pending') {
                throw new \Exception('لا يمكن تعديل هذا الطلب');
            }

            $admission->update($data);

            Log::info('تم تحديث طلب الانتساب', [
                'admission_id' => $admission->id,
                'student_name' => $admission->student_name,
            ]);

            return true;
        });
    }

    /**
     * إعادة تعيين حالة طلب الانتساب
     */
    public function resetAdmissionStatus(Admission $admission): bool
    {
        return DB::transaction(function () use ($admission) {
            // إذا كان الطلب مقبولاً، نحتاج لإزالة الطالب من المجموعة
            if ($admission->status === 'approved' && $admission->group_id) {
                // تقليل عدد الطلاب في المجموعة
                Group::where('id', $admission->group_id)->decrement('students_count');

                // حذف سجل الطالب إذا كان موجوداً
                Student::where('admission_id', $admission->id)->delete();
            }

            // إعادة تعيين الحالة
            $admission->update([
                'status'           => 'pending',
                'group_id'         => null,
                'rejection_reason' => null,
            ]);

            Log::info('تم إعادة تعيين حالة طلب الانتساب', [
                'admission_id' => $admission->id,
                'student_name' => $admission->student_name,
            ]);

            return true;
        });
    }

    /**
     * الحصول على إحصائيات طلبات الانتساب
     */
    public function getStatistics(): array
    {
        return [
            'total'                   => Admission::count(),
            'pending'                 => Admission::pending()->count(),
            'approved'                => Admission::approved()->count(),
            'rejected'                => Admission::rejected()->count(),
            'this_month'              => Admission::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'this_week'               => Admission::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),
            'expired'                 => Admission::pending()
                ->where('created_at', '<', now()->subDays(30))
                ->count(),
            'average_processing_time' => $this->getAverageProcessingTime(),
            'monthly_trend'           => $this->getMonthlyTrend(),
        ];
    }

    /**
     * حساب متوسط وقت معالجة الطلبات
     */
    private function getAverageProcessingTime(): float
    {
        $processedAdmissions = Admission::whereIn('status', ['approved', 'rejected'])
            ->select('created_at', 'updated_at')
            ->get();

        if ($processedAdmissions->isEmpty()) {
            return 0;
        }

        $totalHours = $processedAdmissions->sum(function ($admission) {
            return $admission->created_at->diffInHours($admission->updated_at);
        });

        return round($totalHours / $processedAdmissions->count(), 1);
    }

    /**
     * الحصول على اتجاه الطلبات الشهري
     */
    private function getMonthlyTrend(): array
    {
        return Admission::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * البحث المتقدم في طلبات الانتساب
     */
    public function advancedSearch(array $filters): \Illuminate\Database\Eloquent\Collection
    {
        $query = Admission::query()->with('group');

        // فلتر النص
        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // فلتر الحالة
        if (! empty($filters['status'])) {
            $query->withStatus($filters['status']);
        }

        // فلتر المرحلة الدراسية
        if (! empty($filters['grade'])) {
            $query->where('grade', $filters['grade']);
        }

        // فلتر المجموعة
        if (! empty($filters['group_id'])) {
            $query->where('group_id', $filters['group_id']);
        }

        // فلتر تاريخ التقديم
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // فلتر عمر الطالب
        if (! empty($filters['age_from']) || ! empty($filters['age_to'])) {
            $ageFrom = $filters['age_from'] ?? 0;
            $ageTo   = $filters['age_to'] ?? 100;

            $dateFrom = now()->subYears($ageTo + 1)->startOfYear();
            $dateTo   = now()->subYears($ageFrom)->endOfYear();

            $query->whereBetween('birth_date', [$dateFrom, $dateTo]);
        }

        // فلتر الرسوم
        if (! empty($filters['fee_from'])) {
            $query->where('monthly_fee', '>=', $filters['fee_from']);
        }

        if (! empty($filters['fee_to'])) {
            $query->where('monthly_fee', '<=', $filters['fee_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * معالجة متعددة للطلبات
     */
    public function bulkProcess(array $admissionIds, string $action, array $options = []): array
    {
        $results = [
            'success' => 0,
            'failed'  => 0,
            'errors'  => [],
        ];

        $admissions = Admission::whereIn('id', $admissionIds)->get();

        foreach ($admissions as $admission) {
            try {
                DB::beginTransaction();

                switch ($action) {
                    case 'approve':
                        if (empty($options['group_id'])) {
                            throw new \Exception('يرجى تحديد المجموعة');
                        }
                        $this->approveAdmission($admission, $options['group_id']);
                        break;

                    case 'reject':
                        $this->rejectAdmission($admission, $options['reason'] ?? null);
                        break;

                    case 'delete':
                        $this->deleteAdmission($admission);
                        break;

                    case 'reset':
                        $this->resetAdmissionStatus($admission);
                        break;

                    default:
                        throw new \Exception('إجراء غير معروف');
                }

                DB::commit();
                $results['success']++;

            } catch (\Exception $e) {
                DB::rollBack();
                $results['failed']++;
                $results['errors'][] = [
                    'admission_id' => $admission->id,
                    'student_name' => $admission->student_name,
                    'error'        => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * تنظيف الطلبات المنتهية الصلاحية
     */
    public function cleanupExpiredAdmissions(): int
    {
        $expiredAdmissions = Admission::pending()
            ->where('created_at', '<', now()->subDays(30))
            ->get();

        $count = 0;
        foreach ($expiredAdmissions as $admission) {
            try {
                // إرسال تنبيه أخير قبل الحذف
                $this->sendExpirationNotice($admission);

                // حذف الطلب بعد 7 أيام إضافية
                if ($admission->created_at < now()->subDays(37)) {
                    $admission->delete();
                    $count++;
                }

            } catch (\Exception $e) {
                Log::warning('خطأ في تنظيف طلب منتهي الصلاحية', [
                    'admission_id' => $admission->id,
                    'error'        => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * إرسال إشعار انتهاء الصلاحية
     */
    private function sendExpirationNotice(Admission $admission): void
    {
        $message = "طلب انتساب {$admission->student_name} سينتهي خلال 7 أيام. يرجى المتابعة.";

        if ($admission->father_phone) {
            $this->sendSMS($admission->father_phone, $message);
        }
    }

    /**
     * تصدير بيانات الطلبات
     */
    public function exportAdmissions(array $filters = []): array
    {
        $admissions = $this->advancedSearch($filters);

        return $admissions->map(function ($admission) {
            return [
                'رقم الطلب'          => $admission->application_number,
                'اسم الطالب'         => $admission->student_name,
                'رقم الهوية'         => $admission->student_id,
                'تاريخ الميلاد'      => $admission->birth_date->format('Y-m-d'),
                'المرحلة الدراسية'   => $admission->grade,
                'المستوى الأكاديمي'  => $admission->academic_level,
                'اسم ولي الأمر'      => $admission->parent_name,
                'رقم هوية ولي الأمر' => $admission->parent_id,
                'المهنة'             => $admission->parent_job,
                'جوال الأب'          => $admission->father_phone,
                'جوال الأم'          => $admission->mother_phone ?? '',
                'العنوان'            => $admission->address,
                'الرسوم الشهرية'     => $admission->monthly_fee,
                'تاريخ بدء الدراسة'  => $admission->study_start_date->format('Y-m-d'),
                'المجموعة'           => $admission->group->name ?? '',
                'الحالة'             => $admission->status_in_arabic,
                'تاريخ التقديم'      => $admission->created_at->format('Y-m-d H:i'),
            ];
        })->toArray();
    }

    /**
     * إنشاء تقرير مفصل
     */
    public function generateDetailedReport(array $filters = []): array
    {
        $admissions = $this->advancedSearch($filters);
        $stats      = $this->getStatistics();

        return [
            'summary'                 => $stats,
            'admissions'              => $admissions,
            'grade_distribution'      => $this->getGradeDistribution($admissions),
            'monthly_distribution'    => $this->getMonthlyDistribution($admissions),
            'fee_statistics'          => $this->getFeeStatistics($admissions),
            'geographic_distribution' => $this->getGeographicDistribution($admissions),
        ];
    }

    /**
     * توزيع الطلبات حسب المرحلة الدراسية
     */
    private function getGradeDistribution($admissions): array
    {
        return $admissions->groupBy('grade')
            ->map(function ($group, $grade) {
                return [
                    'grade'      => $grade,
                    'count'      => $group->count(),
                    'percentage' => round(($group->count() / $admissions->count()) * 100, 1),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * التوزيع الشهري
     */
    private function getMonthlyDistribution($admissions): array
    {
        return $admissions->groupBy(function ($admission) {
            return $admission->created_at->format('Y-m');
        })
            ->map(function ($group, $month) {
                return [
                    'month' => $month,
                    'count' => $group->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * إحصائيات الرسوم
     */
    private function getFeeStatistics($admissions): array
    {
        if ($admissions->isEmpty()) {
            return [
                'average' => 0,
                'min'     => 0,
                'max'     => 0,
                'total'   => 0,
            ];
        }

        $fees = $admissions->pluck('monthly_fee');

        return [
            'average' => round($fees->avg(), 2),
            'min'     => $fees->min(),
            'max'     => $fees->max(),
            'total'   => $fees->sum(),
        ];
    }

    /**
     * التوزيع الجغرافي (حسب أول كلمة في العنوان)
     */
    private function getGeographicDistribution($admissions): array
    {
        return $admissions->groupBy(function ($admission) {
            $addressParts = explode(' ', trim($admission->address));
            return $addressParts[0] ?? 'غير محدد';
        })
            ->map(function ($group, $area) {
                return [
                    'area'  => $area,
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(10)
            ->values()
            ->toArray();
    }

    /**
     * التحقق من صحة البيانات قبل المعالجة
     */
    public function validateAdmissionData(array $data): array
    {
        $errors = [];

        // التحقق من تفرد رقم الهوية
        if (isset($data['student_id'])) {
            $existingAdmission = Admission::where('student_id', $data['student_id'])
                ->where('status', '!=', 'rejected')
                ->first();
            if ($existingAdmission) {
                $errors['student_id'] = 'رقم الهوية مسجل مسبقاً';
            }
        }

        // التحقق من تفرد رقم الطلب
        if (isset($data['application_number'])) {
            $existingNumber = Admission::where('application_number', $data['application_number'])
                ->first();
            if ($existingNumber) {
                $errors['application_number'] = 'رقم الطلب مستخدم مسبقاً';
            }
        }

        // التحقق من المنطقية الزمنية
        if (isset($data['birth_date']) && isset($data['grade'])) {
            $age              = now()->diffInYears($data['birth_date']);
            $expectedAgeRange = $this->getExpectedAgeForGrade($data['grade']);

            if ($age < $expectedAgeRange['min'] || $age > $expectedAgeRange['max']) {
                $errors['birth_date'] = "عمر الطالب غير مناسب للمرحلة الدراسية المختارة";
            }
        }

        return $errors;
    }

    /**
     * الحصول على النطاق العمري المتوقع للمرحلة
     */
    private function getExpectedAgeForGrade(string $grade): array
    {
        $ageRanges = [
            'صف أول ابتدائي'  => ['min' => 5, 'max' => 7],
            'صف ثاني ابتدائي' => ['min' => 6, 'max' => 8],
            'صف ثالث ابتدائي' => ['min' => 7, 'max' => 9],
            'صف رابع ابتدائي' => ['min' => 8, 'max' => 10],
            'صف خامس ابتدائي' => ['min' => 9, 'max' => 11],
            'صف سادس ابتدائي' => ['min' => 10, 'max' => 12],
            'صف سابع'         => ['min' => 11, 'max' => 13],
            'صف ثامن'         => ['min' => 12, 'max' => 14],
            'صف تاسع'         => ['min' => 13, 'max' => 15],
            'صف عاشر'         => ['min' => 14, 'max' => 16],
        ];

        return $ageRanges[$grade] ?? ['min' => 5, 'max' => 18];
    }
}
