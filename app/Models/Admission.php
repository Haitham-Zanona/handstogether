<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Admission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // بيانات الطلب الأساسية (من الموديل الأصلي)
        'student_name',
        'parent_name',
        'phone',
        'status',

        // بيانات الطلب المفصلة (من الموديل الجديد)
        'day',
        'application_date',
        'application_number',

        // بيانات الطالب التفصيلية
        'student_id',
        'birth_date',
        'grade',
        'academic_level',

        // بيانات ولي الأمر التفصيلية
        'parent_id',
        'parent_job',

        // بيانات التواصل المتقدمة
        'father_phone',
        'mother_phone',
        'address',

        // المعلومات المالية
        'monthly_fee',
        'study_start_date',
        'payment_due_from',
        'payment_due_to',

        // حالة الطلب وإدارة المجموعات
        'group_id',
        'rejection_reason',

        // معلومات المستخدمين المُنشئين
        'parent_user_id',
        'student_user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'application_date' => 'date',
        'birth_date'       => 'date',
        'study_start_date' => 'date',
        'payment_due_from' => 'date',
        'payment_due_to'   => 'date',
        'monthly_fee'      => 'decimal:2',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // إخفاء معلومات حساسة في API responses
        'student_id',
        'parent_id',
    ];

    // ================== العلاقات ==================

    /**
     * علاقة مع المجموعة
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * علاقة مع المستخدم الأب (إذا تم قبول الطلب)
     */
    public function parentUser()
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    /**
     * علاقة مع المستخدم الطالب (إذا تم قبول الطلب)
     */
    public function studentUser()
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    /**
     * علاقة مع سجل الطالب (إذا تم إنشاؤه)
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    // ================== Scopes ==================

    /**
     * طلبات في الانتظار
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * طلبات مقبولة
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * طلبات مرفوضة
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * البحث في الطلبات
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('student_name', 'like', "%{$search}%")
                ->orWhere('parent_name', 'like', "%{$search}%")
                ->orWhere('student_id', 'like', "%{$search}%")
                ->orWhere('father_phone', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%") // للتوافق مع الموديل الأصلي
                ->orWhere('application_number', 'like', "%{$search}%");
        });
    }

    /**
     * فلترة حسب الحالة
     */
    public function scopeWithStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * فلترة حسب المرحلة الدراسية
     */
    public function scopeWithGrade($query, $grade)
    {
        if ($grade) {
            return $query->where('grade', $grade);
        }
        return $query;
    }

    // ================== Attributes & Accessors ==================

    /**
     * حالة الطلب باللغة العربية
     */
    protected function statusInArabic(): Attribute
    {
        return Attribute::make(
            get: fn()  => match ($this->status) {
                'pending'  => 'في الانتظار',
                'approved' => 'مقبول',
                'rejected' => 'مرفوض',
                default    => 'غير محدد'
            }
        );
    }

    /**
     * للتوافق مع الموديل الأصلي
     */
    // public function getStatusInArabicAttribute()
    // {
    //     return $this->statusInArabic;
    // }

    /**
     * عمر الطالب
     */
    protected function studentAge(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->birth_date ?
                $this->birth_date->diffInYears(now()) : null;
            }
        );
    }

    /**
     * رقم الهاتف المرجعي (للتوافق مع الموديل الأصلي)
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn()       => $this->father_phone ?: $this->attributes['phone'] ?? null,
            set: fn($value) => [
                'phone'        => $value,
                'father_phone' => $this->father_phone ?: $value,
            ]
        );
    }

    /**
     * الاسم الكامل للطالب مع التنسيق
     */
    protected function formattedStudentName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim($this->student_name)
        );
    }

    /**
     * الاسم الكامل لولي الأمر مع التنسيق
     */
    protected function formattedParentName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim($this->parent_name)
        );
    }

    // ================== Methods ==================

    /**
     * توليد رقم طلب تلقائي
     */
    public static function generateApplicationNumber(): string
    {
        $prefix     = 'ADM';
        $year       = now()->year;
        $lastNumber = self::whereYear('created_at', $year)
            ->where('application_number', 'like', "{$prefix}{$year}%")
            ->count();

        $newNumber = $lastNumber + 1;

        return $prefix . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * تحويل الطلب إلى طالب ومستخدم (من الموديل الأصلي مع تحسينات)
     */
    public function convertToStudent($groupId = null)
    {
        if ($this->status !== 'pending') {
            throw new \Exception('يمكن قبول الطلبات في الانتظار فقط');
        }

        DB::transaction(function () use ($groupId) {
            // إنشاء مستخدم ولي الأمر
            $parentEmail = $this->generateUniqueEmail($this->parent_name, 'parent');
            $parent      = User::create([
                'name'        => $this->formatted_parent_name,
                'email'       => $parentEmail,
                'password'    => Hash::make('123456'), // كلمة مرور افتراضية
                'role'        => 'parent',
                'phone'       => $this->father_phone ?: $this->phone,
                'national_id' => $this->parent_id,
            ]);

            // إنشاء مستخدم الطالب
            $studentEmail = $this->generateUniqueEmail($this->student_name, 'student');
            $studentUser  = User::create([
                'name'        => $this->formatted_student_name,
                'email'       => $studentEmail,
                'password'    => Hash::make('123456'), // كلمة مرور افتراضية
                'role'        => 'student',
                'national_id' => $this->student_id,
                'birth_date'  => $this->birth_date,
                'parent_id'   => $parent->id,
            ]);

            // إنشاء سجل الطالب
            $student = Student::create([
                'admission_id'    => $this->id,
                'user_id'         => $studentUser->id,
                'parent_id'       => $parent->id,
                'group_id'        => $groupId,
                'name'            => $this->formatted_student_name,
                'student_id'      => $this->student_id,
                'birth_date'      => $this->birth_date,
                'grade'           => $this->grade,
                'academic_level'  => $this->academic_level,
                'monthly_fee'     => $this->monthly_fee,
                'enrollment_date' => $this->study_start_date ?: now(),
                'status'          => 'active',
            ]);

            // تحديث عدد الطلاب في المجموعة إذا تم تحديدها
            if ($groupId) {
                Group::where('id', $groupId)->increment('students_count');
            }

            // تحديث حالة الطلب وربط المستخدمين
            $this->update([
                'status'          => 'approved',
                'group_id'        => $groupId,
                'parent_user_id'  => $parent->id,
                'student_user_id' => $studentUser->id,
            ]);

            return $student;
        });
    }

    /**
     * توليد ايميل فريد للمستخدم
     */
    private function generateUniqueEmail(string $name, string $type): string
    {
        $baseName  = Str::slug($name, '');
        $baseEmail = strtolower($baseName) . '@academy.local';

        $counter = 1;
        $email   = $baseEmail;

        while (User::where('email', $email)->exists()) {
            $email = strtolower($baseName) . $counter . '@academy.local';
            $counter++;
        }

        return $email;
    }

    /**
     * رفض الطلب مع سبب
     */
    public function reject(string $reason = null): bool
    {
        if ($this->status !== 'pending') {
            throw new \Exception('يمكن رفض الطلبات في الانتظار فقط');
        }

        return $this->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
            'group_id'         => null,
        ]);
    }

    /**
     * إعادة تعيين حالة الطلب
     */
    public function resetStatus(): bool
    {
        DB::transaction(function () {
            // إذا كان الطلب مقبولاً، نحتاج لإزالة البيانات المرتبطة
            if ($this->status === 'approved') {
                // تقليل عدد الطلاب في المجموعة
                if ($this->group_id) {
                    Group::where('id', $this->group_id)->decrement('students_count');
                }

                // حذف سجل الطالب
                Student::where('admission_id', $this->id)->delete();

                // يمكن اختيارياً حذف مستخدمي الطالب وولي الأمر
                // أو تعطيل حساباتهم بدلاً من الحذف
                if ($this->parent_user_id) {
                    User::where('id', $this->parent_user_id)->update(['is_active' => false]);
                }
                if ($this->student_user_id) {
                    User::where('id', $this->student_user_id)->update(['is_active' => false]);
                }
            }

            // إعادة تعيين الحالة
            $this->update([
                'status'           => 'pending',
                'group_id'         => null,
                'rejection_reason' => null,
                'parent_user_id'   => null,
                'student_user_id'  => null,
            ]);
        });

        return true;
    }

    /**
     * التحقق من انتهاء صلاحية الطلب
     */
    public function isExpired(): bool
    {
        return $this->status === 'pending' &&
        $this->created_at->addDays(30)->isPast();
    }

    /**
     * حساب المبلغ المستحق
     */
    public function getDueAmount(): float
    {
        if (! $this->study_start_date || ! $this->payment_due_from || ! $this->payment_due_to) {
            return 0;
        }

        $startDate = max($this->study_start_date, $this->payment_due_from);
        $endDate   = min(now(), $this->payment_due_to);

        if ($startDate > $endDate) {
            return 0;
        }

        $months = $startDate->diffInMonths($endDate) + 1;
        return (float) $this->monthly_fee * $months;
    }

    /**
     * التحقق من صحة البيانات المطلوبة للقبول
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending' &&
        ! empty($this->student_name) &&
        ! empty($this->parent_name) &&
        ! empty($this->student_id) &&
        strlen($this->student_id) === 9;
    }

    /**
     * الحصول على تفاصيل الطلب للعرض
     */
    public function getDisplayData(): array
    {
        return [
            'id'                 => $this->id,
            'application_number' => $this->application_number,
            'student_name'       => $this->formatted_student_name,
            'parent_name'        => $this->formatted_parent_name,
            'phone'              => $this->phone,
            'status'             => $this->status,
            'status_arabic'      => $this->status_in_arabic,
            'grade'              => $this->grade,
            'age'                => $this->student_age,
            'group_name'         => $this->group?->name,
            'created_date'       => $this->created_at?->format('Y-m-d'),
            'can_be_approved'    => $this->canBeApproved(),
            'is_expired'         => $this->isExpired(),
        ];
    }

    // ================== Boot Method ==================

    /**
     * Boot method لتوليد رقم الطلب تلقائياً
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($admission) {
            // توليد رقم الطلب إذا لم يكن موجوداً
            if (empty($admission->application_number)) {
                $admission->application_number = self::generateApplicationNumber();
            }

            // تعيين قيم افتراضية للتوافق
            if (empty($admission->phone) && ! empty($admission->father_phone)) {
                $admission->phone = $admission->father_phone;
            }

            if (empty($admission->application_date)) {
                $admission->application_date = now();
            }
        });

        static::updating(function ($admission) {
            // تحديث رقم الهاتف المرجعي عند التغيير
            if ($admission->isDirty('father_phone') && empty($admission->phone)) {
                $admission->phone = $admission->father_phone;
            }
        });
    }
}
