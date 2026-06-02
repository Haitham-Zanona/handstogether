<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature   = 'admin:create {--from-env : Create admin silently from ADMIN_* environment variables}';
    protected $description = 'إنشاء حساب مشرف جديد أو تحديث مشرف موجود (تفاعلي أو من متغيرات البيئة)';

    public function handle(): int
    {
        if ($this->option('from-env')) {
            return $this->handleFromEnv();
        }

        $this->info('');
        $this->info('══════════════════════════════════════');
        $this->info('     إنشاء حساب المشرف - الأكاديمية  ');
        $this->info('══════════════════════════════════════');
        $this->info('');

        // ── الاسم ────────────────────────────────────────────────────────
        $name = $this->ask('الاسم الكامل');

        if (empty(trim($name))) {
            $this->error('الاسم لا يمكن أن يكون فارغاً.');
            return Command::FAILURE;
        }

        // ── الإيميل ───────────────────────────────────────────────────────
        $email = $this->askValidEmail();

        // ── التحقق من وجود مستخدم بنفس الإيميل ──────────────────────────
        $existing = User::where('email', $email)->first();

        if ($existing) {
            if ($existing->role !== 'admin') {
                $this->error("هذا الإيميل مسجّل بدور آخر ({$existing->role})، لا يمكن تحويله لمشرف من هنا.");
                return Command::FAILURE;
            }

            $this->warn("يوجد حساب مشرف بهذا الإيميل بالفعل: {$existing->name}");

            if (! $this->confirm('هل تريد تحديث بياناته؟', false)) {
                $this->info('تم الإلغاء. لم يتغيّر شيء.');
                return Command::SUCCESS;
            }
        }

        // ── رقم الهاتف (اختياري) ──────────────────────────────────────────
        $phone = $this->ask('رقم الهاتف (اختياري — اضغط Enter للتخطي)', null);

        // ── كلمة السر ────────────────────────────────────────────────────
        $password = $this->askValidPassword();

        // ── ملخص قبل الحفظ ───────────────────────────────────────────────
        $this->info('');
        $this->info('مراجعة البيانات قبل الحفظ:');
        $this->table(
            ['الحقل', 'القيمة'],
            [
                ['الاسم',             trim($name)],
                ['البريد الإلكتروني',  $email],
                ['رقم الهاتف',         $phone ?: '—'],
                ['الدور',             'مشرف (admin)'],
                ['كلمة المرور',        str_repeat('•', min(strlen($password), 12))],
            ]
        );

        if (! $this->confirm('متأكد؟ هل تريد الحفظ؟', true)) {
            $this->info('تم الإلغاء. لم يُحفظ أي شيء.');
            return Command::SUCCESS;
        }

        // ── الحفظ ────────────────────────────────────────────────────────
        $data = [
            'name'              => trim($name),
            'password'          => Hash::make($password),
            'role'              => 'admin',
            'is_active'         => true,
            'phone'             => $phone ? trim($phone) : null,
            'email_verified_at' => now(),
        ];

        if ($existing) {
            $existing->update($data);
            $this->info('');
            $this->info("✓ تم تحديث حساب المشرف بنجاح: {$email}");
        } else {
            $data['email'] = $email;
            User::create($data);
            $this->info('');
            $this->info("✓ تم إنشاء حساب المشرف بنجاح: {$email}");
        }

        $this->warn('  لا تشارك كلمة المرور مع أحد ولا تحفظها في أي ملف نصي.');
        $this->info('');

        return Command::SUCCESS;
    }

    // ── Non-interactive: reads from ADMIN_* env vars ─────────────────────

    private function handleFromEnv(): int
    {
        $email    = env('ADMIN_EMAIL');
        $name     = env('ADMIN_NAME');
        $password = env('ADMIN_PASSWORD');
        $phone    = env('ADMIN_PHONE');

        if (empty($email) || empty($name) || empty($password)) {
            $this->warn('admin:create --from-env skipped: ADMIN_EMAIL, ADMIN_NAME, or ADMIN_PASSWORD not set.');
            return Command::SUCCESS;
        }

        // Skip if any user already exists with this email (regardless of role)
        if (User::where('email', $email)->exists()) {
            $this->info("User already exists with this email: {$email} — skipped.");
            return Command::SUCCESS;
        }

        User::create([
            'name'              => $name,
            'email'             => $email,
            'password'          => Hash::make($password),
            'role'              => 'admin',
            'phone'             => $phone ?: null,
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $this->info("✓ Admin created from environment variables: {$email}");
        return Command::SUCCESS;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function askValidEmail(): string
    {
        while (true) {
            $email = $this->ask('البريد الإلكتروني');

            $v = Validator::make(
                ['email' => $email],
                ['email' => 'required|email']
            );

            if ($v->fails()) {
                $this->error('البريد الإلكتروني غير صالح، حاول مجدداً.');
                continue;
            }

            return strtolower(trim($email));
        }
    }

    private function askValidPassword(): string
    {
        while (true) {
            $password = $this->secret('كلمة المرور (8 أحرف على الأقل — لن تظهر على الشاشة)');

            if (strlen($password) < 8) {
                $this->error('كلمة المرور يجب أن تكون 8 أحرف على الأقل.');
                continue;
            }

            $confirm = $this->secret('تأكيد كلمة المرور');

            if ($password !== $confirm) {
                $this->error('كلمتا المرور غير متطابقتين، حاول مجدداً.');
                continue;
            }

            return $password;
        }
    }
}
