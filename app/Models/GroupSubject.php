<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'subject_id',
        'teacher_id',
        'schedule',
        'is_active',
    ];

    protected $casts = [
        'schedule' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * علاقة مع المجموعة
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * علاقة مع المادة
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * علاقة مع المدرس
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * scope للمواد النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * scope للبحث حسب المرحلة
     */
    public function scopeByGradeLevel($query, $gradeLevel)
    {
        return $query->whereHas('group', function ($q) use ($gradeLevel) {
            $q->where('grade_level', $gradeLevel);
        });
    }

    /**
     * scope للبحث حسب المادة
     */
    public function scopeBySubject($query, $subjectName)
    {
        return $query->whereHas('subject', function ($q) use ($subjectName) {
            $q->where('name', $subjectName);
        });
    }

    /**
     * scope للبحث حسب المدرس
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * تعيين مدرس للمادة
     */
    public function assignTeacher($teacherId)
    {
        $this->update(['teacher_id' => $teacherId]);
        return $this;
    }

    /**
     * إزالة المدرس من المادة
     */
    public function removeTeacher()
    {
        $this->update(['teacher_id' => null]);
        return $this;
    }

    /**
     * تحديث جدولة المادة
     */
    public function updateSchedule($schedule)
    {
        $this->update(['schedule' => $schedule]);
        return $this;
    }

    /**
     * الحصول على اسم المادة
     */
    public function getSubjectNameAttribute()
    {
        return $this->subject ? $this->subject->name : null;
    }

    /**
     * الحصول على اسم المدرس
     */
    public function getTeacherNameAttribute()
    {
        return $this->teacher ? $this->teacher->user->name : 'غير محدد';
    }

    /**
     * الحصول على اسم المجموعة
     */
    public function getGroupNameAttribute()
    {
        return $this->group ? $this->group->full_name : null;
    }

    /**
     * التحقق من وجود جدولة
     */
    public function getHasScheduleAttribute()
    {
        return !empty($this->schedule) && 
               (isset($this->schedule['days']) && !empty($this->schedule['days'])) ||
               (isset($this->schedule['times']) && !empty($this->schedule['times']));
    }

    /**
     * الحصول على عدد الحصص في الأسبوع
     */
    public function getWeeklyClassesCountAttribute()
    {
        if (!$this->has_schedule) {
            return 0;
        }

        $days = $this->schedule['days'] ?? [];
        return count($days);
    }

    /**
     * الحصول على إجمالي الدقائق في الأسبوع
     */
    public function getWeeklyDurationAttribute()
    {
        $duration = $this->schedule['duration'] ?? 45;
        return $this->weekly_classes_count * $duration;
    }
}