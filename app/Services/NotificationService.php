<?php

    // app/Services/NotificationService.php
    namespace App\Services;

    use App\Models\User;
    use App\Notifications\AcademyNotification;

    class NotificationService
    {
        /**
         * إرسال إشعار لجميع المستخدمين من دور معين
         */
        public static function notifyRole($role, $message, $link = null, $type = 'info')
        {
            $users = User::where('role', $role)->get();

            foreach ($users as $user) {
                $user->notify(new AcademyNotification($message, $link, $type));
            }
        }

        /**
         * إرسال إشعار لأولياء أمور مجموعة معينة
         */
        public static function notifyParentsOfGroup($groupId, $message, $link = null, $type = 'info')
        {
            $parents = User::whereHas('children.group', function ($query) use ($groupId) {
                $query->where('id', $groupId);
            })->get();

            foreach ($parents as $parent) {
                $parent->notify(new AcademyNotification($message, $link, $type));
            }
        }

        /**
         * إرسال إشعار لطلاب مجموعة معينة
         */
        public static function notifyStudentsOfGroup($groupId, $message, $link = null, $type = 'info')
        {
            $students = User::whereHas('student.group', function ($query) use ($groupId) {
                $query->where('id', $groupId);
            })->get();

            foreach ($students as $student) {
                $student->notify(new AcademyNotification($message, $link, $type));
            }
        }

        /**
         * إشعار عن محاضرة جديدة
         */
        public static function notifyNewLecture($lecture)
        {
            // Notify students
            self::notifyStudentsOfGroup(
                $lecture->group_id,
                "محاضرة جديدة: {$lecture->title} - {$lecture->date->format('Y-m-d')} في {$lecture->start_time->format('H:i')}",
                route('student.schedule'),
                'info'
            );

            // Notify parents
            self::notifyParentsOfGroup(
                $lecture->group_id,
                "محاضرة جديدة لطفلكم: {$lecture->title} - {$lecture->date->format('Y-m-d')} في {$lecture->start_time->format('H:i')}",
                route('parent.schedule'),
                'info'
            );
        }

        /**
         * إشعار عن تغيير موعد محاضرة
         */
        public static function notifyLectureRescheduled($lecture, $oldDate, $oldTime)
        {
            $message = "تم تغيير موعد محاضرة {$lecture->title} من {$oldDate} {$oldTime} إلى {$lecture->date->format('Y-m-d')} {$lecture->start_time->format('H:i')}";

            self::notifyStudentsOfGroup($lecture->group_id, $message, route('student.schedule'), 'warning');
            self::notifyParentsOfGroup($lecture->group_id, $message, route('parent.schedule'), 'warning');
        }

        /**
         * إشعار عن الدفعات المتأخرة
         */
        public static function notifyOverduePayments()
        {
            $overduePayments = \App\Models\Payment::with(['student.user', 'student.parent'])
                ->where('status', 'unpaid')
                ->where('month', '<', now()->format('Y-m'))
                ->get();

            foreach ($overduePayments as $payment) {
                if ($payment->student->parent) {
                    $payment->student->parent->notify(
                        AcademyNotification::paymentOverdue(
                            $payment->student->user->name,
                            $payment->formatted_month
                        )
                    );
                }
            }
        }

        /**
         * إشعار عن نسبة حضور منخفضة
         */
        public static function notifyLowAttendance($threshold = 75)
        {
            $students = \App\Models\Student::with(['user', 'parent'])
                ->get()
                ->filter(function ($student) use ($threshold) {
                    return $student->getAttendancePercentage() < $threshold;
                });

            foreach ($students as $student) {
                if ($student->parent) {
                    $student->parent->notify(new AcademyNotification(
                        "تنبيه: نسبة حضور {$student->user->name} منخفضة ({$student->getAttendancePercentage()}%)",
                        route('parent.attendance'),
                        'warning'
                    ));
                }
            }
        }

        /**
         * إشعار عن طلب انتساب جديد
         */
        public static function notifyNewAdmission($admission)
        {
            // إشعار جميع الإداريين
            self::notifyRole(
                'admin',
                "طلب انتساب جديد: {$admission->student_name} - ولي الأمر: {$admission->parent_name}",
                route('admin.admissions'),
                'info'
            );
        }

        /**
         * إشعار عن قبول طلب انتساب
         */
        public static function notifyAdmissionApproved($student)
        {
            if ($student->parent) {
                $student->parent->notify(new AcademyNotification(
                    "تم قبول طلب انتساب {$student->user->name} بنجاح! مرحباً بكم في الأكاديمية",
                    route('parent.dashboard'),
                    'success'
                ));
            }
        }

        /**
         * إشعار عن غياب طالب
         */
        public static function notifyStudentAbsence($student, $lecture)
        {
            if ($student->parent) {
                $student->parent->notify(new AcademyNotification(
                    "غياب الطالب {$student->user->name} من محاضرة {$lecture->title} بتاريخ {$lecture->date->format('Y-m-d')}",
                    route('parent.attendance'),
                    'warning'
                ));
            }
        }

        /**
         * إشعار عن استلام دفعة
         */
        public static function notifyPaymentReceived($payment)
        {
            if ($payment->student->parent) {
                $payment->student->parent->notify(new AcademyNotification(
                    "تم استلام دفعة شهر {$payment->formatted_month} للطالب {$payment->student->user->name} بمبلغ {$payment->amount} ش.ج",
                    route('parent.payments'),
                    'success'
                ));
            }
        }

        /**
         * إشعار جماعي مخصص
         */
        public static function notifyCustomMessage($userIds, $message, $link = null, $type = 'info')
        {
            $users = User::whereIn('id', $userIds)->get();

            foreach ($users as $user) {
                $user->notify(new AcademyNotification($message, $link, $type));
            }
        }

        /**
         * إشعار عن إلغاء محاضرة
         */
        public static function notifyLectureCancelled($lecture, $reason = null)
        {
            $message = "تم إلغاء محاضرة {$lecture->title} المقررة بتاريخ {$lecture->date->format('Y-m-d')} في {$lecture->start_time->format('H:i')}";

            if ($reason) {
                $message .= " - السبب: {$reason}";
            }

            self::notifyStudentsOfGroup($lecture->group_id, $message, route('student.schedule'), 'error');
            self::notifyParentsOfGroup($lecture->group_id, $message, route('parent.schedule'), 'error');
        }

        /**
         * تذكير بمحاضرة قادمة (لتشغيلها عبر Cron Job)
         */
        public static function remindUpcomingLectures($hoursBefore = 2)
        {
            $upcomingLectures = \App\Models\Lecture::with(['teacher.user', 'group'])
                ->where('date', today())
                ->whereBetween('start_time', [
                    now()->addHours($hoursBeefore)->format('H:i:s'),
                    now()->addHours($hoursBeeure + 1)->format('H:i:s'),
                ])
                ->get();

            foreach ($upcomingLectures as $lecture) {
                self::notifyStudentsOfGroup(
                    $lecture->group_id,
                    "تذكير: محاضرة {$lecture->title} ستبدأ خلال ساعتين في {$lecture->start_time->format('H:i')}",
                    route('student.schedule'),
                    'info'
                );
            }
        }

        /**
         * إرسال تقرير أسبوعي للأهالي
         */
        public static function sendWeeklyReportToParents()
        {
            $parents = User::where('role', 'parent')->with('children')->get();

            foreach ($parents as $parent) {
                foreach ($parent->children as $child) {
                    $weeklyAttendance = $child->getWeeklyAttendancePercentage();

                    $parent->notify(new AcademyNotification(
                        "التقرير الأسبوعي للطالب {$child->user->name}: نسبة الحضور {$weeklyAttendance}%",
                        route('parent.attendance'),
                        $weeklyAttendance >= 80 ? 'success' : 'warning'
                    ));
                }
            }
    }
}
