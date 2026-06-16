<?php
namespace App\Models;

use Carbon\Carbon;
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
        'day',
        'application_date',
        'application_number',
        'student_name',
        'student_id',
        'birth_date',
        'grade',
        'academic_level',
        'parent_name',
        'parent_id',
        'parent_job',
        'phone',
        'father_phone',
        'mother_phone',
        'address',
        'monthly_fee',
        'num_payments',
        'study_start_date',
        'status',
        'group_id',
        'rejection_reason',
        'approved_at',
        'approved_by',
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

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function parentUser()
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    public function studentUser()
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('student_name', 'LIKE', "%{$search}%")
                ->orWhere('parent_name', 'LIKE', "%{$search}%")
                ->orWhere('application_number', 'LIKE', "%{$search}%")
                ->orWhere('student_id', 'LIKE', "%{$search}%");

        });
    }

    public function scopeWithStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeWithGrade($query, $grade)
    {
        if ($grade) {
            return $query->where('grade', $grade);
        }
        return $query;
    }

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

    protected function studentAge(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->birth_date ?
                $this->birth_date->diffInYears(now()) : null;
            }
        );
    }

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

    protected function formattedStudentName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim($this->student_name)
        );
    }

    protected function formattedParentName(): Attribute
    {
        return Attribute::make(
            get: fn() => trim($this->parent_name)
        );
    }

    public static function generateApplicationNumber(): string
    {
        $regexOp = \DB::getDriverName() === 'pgsql' ? '~' : 'REGEXP';
        $lastNumber = self::whereRaw("application_number $regexOp '^[0-9]{4}$'")
            ->max('application_number');

        $nextNumber = $lastNumber === null ? 0 : (intval($lastNumber) + 1);

        if ($nextNumber > 9999) {
            throw new \Exception('تم الوصول إلى الحد الأقصى لأرقام الطلبات (9999). يرجى التواصل مع الدعم الفني.');
        }

        return str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function convertToStudent(?int $groupId = null, ?int $approvedBy = null): Student
    {
        if ($this->status !== 'pending') {
            throw new \Exception('يمكن قبول الطلبات في الانتظار فقط');
        }

        return DB::transaction(function () use ($groupId, $approvedBy) {
            $parentEmail = $this->generateUniqueEmail($this->parent_name, 'parent');
            $parent      = User::create([
                'name'              => $this->formatted_parent_name,
                'email'             => $parentEmail,
                'password'          => Hash::make($this->father_phone ?: $this->phone),
                'role'              => 'parent',
                'phone'             => $this->father_phone ?: $this->phone,
                'national_id'       => $this->parent_id,
                'email_verified_at' => now(),
            ]);

            $studentEmail = $this->generateUniqueEmail($this->student_name, 'student');
            $studentUser  = User::create([
                'name'              => $this->formatted_student_name,
                'email'             => $studentEmail,
                'password'          => Hash::make($this->application_number),
                'role'              => 'student',
                'national_id'       => $this->student_id,
                'birth_date'        => $this->birth_date,
                'email_verified_at' => now(),
            ]);
            // parent_id is not in $fillable to prevent mass-assignment via forms
            $studentUser->forceFill(['parent_id' => $parent->id])->save();

            $student = Student::create([
                'admission_id' => $this->id,
                'user_id'      => $studentUser->id,
                'parent_id'    => $parent->id,
                'group_id'     => $groupId,
                'birth_date'   => $this->birth_date,
            ]);

            if ($groupId) {
                Group::where('id', $groupId)->increment('students_count');
            }

            $this->update([
                'status'          => 'approved',
                'group_id'        => $groupId,
                'approved_at'     => now(),
                'approved_by'     => $approvedBy,
                'parent_user_id'  => $parent->id,
                'student_user_id' => $studentUser->id,
            ]);

            return $student;
        });
    }

    private function generateUniqueEmail(string $name, string $type): string
    {
        $baseName  = Str::slug($name, '');
        $baseEmail = strtolower($baseName) . '.' . $type . '@academy.local';

        $counter = 1;
        $email   = $baseEmail;

        while (User::where('email', $email)->exists()) {
            $email = strtolower($baseName) . '.' . $type . $counter . '@academy.local';
            $counter++;
        }

        return $email;
    }

    public function reject(?string $reason = null): bool
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

    public function isExpired(): bool
    {
        return $this->status === 'pending' &&
        $this->created_at->addDays(30)->isPast();
    }

    public function getDueAmount(): float
    {
        if (! $this->study_start_date || ! $this->payment_due_from || ! $this->payment_due_to) {
            return 0;
        }

        $studyStart = Carbon::parse($this->study_start_date);
        $dueFrom    = Carbon::parse($this->payment_due_from);
        $dueTo      = Carbon::parse($this->payment_due_to);

        $startDate = $studyStart->gt($dueFrom) ? $studyStart : $dueFrom;
        $endDate   = now()->lt($dueTo) ? now() : $dueTo;

        if ($startDate->gt($endDate)) {
            return 0;
        }

        $months = $startDate->diffInMonths($endDate) + 1;
        return (float) $this->monthly_fee * $months;
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'pending' &&
        ! empty($this->student_name) &&
        ! empty($this->parent_name) &&
        ! empty($this->student_id) &&
        strlen($this->student_id) === 9;
    }

    public function getDisplayData(): array
    {
        return [
            'id'                 => $this->id,
            'student_name'       => $this->student_name,
            'parent_name'        => $this->parent_name,
            'phone'              => $this->father_phone ?: $this->phone,
            'father_phone'       => $this->father_phone,
            'mother_phone'       => $this->mother_phone,
            'status'             => $this->status,
            'status_in_arabic'   => $this->status_in_arabic,
            'application_number' => $this->application_number,
            'student_id'         => $this->student_id,
            'grade'              => $this->grade,
            'application_date'   => $this->application_date,
            'created_at'         => $this->created_at?->format('Y-m-d'),
            'group_id'           => $this->group_id,
            'group'              => $this->group?->only(['id', 'name']),
        ];

    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($admission) {
            $admission->application_number = self::generateApplicationNumber();

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
