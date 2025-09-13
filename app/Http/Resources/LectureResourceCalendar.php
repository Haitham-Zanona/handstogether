<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LectureResourceCalendar extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        $startTime = $this->start_time ?? '09:00:00';
        $endTime   = $this->end_time ?? '10:30:00';

        if ($startTime instanceof \Carbon\Carbon) {
            $startTime = $startTime->format('H:i:s');
        }

        if ($endTime instanceof \Carbon\Carbon) {
            $endTime = $endTime->format('H:i:s');
        }

        return [
            'id'              => $this->id,
            'title'           => $this->title ?? 'محاضرة بدون عنوان',
            'start'           => $this->date->format('Y-m-d') . 'T' . $startTime,
            'end'             => $this->date->format('Y-m-d') . 'T' . $endTime,
            'backgroundColor' => $this->getEventColor($this->type ?? 'lecture'),
            'borderColor'     => $this->getEventBorderColor($this->status ?? 'scheduled'),
            'extendedProps'   => [
                'type'           => $this->type ?? 'lecture',
                'status'         => $this->status ?? 'scheduled',
                'teacher_name'   => $this->teacher?->user->name ?? 'غير محدد',
                'group_name'     => $this->group->name ?? 'غير محدد',
                'subject_name'   => $this->subject->name ?? '',
                'students_count' => $this->group->students_count ?? 0,
                'series_id'      => $this->series_id ?? null,
                'description'    => $this->description ?? '',
            ],
        ];
    }

    private function getEventColor($type)
    {
        return match ($type) {
            'final_exam' => '#DC3545',
            'exam'       => '#EE8100',
            'review'     => '#28A745',
            'activity'   => '#FFC107',
            default      => '#2778E5',
        };
    }

    private function getEventBorderColor($status)
    {
        return match ($status) {
            'cancelled'   => '#6C757D',
            'rescheduled' => '#6F42C1',
            'completed'   => '#198754',
            default       => '#2778E5',
        };
    }

    /**
     * Static method to fetch lectures with optional dynamic filters
     */
    public static function fetchFiltered($request)
    {
        $query = \App\Models\Lecture::with(['teacher.user', 'group', 'subject']);

        // فلاتر ديناميكية من الـ request
        if ($request->filled('start') && $request->filled('end')) {
            $query->whereBetween('date', [$request->start, $request->end]);
        }

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $lectures = $query->get();

        if ($request->filled('mini') && $request->mini) {
            return $lectures->map(fn($lecture) => [
                'start'           => $lecture->date->format('Y-m-d') . 'T' . ($lecture->start_time ?? '09:00:00'),
                'end'             => $lecture->date->format('Y-m-d') . 'T' . ($lecture->end_time ?? '10:30:00'),
                'backgroundColor' => (new self($lecture))->getEventColor($lecture->type ?? 'lecture'),
            ]);
        }

        return self::collection($query->get());
    }
}