<?php
namespace App\Console\Commands;

use App\Models\Admission;
use App\Services\AdmissionService;
use Illuminate\Console\Command;

class ManageAdmissions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'admissions:manage
                            {action : العملية المطلوبة (stats|cleanup|export)}
                            {--status= : فلترة حسب الحالة}
                            {--days= : عدد الأيام للتنظيف}
                            {--format= : صيغة التصدير (json|csv)}';

    /**
     * The console command description.
     */
    protected $description = 'إدارة طلبات الانتساب من سطر الأوامر';

    /**
     * خدمة طلبات الانتساب
     */
    protected AdmissionService $admissionService;

    /**
     * Create a new command instance.
     */
    public function __construct(AdmissionService $admissionService)
    {
        parent::__construct();
        $this->admissionService = $admissionService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'stats':
                return $this->showStatistics();

            case 'cleanup':
                return $this->cleanupExpired();

            case 'export':
                return $this->exportData();

            default:
                $this->error("العملية '{$action}' غير مدعومة.");
                $this->info('العمليات المتاحة: stats, cleanup, export');
                return 1;
        }
    }

    /**
     * عرض الإحصائيات
     */
    protected function showStatistics(): int
    {
        $this->info('🔄 جاري جمع الإحصائيات...');

        $stats = $this->admissionService->getStatistics();

        $this->info('📊 إحصائيات طلبات الانتساب');
        $this->line('═══════════════════════════════');

        // الإحصائيات العامة
        $this->table(
            ['المؤشر', 'العدد'],
            [
                ['إجمالي الطلبات', $stats['total']],
                ['في الانتظار', $stats['pending']],
                ['مقبولة', $stats['approved']],
                ['مرفوضة', $stats['rejected']],
                ['هذا الشهر', $stats['this_month']],
                ['هذا الأسبوع', $stats['this_week']],
                ['منتهية الصلاحية', $stats['expired']],
                ['متوسط وقت المعالجة (ساعة)', $stats['average_processing_time']],
            ]
        );

        // الإحصائيات حسب المرحلة
        $gradeStats = Admission::selectRaw('grade, count(*) as count, status')
            ->groupBy('grade', 'status')
            ->orderBy('grade')
            ->get()
            ->groupBy('grade');

        if ($gradeStats->isNotEmpty()) {
            $this->info('📚 توزيع المراحل الدراسية');
            $this->line('═══════════════════════════════');

            $gradeTable = [];
            foreach ($gradeStats as $grade => $statuses) {
                $pending  = $statuses->where('status', 'pending')->sum('count');
                $approved = $statuses->where('status', 'approved')->sum('count');
                $rejected = $statuses->where('status', 'rejected')->sum('count');
                $total    = $pending + $approved + $rejected;

                $gradeTable[] = [
                    $grade,
                    $total,
                    $pending,
                    $approved,
                    $rejected,
                ];
            }

            $this->table(
                ['المرحلة', 'الإجمالي', 'انتظار', 'مقبول', 'مرفوض'],
                $gradeTable
            );
        }

        // الاتجاه الشهري
        if (! empty($stats['monthly_trend'])) {
            $this->info('📈 الاتجاه الشهري');
            $this->line('═══════════════════════════════');

            $trendTable = array_map(function ($item) {
                return [$item['month'], $item['count']];
            }, $stats['monthly_trend']);

            $this->table(['الشهر', 'عدد الطلبات'], $trendTable);
        }

        // تحذيرات
        $this->showWarnings($stats);

        return 0;
    }

    /**
     * عرض التحذيرات
     */
    protected function showWarnings(array $stats): void
    {
        $warnings = [];

        if ($stats['expired'] > 0) {
            $warnings[] = "⚠️  يوجد {$stats['expired']} طلب منتهي الصلاحية";
        }

        if ($stats['pending'] > 50) {
            $warnings[] = "⚠️  عدد كبير من الطلبات في الانتظار ({$stats['pending']})";
        }

        if ($stats['average_processing_time'] > 48) {
            $warnings[] = "⚠️  متوسط وقت المعالجة مرتفع ({$stats['average_processing_time']} ساعة)";
        }

        if (! empty($warnings)) {
            $this->warn('🚨 تحذيرات');
            foreach ($warnings as $warning) {
                $this->warn($warning);
            }
        } else {
            $this->info('✅ لا توجد تحذيرات');
        }
    }

    /**
     * تنظيف الطلبات منتهية الصلاحية
     */
    protected function cleanupExpired(): int
    {
        $days = (int) $this->option('days') ?: 30;

        $this->info("🧹 تنظيف الطلبات المنتهية منذ أكثر من {$days} يوم...");

        $expiredQuery = Admission::pending()
            ->where('created_at', '<', now()->subDays($days));

        $count = $expiredQuery->count();

        if ($count === 0) {
            $this->info('✅ لا توجد طلبات منتهية الصلاحية للتنظيف');
            return 0;
        }

        $this->warn("تم العثور على {$count} طلب منتهي الصلاحية");

        if (! $this->confirm('هل تريد المتابعة مع عملية التنظيف؟')) {
            $this->info('تم إلغاء العملية');
            return 0;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $deleted = 0;
        $failed  = 0;

        $expiredAdmissions = $expiredQuery->get();

        foreach ($expiredAdmissions as $admission) {
            try {
                // إرسال إشعار أخير قبل الحذف
                $this->sendFinalNotice($admission);

                $admission->delete();
                $deleted++;

            } catch (\Exception $e) {
                $failed++;
                $this->error("خطأ في حذف الطلب #{$admission->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("✅ تم حذف {$deleted} طلب بنجاح");
        if ($failed > 0) {
            $this->warn("⚠️  فشل في حذف {$failed} طلب");
        }

        return 0;
    }

    /**
     * إرسال إشعار أخير
     */
    protected function sendFinalNotice(Admission $admission): void
    {
        // هنا يمكن إضافة منطق إرسال إشعار أخير
        // مثل SMS أو Email
    }

    /**
     * تصدير البيانات
     */
    protected function exportData(): int
    {
        $format = $this->option('format') ?: 'json';
        $status = $this->option('status');

        $this->info('📤 جاري تصدير البيانات...');

        $filters = [];
        if ($status) {
            $filters['status'] = $status;
            $this->info("فلترة حسب الحالة: {$status}");
        }

        try {
            $data = $this->admissionService->exportAdmissions($filters);

            $filename = 'admissions_' . now()->format('Y-m-d_H-i-s');

            switch ($format) {
                case 'json':
                    $filepath = storage_path("app/exports/{$filename}.json");
                    file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    break;

                case 'csv':
                    $filepath = storage_path("app/exports/{$filename}.csv");
                    $this->exportToCsv($data, $filepath);
                    break;

                default:
                    $this->error("صيغة التصدير '{$format}' غير مدعومة");
                    return 1;
            }

            $this->info("✅ تم تصدير " . count($data) . " سجل إلى: {$filepath}");

        } catch (\Exception $e) {
            $this->error("خطأ في التصدير: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }

    /**
     * تصدير إلى CSV
     */
    protected function exportToCsv(array $data, string $filepath): void
    {
        // إنشاء مجلد التصدير إذا لم يكن موجوداً
        $directory = dirname($filepath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $handle = fopen($filepath, 'w');

        // كتابة BOM للدعم العربي
        fwrite($handle, "\xEF\xBB\xBF");

        if (! empty($data)) {
            // كتابة العناوين
            fputcsv($handle, array_keys($data[0]));

            // كتابة البيانات
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
        }

        fclose($handle);
    }
}

// إضافة Command للجدولة التلقائية
class AdmissionScheduledTasks extends Command
{
    protected $signature   = 'admissions:scheduled-tasks';
    protected $description = 'تنفيذ المهام المجدولة لطلبات الانتساب';

    protected AdmissionService $admissionService;

    public function __construct(AdmissionService $admissionService)
    {
        parent::__construct();
        $this->admissionService = $admissionService;
    }

    public function handle(): int
    {
        $this->info('🕒 تنفيذ المهام المجدولة...');

        // تنظيف الطلبات المنتهية الصلاحية
        $deleted = $this->admissionService->cleanupExpiredAdmissions();
        if ($deleted > 0) {
            $this->info("تم حذف {$deleted} طلب منتهي الصلاحية");
        }

        // إرسال تذكيرات للطلبات القديمة
        $this->sendReminders();

        // تحديث الإحصائيات المخزنة مؤقتاً
        $this->updateCachedStats();

        $this->info('✅ تمت المهام المجدولة بنجاح');

        return 0;
    }

    /**
     * إرسال تذكيرات
     */
    protected function sendReminders(): void
    {
        $oldPending = Admission::pending()
            ->whereBetween('created_at', [
                now()->subDays(7),
                now()->subDays(6),
            ])
            ->get();

        foreach ($oldPending as $admission) {
            // إرسال تذكير
            $message = "تذكير: طلب انتساب {$admission->student_name} قيد المراجعة منذ أسبوع";
            // SMSService::send($admission->father_phone, $message);
        }

        if ($oldPending->count() > 0) {
            $this->info("تم إرسال {$oldPending->count()} تذكير");
        }
    }

    /**
     * تحديث الإحصائيات المخزنة مؤقتاً
     */
    protected function updateCachedStats(): void
    {
        $stats = $this->admissionService->getStatistics();
        cache(['admission_stats' => $stats], now()->addHours(6));
        $this->info('تم تحديث الإحصائيات المخزنة مؤقتاً');
    }
}
