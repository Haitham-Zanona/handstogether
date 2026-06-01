<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// إرسال إشعارات الدفعات غير المسددة يومياً في نهاية الدوام (4 م)
Schedule::command('payments:notify-due')->dailyAt('16:00');

// تذكير المدرسين بإدخال التقييمات الدورية (يشتغل يومياً ويتحقق هل اليوم هو يوم تقييم)
Schedule::command('grades:remind-evaluations')->dailyAt('08:00');

// جلب آخر منشورات Instagram وTikTok وتخزينها في الـ cache كل ساعتين
Schedule::command('social:fetch-posts')->everyTwoHours();

// تجديد Instagram long-lived token شهرياً قبل انتهاء صلاحيته (60 يوم)
Schedule::command('social:refresh-instagram-token')->monthly();
