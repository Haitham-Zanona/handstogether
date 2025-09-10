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

    // ✅ العلاقة الصحيحة
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'group_subjects')
            ->withPivot(['teacher_id', 'schedule', 'is_active'])
            ->withTimestamps();
    }

    public function activeSubjects()
    {
        return $this->subjects()->wherePivot('is_active', true);
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
        )->whereNotNull('group_subjects.teacher_id');
    }

    // ✅ دالة محدثة لإضافة الطالب
    public function addStudent(Student $student)
    {
        // التحقق من وجود مساحة في المجموعة الحالية
        if ($this->students_count < $this->max_capacity) {
            $student->update(['group_id' => $this->id]);
            $this->increment('students_count');
            return $this;
        }

        // إنشاء شعبة جديدة إذا وصلت للحد الأقصى
        $newGroup = $this->createNewSection();
        $student->update(['group_id' => $newGroup->id]);
        return $newGroup;
    }

    // ✅ دالة محدثة لإزالة الطالب
    public function removeStudent(Student $student)
    {
        if ($student->group_id === $this->id) {
            $student->update(['group_id' => null]);
            if ($this->students_count > 0) {
                $this->decrement('students_count');
            }
        }
        return $this;
    }

    // ✅ دالة جديدة لنقل الطالب
    public function transferStudentTo(Student $student, Group $targetGroup)
    {
        if ($student->group_id === $this->id) {
            if ($targetGroup->students_count < $targetGroup->max_capacity) {
                $student->update(['group_id' => $targetGroup->id]);
                $this->decrement('students_count');
                $targetGroup->increment('students_count');
                return true;
            }
        }
        return false;
    }

    private function createNewSection()
    {
        $latestSection = self::where('grade_level', $this->grade_level)
            ->orderBy('section_number', 'desc')
            ->first();

        $newSectionNumber = $latestSection ? $latestSection->section_number + 1 : 1;
        $sectionLetter    = $this->getSectionLetter($newSectionNumber);

        $newGroup = self::create([
            'name'           => $this->grade_level . ' - الشعبة ' . $sectionLetter,
            'grade_level'    => $this->grade_level,
            'section'        => $sectionLetter,
            'section_number' => $newSectionNumber,
            'students_count' => 1, // الطالب الجديد
            'max_capacity'   => $this->max_capacity,
            'is_active'      => true,
            'description'    => 'شعبة جديدة لطلاب ' . $this->grade_level,
        ]);

        $this->copySubjectsToNewGroup($newGroup);
        return $newGroup;
    }

    private function getSectionLetter($sectionNumber)
    {
        $letters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و', 'ز', 'ح', 'ط', 'ي', 'ك', 'ل', 'م', 'ن'];
        return $letters[($sectionNumber - 1) % count($letters)];
    }

    public static function getAvailableGroups($gradeLevel = null)
    {
        $query = self::where('is_active', true)
            ->whereRaw('students_count < max_capacity');

        if ($gradeLevel) {
            $query->where('grade_level', $gradeLevel);
        }

        return $query->get();
    }

    public static function getFullGroups()
    {
        return self::where('is_active', true)
            ->whereRaw('students_count >= max_capacity')
            ->get();
    }

    public function scopeByGradeLevel($query, $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getOccupancyPercentageAttribute()
    {
        return $this->max_capacity > 0 ?
        round(($this->students_count / $this->max_capacity) * 100, 2) : 0;
    }

    public function getCanAddStudentsAttribute()
    {
        return $this->students_count < $this->max_capacity && $this->is_active;
    }

    public function getFullNameAttribute()
    {
        return $this->grade_level . ' - الشعبة ' . $this->section;
    }

    private function copySubjectsToNewGroup($newGroup)
    {
        $groupSubjects = $this->groupSubjects;

        foreach ($groupSubjects as $groupSubject) {
            GroupSubject::create([
                'group_id'   => $newGroup->id,
                'subject_id' => $groupSubject->subject_id,
                'teacher_id' => $groupSubject->teacher_id,
                'schedule'   => $groupSubject->schedule,
                'is_active'  => $groupSubject->is_active,
            ]);
        }
    }

    public function groupSubjects()
    {
        return $this->hasMany(GroupSubject::class);
    }

    // ✅ دالة محدثة لعدد الطلاب الحقيقي
    public function getRealStudentsCountAttribute()
    {
        return $this->students()->count();
    }

    public function getTodayLectures()
    {
        return $this->lectures()
            ->whereDate('date', today())
            ->with('teacher.user')
            ->orderBy('start_time')
            ->get();
    }

    // ✅ دالة للتحقق من صحة عدد الطلاب
    public function syncStudentsCount()
    {
        $realCount = $this->students()->count();
        if ($this->students_count !== $realCount) {
            $this->update(['students_count' => $realCount]);
        }
        return $this;
    }
}