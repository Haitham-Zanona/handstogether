<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name',
        'grade_level',
        'section',
        'section_number',
        'students_count',
        'max_capacity',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'group_student');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'group_subjects')
            ->withPivot(['teacher_id', 'schedule', 'is_active'])
            ->withTimestamps();
    }

    public function admissions()
    {
        return $this->hasMany(Admission::class);
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class);
    }

    public function teachers()
    {
        return $this->hasManyThrough(
                    Teacher::class,
                    GroupSubject::class,
                    'group_id',
                    'id',
                    'id',
                    'teacher_id'
                );    
    }

    /**
     * إضافة طالب للمجموعة مع إنشاء شعبة جديدة تلقائياً عند الحاجة
     */
    public function addStudent()
    {
        // التحقق من وجود مساحة في المجموعة الحالية
        if ($this->students_count < $this->max_capacity) {
            $this->increment('students_count');
            return $this;
        }

        // إنشاء شعبة جديدة إذا وصلت للحد الأقصى
        return $this->createNewSection();
    }


     /**
     * إنشاء شعبة جديدة
     */
    private function createNewSection()
    {
        // العثور على أحدث رقم شعبة لنفس المرحلة
        $latestSection = self::where('grade_level', $this->grade_level)
            ->orderBy('section_number', 'desc')
            ->first();

        $newSectionNumber = $latestSection ? $latestSection->section_number + 1 : 1;
        
        // تحديد حرف الشعبة
        $sectionLetter = $this->getSectionLetter($newSectionNumber);

        // إنشاء المجموعة الجديدة
        $newGroup = self::create([
            'name' => $this->grade_level . ' - الشعبة ' . $sectionLetter,
            'grade_level' => $this->grade_level,
            'section' => $sectionLetter,
            'section_number' => $newSectionNumber,
            'students_count' => 1, // إضافة الطالب الجديد
            'max_capacity' => $this->max_capacity,
            'is_active' => true,
            'description' => 'شعبة جديدة لطلاب ' . $this->grade_level,
        ]);

        // نسخ المواد من المجموعة الأصلية للجديدة
        $this->copySubjectsToNewGroup($newGroup);

        return $newGroup;
    }



    /**
     * تحديد حرف الشعبة بناءً على الرقم
     */
    private function getSectionLetter($sectionNumber)
    {
        $letters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و', 'ز', 'ح', 'ط', 'ي', 'ك', 'ل', 'م', 'ن'];
        return $letters[($sectionNumber - 1) % count($letters)];
    }




     /**
     * الحصول على المجموعات المتاحة (التي لم تصل للحد الأقصى)
     */
    public static function getAvailableGroups($gradeLevel = null)
    {
        $query = self::where('is_active', true)
            ->whereRaw('students_count < max_capacity');

        if ($gradeLevel) {
            $query->where('grade_level', $gradeLevel);
        }

        return $query->get();
    }

    /**
     * الحصول على المجموعات الممتلئة
     */
    public static function getFullGroups()
    {
        return self::where('is_active', true)
            ->whereRaw('students_count >= max_capacity')
            ->get();
    }



     /**
     * scope للبحث حسب المرحلة
     */
    public function scopeByGradeLevel($query, $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    /**
     * scope للمجموعات النشطة
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * الحصول على نسبة الامتلاء
     */
    public function getOccupancyPercentageAttribute()
    {
        return $this->max_capacity > 0 ? 
            round(($this->students_count / $this->max_capacity) * 100, 2) : 0;
    }

    /**
     * التحقق من إمكانية إضافة طلاب جدد
     */
    public function getCanAddStudentsAttribute()
    {
        return $this->students_count < $this->max_capacity && $this->is_active;
    }

    /**
     * الحصول على الاسم الكامل للشعبة
     */
    public function getFullNameAttribute()
    {
        return $this->grade_level . ' - الشعبة ' . $this->section;
    }


    /**
     * إزالة طالب من المجموعة
     */
    public function removeStudent()
    {
        if ($this->students_count > 0) {
            $this->decrement('students_count');
        }
        return $this;
    }


     /**
     * نسخ المواد والمدرسين للمجموعة الجديدة
     */
    private function copySubjectsToNewGroup($newGroup)
    {
        $groupSubjects = $this->groupSubjects;
        
        foreach ($groupSubjects as $groupSubject) {
            GroupSubject::create([
                'group_id' => $newGroup->id,
                'subject_id' => $groupSubject->subject_id,
                'teacher_id' => $groupSubject->teacher_id, // نفس المدرس
                'schedule' => $groupSubject->schedule,
                'is_active' => $groupSubject->is_active,
            ]);
        }
    }


     /**
     * الحصول على مواد المجموعة مع المدرسين
     */
    public function groupSubjects()
    {
        return $this->hasMany(GroupSubject::class);
    }

    // Get total students count
    public function getTotalStudentsAttribute()
    {
        // Use the pre-loaded students_count from withCount() if available
        if (array_key_exists('students_count', $this->attributes)) {
            return $this->attributes['students_count'];
        }

        // Fallback to counting if students_count is not pre-loaded
        return $this->students()->count();
    }

    // Get active lectures for today
    public function getTodayLectures()
    {
        return $this->lectures()
            ->whereDate('date', today())
            ->with('teacher.user')
            ->orderBy('start_time')
            ->get();
    }
}
