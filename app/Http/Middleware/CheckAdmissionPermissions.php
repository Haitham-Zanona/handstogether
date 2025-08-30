<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmissionPermissions
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $user = $request->user();

        // التحقق من تسجيل الدخول
        if (! $user) {
            return redirect()->route('login');
        }

        // التحقق من الصلاحيات حسب الدور
        switch ($permission) {
            case 'reset_status':
                // فقط المشرفون يمكنهم إعادة تعيين الحالة
                if ($user->role !== 'super-admin') {
                    abort(403, 'ليس لديك صلاحية لتنفيذ هذا الإجراء');
                }
                break;

            case 'bulk_operations':
                // العمليات المتعددة للمدراء والمشرفين
                if (! in_array($user->role, ['admin', 'super-admin'])) {
                    abort(403, 'ليس لديك صلاحية للعمليات المتعددة');
                }
                break;

            case 'export_data':
                // تصدير البيانات
                if (! in_array($user->role, ['admin', 'super-admin'])) {
                    abort(403, 'ليس لديك صلاحية لتصدير البيانات');
                }
                break;

            default:
                // صلاحية عامة للوصول لنظام الانتساب
                if ($user->role !== 'admin' && $user->role !== 'super-admin') {
                    abort(403, 'ليس لديك صلاحية للوصول لهذا القسم');
                }
        }

        return $next($request);
    }
}
