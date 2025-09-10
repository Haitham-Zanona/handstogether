<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'grade_level',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * علاقة مع GroupSubject (Many-to-Many عبر جدول وسيط)
     */
    public function groupSubjects()
    {
        return $this->hasMany(GroupSubject::class);
    }

    /**
     * علاقة مع Groups عبر GroupSubject
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_subjects')
            ->withPivot(['teacher_id', 'schedule', 'is_active'])
            ->withTimestamps();
    }

    /**
     * علاقة مع المحاضرات
     */
    public function lectures()
    {
        return $this->hasMany(Lecture::class);
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
        return $query->where('grade_level', $gradeLevel);
    }

    /**
     * scope للبحث بالاسم
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    /**
     * الحصول على عدد المجموعات المرتبطة
     */
    public function getGroupsCountAttribute()
    {
        return $this->groups()->count();
    }

    /**
     * الحصول على المدرسين المرتبطين بهذه المادة
     */
    public function getTeachersAttribute()
    {
        return $this->groupSubjects()
            ->with('teacher.user')
            ->whereNotNull('teacher_id')
            ->get()
            ->pluck('teacher')
            ->unique('id');
    }

    /**
     * التحقق من وجود مدرس للمادة في مجموعة معينة
     */
    public function hasTeacherInGroup($groupId)
    {
        return $this->groupSubjects()
            ->where('group_id', $groupId)
            ->whereNotNull('teacher_id')
            ->exists();
    }

    /**
     * الحصول على المادة مع معلومات المجموعة
     */
    public function getSubjectInfoForGroup($groupId)
    {
        return $this->groupSubjects()
            ->with(['teacher.user', 'group'])
            ->where('group_id', $groupId)
            ->first();
    }

    /**
     * الحصول على إجمالي عدد المحاضرات للمادة
     */
    public function getTotalLecturesAttribute()
    {
        return $this->lectures()->count();
    }

    /**
     * الحصول على محاضرات الشهر الحالي
     */
    public function getThisMonthLecturesAttribute()
    {
        return $this->lectures()
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();
    }
}